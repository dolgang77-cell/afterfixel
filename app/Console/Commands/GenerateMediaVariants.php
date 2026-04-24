<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;

class GenerateMediaVariants extends Command
{
    protected $signature = 'media:generate-variants {--id=* : Specific media IDs to process} {--force : Rebuild even if variants already exist}';
    protected $description = 'Generate responsive image variants for stored media files';

    public function handle(): int
    {
        $ids = collect((array) $this->option('id'))
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->values();

        $query = Media::query()->orderBy('id');

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids);
        }

        $force = (bool) $this->option('force');
        $processed = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;

        $query->chunkById(100, function ($mediaItems) use ($force, &$processed, &$updated, &$skipped, &$failed) {
            foreach ($mediaItems as $media) {
                $processed++;

                if (!$media->file_path) {
                    $skipped++;
                    $this->line("skip #{$media->id} no file_path");
                    continue;
                }

                if (!$force && !empty($media->variant_paths)) {
                    $skipped++;
                    $this->line("skip #{$media->id} variants already exist");
                    continue;
                }

                try {
                    $derivedAssets = ImageOptimizer::regenerateDerivedAssetsForStoredMedia(
                        $media->file_path,
                        $media->mime_type,
                        $force,
                    );

                    $media->forceFill([
                        'thumbnail_path' => $derivedAssets['thumbnail_path'],
                        'variant_paths' => $derivedAssets['variant_paths'],
                    ])->save();

                    $updated++;
                    $this->line("ok   #{$media->id} generated " . count($derivedAssets['variant_paths']) . ' variants');
                } catch (\Throwable $exception) {
                    $failed++;
                    $this->error("fail #{$media->id} {$exception->getMessage()}");
                }
            }
        });

        $this->newLine();
        $this->info("processed={$processed} updated={$updated} skipped={$skipped} failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
