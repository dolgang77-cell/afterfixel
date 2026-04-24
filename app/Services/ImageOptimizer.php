<?php

namespace App\Services;

use App\Support\ImageUrl;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    private const MAX_WIDTH = 1920;
    private const MAX_HEIGHT = 1920;
    private const QUALITY = 82;
    private const THUMB_SIZE = 400;
    private const RESPONSIVE_WIDTHS = [320, 640, 960];
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB 원본 허용

    /**
     * 이미지 업로드 + 최적화 + 썸네일 생성
     *
     * @return array{path: string, url: string, thumbnail_path: string|null, variant_paths: array<string, string>, original_name: string, original_size: int, optimized_size: int, mime_type: string, width: int, height: int}
     */
    public static function process(UploadedFile $file, string $folder): array
    {
        $path = $file->getRealPath();
        $originalName = $file->getClientOriginalName();
        $originalSize = (int) $file->getSize();
        $extension = strtolower($file->getClientOriginalExtension());
        $detectedInfo = @getimagesize($path ?: '');
        $detectedMime = is_array($detectedInfo) ? ($detectedInfo['mime'] ?? null) : null;

        if ($originalSize <= 0 || $originalSize > self::MAX_FILE_SIZE) {
            throw new \RuntimeException('최대 10MB 이하 이미지 파일만 업로드할 수 있습니다.');
        }

        if (!$detectedInfo || !$detectedMime) {
            self::logDecodeFailure($file, $extension, null, 'image-metadata-unreadable');
            throw new \RuntimeException('이미지 정보를 읽을 수 없습니다. 파일을 다시 저장한 뒤 업로드해 주세요.');
        }

        // 안전한 파일명
        $filename = Str::random(20) . '_' . time();

        // GD로 이미지 로드
        $source = self::createFromFile($path ?: '', $extension, $detectedMime);
        if (!$source) {
            self::logDecodeFailure($file, $extension, $detectedMime, 'gd-decode-failed', $detectedInfo);
            return self::storeOriginalWithoutOptimization(
                $file,
                $folder,
                $filename,
                $originalName,
                $originalSize,
                $detectedMime,
                $detectedInfo
            );
        }

        // EXIF 방향 보정 (JPEG)
        if (in_array($extension, ['jpg', 'jpeg'])) {
            $source = self::fixOrientation($path ?: '', $source);
        }

        // 리사이즈
        $source = self::resize($source, self::MAX_WIDTH, self::MAX_HEIGHT);
        $width = imagesx($source);
        $height = imagesy($source);

        // 최적화된 파일 저장 (JPEG로 통일, 투명 PNG는 PNG 유지)
        $hasAlpha = self::hasTransparency($source, $extension);
        $outputExt = $hasAlpha ? 'png' : 'jpg';
        $outputMime = $hasAlpha ? 'image/png' : 'image/jpeg';
        $storedName = "{$filename}.{$outputExt}";
        $storedPath = "{$folder}/{$storedName}";

        $tempPath = tempnam(sys_get_temp_dir(), 'nite_img_');
        if ($hasAlpha) {
            imagepng($source, $tempPath, 8);
        } else {
            imagejpeg($source, $tempPath, self::QUALITY);
        }
        $optimizedSize = filesize($tempPath);

        Storage::disk('public')->put($storedPath, file_get_contents($tempPath));

        $variantPaths = self::persistResponsiveVariants(
            $source,
            $folder,
            $filename,
            $outputExt,
            $hasAlpha
        );

        // 썸네일 생성
        $thumbPath = null;
        $thumb = self::resize($source, self::THUMB_SIZE, self::THUMB_SIZE);
        $thumbName = "{$filename}_thumb.{$outputExt}";
        $thumbStoredPath = "{$folder}/thumbs/{$thumbName}";
        $tempThumb = tempnam(sys_get_temp_dir(), 'nite_thumb_');
        if ($hasAlpha) {
            imagepng($thumb, $tempThumb, 8);
        } else {
            imagejpeg($thumb, $tempThumb, 75);
        }
        Storage::disk('public')->put($thumbStoredPath, file_get_contents($tempThumb));
        $thumbPath = $thumbStoredPath;
        imagedestroy($thumb);

        // 정리
        imagedestroy($source);
        @unlink($tempPath);
        @unlink($tempThumb);

        return [
            'path'           => $storedPath,
            'url'            => ImageUrl::storage($storedPath),
            'thumbnail_path' => $thumbPath,
            'variant_paths'  => $variantPaths,
            'original_name'  => $originalName,
            'original_size'  => $originalSize,
            'optimized_size' => $optimizedSize,
            'mime_type'      => $outputMime,
            'width'          => $width,
            'height'         => $height,
        ];
    }

    private static function createFromFile(string $path, string $ext, ?string $mimeType = null): ?\GdImage
    {
        $loader = match ($mimeType) {
            'image/jpeg' => static fn (string $imagePath) => @imagecreatefromjpeg($imagePath),
            'image/png', 'image/x-png' => static fn (string $imagePath) => @imagecreatefrompng($imagePath),
            'image/gif' => static fn (string $imagePath) => @imagecreatefromgif($imagePath),
            'image/webp' => static fn (string $imagePath) => @imagecreatefromwebp($imagePath),
            default => match ($ext) {
                'jpg', 'jpeg' => static fn (string $imagePath) => @imagecreatefromjpeg($imagePath),
                'png' => static fn (string $imagePath) => @imagecreatefrompng($imagePath),
                'gif' => static fn (string $imagePath) => @imagecreatefromgif($imagePath),
                'webp' => static fn (string $imagePath) => @imagecreatefromwebp($imagePath),
                default => null,
            },
        };

        if ($loader) {
            $source = $loader($path);
            if ($source instanceof \GdImage) {
                return $source;
            }
        }

        $binary = @file_get_contents($path);
        if ($binary === false || $binary === '') {
            return null;
        }

        $fallback = @imagecreatefromstring($binary);

        return $fallback instanceof \GdImage ? $fallback : null;
    }

    private static function fixOrientation(string $path, \GdImage $image): \GdImage
    {
        $exif = @exif_read_data($path);
        if (!$exif || !isset($exif['Orientation'])) return $image;

        return match ((int) $exif['Orientation']) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    private static function resize(\GdImage $source, int $maxW, int $maxH): \GdImage
    {
        $w = imagesx($source);
        $h = imagesy($source);

        if ($w <= $maxW && $h <= $maxH) {
            return $source;
        }

        $ratio = min($maxW / $w, $maxH / $h);
        $newW = (int) round($w * $ratio);
        $newH = (int) round($h * $ratio);

        $resized = imagecreatetruecolor($newW, $newH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $w, $h);

        return $resized;
    }

    private static function hasTransparency(\GdImage $image, string $ext): bool
    {
        if ($ext !== 'png' && $ext !== 'gif') return false;
        return imagecolortransparent($image) >= 0 || ($ext === 'png');
    }

    /**
     * GD가 디코딩하지 못하는 일부 파일은 원본 그대로 저장해서 업로드는 허용한다.
     *
     * @param array<int|string, mixed> $detectedInfo
     * @return array{path: string, url: string, thumbnail_path: string|null, variant_paths: array<string, string>, original_name: string, original_size: int, optimized_size: int, mime_type: string, width: int, height: int}
     */
    private static function storeOriginalWithoutOptimization(
        UploadedFile $file,
        string $folder,
        string $filename,
        string $originalName,
        int $originalSize,
        string $detectedMime,
        array $detectedInfo
    ): array {
        $extension = self::extensionFromMime($detectedMime)
            ?? strtolower($file->getClientOriginalExtension())
            ?: 'img';

        $storedPath = "{$folder}/{$filename}.{$extension}";
        $binary = @file_get_contents($file->getRealPath() ?: '');

        if ($binary === false || $binary === '') {
            throw new \RuntimeException('이미지 파일을 저장할 수 없습니다.');
        }

        Storage::disk('public')->put($storedPath, $binary);

        Log::warning('Image stored without optimization fallback', [
            'original_name' => $originalName,
            'mime' => $detectedMime,
            'size' => $originalSize,
            'path' => $storedPath,
            'width' => (int) ($detectedInfo[0] ?? 0),
            'height' => (int) ($detectedInfo[1] ?? 0),
        ]);

        return [
            'path' => $storedPath,
            'url' => ImageUrl::storage($storedPath),
            'thumbnail_path' => null,
            'variant_paths' => [],
            'original_name' => $originalName,
            'original_size' => $originalSize,
            'optimized_size' => $originalSize,
            'mime_type' => $detectedMime,
            'width' => (int) ($detectedInfo[0] ?? 0),
            'height' => (int) ($detectedInfo[1] ?? 0),
        ];
    }

    /**
     * @return array{thumbnail_path: string, variant_paths: array<string, string>}
     */
    public static function regenerateDerivedAssetsForStoredMedia(string $storedPath, ?string $mimeType = null, bool $force = false): array
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($storedPath)) {
            throw new \RuntimeException('stored media file not found: ' . $storedPath);
        }

        $absolutePath = $disk->path($storedPath);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $detectedInfo = @getimagesize($absolutePath);
        $detectedMime = is_array($detectedInfo) ? ($detectedInfo['mime'] ?? $mimeType) : $mimeType;
        $source = self::createFromFile($absolutePath, $extension, $detectedMime);

        if (!$source) {
            throw new \RuntimeException('unable to decode stored media');
        }

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            $source = self::fixOrientation($absolutePath, $source);
        }

        $directory = trim((string) pathinfo($storedPath, PATHINFO_DIRNAME), '.');
        $basename = pathinfo($storedPath, PATHINFO_FILENAME);
        $hasAlpha = self::hasTransparency($source, $extension);
        $outputExt = $hasAlpha ? 'png' : 'jpg';
        $thumbnailPath = trim($directory, '/') . '/thumbs/' . $basename . '_thumb.' . $outputExt;

        self::persistThumbnail(
            $source,
            $thumbnailPath,
            $hasAlpha,
            $force
        );

        $variantPaths = self::persistResponsiveVariants(
            $source,
            $directory,
            $basename,
            $outputExt,
            $hasAlpha,
            $force
        );

        imagedestroy($source);

        return [
            'thumbnail_path' => $thumbnailPath,
            'variant_paths' => $variantPaths,
        ];
    }

    private static function extensionFromMime(string $mimeType): ?string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png', 'image/x-png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => null,
        };
    }

    private static function logDecodeFailure(
        UploadedFile $file,
        string $extension,
        ?string $detectedMime,
        string $reason,
        ?array $detectedInfo = null
    ): void {
        Log::warning('Image upload decode failed', [
            'reason' => $reason,
            'original_name' => $file->getClientOriginalName(),
            'client_extension' => $extension,
            'client_mime' => $file->getClientMimeType(),
            'detected_mime' => $detectedMime,
            'size' => (int) $file->getSize(),
            'width' => $detectedInfo[0] ?? null,
            'height' => $detectedInfo[1] ?? null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function persistResponsiveVariants(
        \GdImage $source,
        string $folder,
        string $filename,
        string $outputExt,
        bool $hasAlpha,
        bool $force = false
    ): array {
        $disk = Storage::disk('public');
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $variants = [];

        foreach (self::RESPONSIVE_WIDTHS as $targetWidth) {
            if ($sourceWidth <= $targetWidth) {
                continue;
            }

            $targetHeight = (int) round(($sourceHeight / $sourceWidth) * $targetWidth);
            $variant = imagecreatetruecolor($targetWidth, $targetHeight);

            if ($hasAlpha) {
                imagealphablending($variant, false);
                imagesavealpha($variant, true);
                $transparent = imagecolorallocatealpha($variant, 0, 0, 0, 127);
                imagefilledrectangle($variant, 0, 0, $targetWidth, $targetHeight, $transparent);
            }

            imagecopyresampled($variant, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

            $variantPath = trim($folder, '/') . '/variants/' . $filename . '_' . $targetWidth . '.' . $outputExt;

            if ($force || !$disk->exists($variantPath)) {
                $tempFile = tempnam(sys_get_temp_dir(), 'nite_variant_');

                if ($hasAlpha) {
                    imagepng($variant, $tempFile, 8);
                } else {
                    imagejpeg($variant, $tempFile, self::QUALITY);
                }

                $disk->put($variantPath, file_get_contents($tempFile));
                @unlink($tempFile);
            }

            imagedestroy($variant);
            $variants[(string) $targetWidth] = $variantPath;
        }

        return $variants;
    }

    private static function persistThumbnail(
        \GdImage $source,
        string $thumbnailPath,
        bool $hasAlpha,
        bool $force = false
    ): void {
        $disk = Storage::disk('public');

        if (!$force && $disk->exists($thumbnailPath)) {
            return;
        }

        $thumb = self::resizeClone($source, self::THUMB_SIZE, self::THUMB_SIZE);
        $tempThumb = tempnam(sys_get_temp_dir(), 'nite_thumb_');

        if ($hasAlpha) {
            imagepng($thumb, $tempThumb, 8);
        } else {
            imagejpeg($thumb, $tempThumb, 75);
        }

        $disk->put($thumbnailPath, file_get_contents($tempThumb));

        imagedestroy($thumb);
        @unlink($tempThumb);
    }

    private static function resizeClone(\GdImage $source, int $maxW, int $maxH): \GdImage
    {
        $resized = self::resize($source, $maxW, $maxH);

        if ($resized !== $source) {
            return $resized;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $clone = imagecreatetruecolor($width, $height);
        imagealphablending($clone, false);
        imagesavealpha($clone, true);
        $transparent = imagecolorallocatealpha($clone, 0, 0, 0, 127);
        imagefilledrectangle($clone, 0, 0, $width, $height, $transparent);
        imagecopy($clone, $source, 0, 0, 0, 0, $width, $height);

        return $clone;
    }
}
