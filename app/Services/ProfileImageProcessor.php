<?php

namespace App\Services;

use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileImageProcessor
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private readonly ProfileImageModerationService $moderationService,
    ) {
    }

    public function storeForUser(User $user, UploadedFile $file): ProfileImage
    {
        $this->assertValidUpload($file);

        $diskName = (string) config('profile-images.disk', 'public');
        $disk = Storage::disk($diskName);
        $baseDir = trim((string) config('profile-images.base_dir', 'uploads/profile'), '/');
        if ($diskName === 'public') {
            $this->ensureProtectedUploadRoot($disk, $baseDir);
        } else {
            $disk->makeDirectory($baseDir);
        }

        $uploadUuid = (string) Str::uuid();
        $targetDir = "{$baseDir}/{$user->id}/{$uploadUuid}";

        $originalBinary = file_get_contents($file->getRealPath());
        if ($originalBinary === false) {
            throw ValidationException::withMessages([
                'image' => '업로드된 파일을 읽을 수 없습니다.',
            ]);
        }

        $imageInfo = @getimagesizefromstring($originalBinary);
        $serverMime = $imageInfo['mime'] ?? null;
        if (!$serverMime || !in_array($serverMime, self::ALLOWED_MIME_TYPES, true)) {
            throw ValidationException::withMessages([
                'image' => '지원하지 않는 이미지 형식입니다.',
            ]);
        }

        $source = @imagecreatefromstring($originalBinary);
        if (!$source instanceof \GdImage) {
            throw ValidationException::withMessages([
                'image' => '손상되었거나 처리할 수 없는 이미지입니다.',
            ]);
        }

        if ($serverMime === 'image/jpeg') {
            $source = $this->fixOrientation($file->getRealPath(), $source);
        }

        $optimized = $this->resizeToFit($source, (int) config('profile-images.max_dimension', 1024));
        $thumb = $this->createSquareThumbnail($optimized, (int) config('profile-images.thumbnail_size', 200));

        [$extension, $mimeType] = $this->determineOutputFormat($serverMime);
        $optimizedBinary = $this->encodeImage($optimized, $extension, (int) config('profile-images.quality', 82));
        $thumbBinary = $this->encodeImage($thumb, $extension, 75);

        $originalPath = null;
        if ((bool) config('profile-images.store_original', false)) {
            $originalExt = strtolower((string) $file->getClientOriginalExtension());
            $originalPath = "{$targetDir}/original.{$originalExt}";
            $disk->put($originalPath, $originalBinary);
        }

        $imagePath = "{$targetDir}/optimized.{$extension}";
        $thumbPath = "{$targetDir}/thumb.{$extension}";

        $disk->put($imagePath, $optimizedBinary);
        $disk->put($thumbPath, $thumbBinary);

        $moderation = $this->moderationService->inspect($optimizedBinary);
        $status = $moderation['verdict'] === 'safe' ? 'approved' : 'pending';

        $profileImage = DB::transaction(function () use (
            $user,
            $uploadUuid,
            $diskName,
            $originalPath,
            $imagePath,
            $thumbPath,
            $mimeType,
            $file,
            $optimizedBinary,
            $optimized,
            $moderation,
            $status
        ) {
            if ($status === 'approved') {
                ProfileImage::where('user_id', $user->id)->where('is_current', true)->update(['is_current' => false]);
            }

            return ProfileImage::create([
                'user_id' => $user->id,
                'upload_uuid' => $uploadUuid,
                'disk' => $diskName,
                'original_path' => $originalPath,
                'image_path' => $imagePath,
                'thumb_path' => $thumbPath,
                'mime_type' => $mimeType,
                'original_size' => $file->getSize(),
                'optimized_size' => strlen($optimizedBinary),
                'width' => imagesx($optimized),
                'height' => imagesy($optimized),
                'moderation_provider' => $moderation['provider'],
                'moderation_verdict' => $moderation['verdict'],
                'moderation_score' => $moderation['score'],
                'moderation_labels' => $moderation['labels'],
                'status' => $status,
                'is_current' => $status === 'approved',
                'approved_at' => $status === 'approved' ? now() : null,
            ]);
        });

        imagedestroy($source);
        imagedestroy($optimized);
        imagedestroy($thumb);

        return $profileImage->fresh();
    }

    private function assertValidUpload(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw ValidationException::withMessages([
                'image' => '업로드에 실패했습니다.',
            ]);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'image' => 'jpg, jpeg, png, webp 파일만 업로드할 수 있습니다.',
            ]);
        }

        $size = (int) $file->getSize();
        $maxBytes = ((int) config('profile-images.max_upload_size_kb', 10240)) * 1024;
        if ($size <= 0 || $size > $maxBytes) {
            throw ValidationException::withMessages([
                'image' => '최대 10MB 이미지까지 업로드할 수 있습니다.',
            ]);
        }
    }

    private function ensureProtectedUploadRoot($disk, string $baseDir): void
    {
        $disk->makeDirectory($baseDir);
        $disk->put("{$baseDir}/.htaccess", <<<'HTACCESS'
Options -Indexes -ExecCGI
RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8 .phar .cgi .pl .py .sh
<FilesMatch "\.(php|phtml|phar|cgi|pl|py|sh|exe|js|jsp|asp|aspx|html|htm)$">
    Require all denied
</FilesMatch>
HTACCESS);
    }

    private function fixOrientation(string $path, \GdImage $image): \GdImage
    {
        $exif = @exif_read_data($path);
        if (!$exif || !isset($exif['Orientation'])) {
            return $image;
        }

        $rotated = match ((int) $exif['Orientation']) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if (!$rotated instanceof \GdImage) {
            return $image;
        }

        if ($rotated !== $image) {
            imagedestroy($image);
        }

        return $rotated;
    }

    private function resizeToFit(\GdImage $source, int $maxDimension): \GdImage
    {
        $srcWidth = imagesx($source);
        $srcHeight = imagesy($source);
        $scale = min($maxDimension / $srcWidth, $maxDimension / $srcHeight, 1);
        $targetWidth = max((int) round($srcWidth * $scale), 1);
        $targetHeight = max((int) round($srcHeight * $scale), 1);

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        $this->prepareCanvas($resized);

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcWidth, $srcHeight);

        return $resized;
    }

    private function createSquareThumbnail(\GdImage $source, int $size): \GdImage
    {
        $srcWidth = imagesx($source);
        $srcHeight = imagesy($source);
        $cropSize = min($srcWidth, $srcHeight);
        $srcX = (int) floor(($srcWidth - $cropSize) / 2);
        $srcY = (int) floor(($srcHeight - $cropSize) / 2);

        $thumb = imagecreatetruecolor($size, $size);
        $this->prepareCanvas($thumb);

        imagecopyresampled($thumb, $source, 0, 0, $srcX, $srcY, $size, $size, $cropSize, $cropSize);

        return $thumb;
    }

    private function prepareCanvas(\GdImage $image): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $transparent);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function determineOutputFormat(string $serverMime): array
    {
        if (function_exists('imagewebp')) {
            return ['webp', 'image/webp'];
        }

        return match ($serverMime) {
            'image/png', 'image/webp' => ['png', 'image/png'],
            default => ['jpg', 'image/jpeg'],
        };
    }

    private function encodeImage(\GdImage $image, string $extension, int $quality): string
    {
        ob_start();

        $success = match ($extension) {
            'webp' => imagewebp($image, null, $quality),
            'png' => imagepng($image, null, 8),
            default => imagejpeg($image, null, $quality),
        };

        $binary = ob_get_clean();

        if (!$success || !is_string($binary) || $binary === '') {
            throw ValidationException::withMessages([
                'image' => '이미지 인코딩에 실패했습니다.',
            ]);
        }

        return $binary;
    }
}
