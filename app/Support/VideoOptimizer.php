<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Web video optimizer.
 *
 * Re-encodes the SAME footage into a streaming-friendly MP4:
 *  - H.264 (yuv420p, plays on every browser / iPhone / Android)
 *  - Max 1920px wide, capped bitrate (~4 Mbps) so it does not stall on slow networks
 *  - "faststart" (moov atom at the start) so playback begins before the full download
 *
 * The media record is kept (same id, same custom properties), only the file is replaced.
 */
class VideoOptimizer
{
    public static function findFfmpeg(): ?string
    {
        if (!function_exists('shell_exec') && !function_exists('exec')) {
            return null;
        }

        $candidates = [
            env('FFMPEG_PATH'),
            config('media-library.ffmpeg_path'),
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/opt/homebrew/bin/ffmpeg',
            'ffmpeg',
        ];

        foreach (array_unique(array_filter($candidates)) as $bin) {
            $test = @shell_exec(escapeshellcmd($bin) . ' -version 2>&1');
            if ($test && stripos($test, 'ffmpeg version') !== false) {
                return $bin;
            }
        }

        return null;
    }

    /**
     * Optimize a video media item in place. Returns true if the file was replaced.
     */
    public static function optimize(Media $media): bool
    {
        try {
            $source = $media->getPath();
            if (!is_file($source)) {
                return false;
            }

            $ffmpeg = self::findFfmpeg();
            if (!$ffmpeg) {
                Log::info("VideoOptimizer: ffmpeg not available, media #{$media->id} kept as uploaded.");
                return false;
            }

            @set_time_limit(0);
            @ignore_user_abort(true);

            $dir = dirname($source);
            $baseName = pathinfo($media->file_name, PATHINFO_FILENAME);
            $tmp = $dir . DIRECTORY_SEPARATOR . $baseName . '__opt_' . uniqid() . '.mp4';

            $cmd = sprintf(
                '%s -y -i %s -map 0:v:0 -map 0:a:0? -c:v libx264 -preset veryfast -profile:v high -level 4.1 -pix_fmt yuv420p '
                . '-crf 24 -maxrate 4M -bufsize 8M -vf "scale=\'min(1920,iw)\':-2" -g 48 '
                . '-c:a aac -b:a 96k -movflags +faststart %s 2>&1',
                escapeshellcmd($ffmpeg),
                escapeshellarg($source),
                escapeshellarg($tmp)
            );

            @exec($cmd, $output, $code);

            if ($code !== 0 || !is_file($tmp) || filesize($tmp) === 0) {
                @unlink($tmp);
                Log::warning("VideoOptimizer: ffmpeg failed for media #{$media->id}", ['output' => array_slice($output ?? [], -5)]);
                return false;
            }

            // Only keep the new file if it is actually lighter (or the original was not MP4)
            $originalIsMp4 = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION)) === 'mp4';
            if ($originalIsMp4 && filesize($tmp) >= filesize($source)) {
                @unlink($tmp);
                return false;
            }

            $finalName = $baseName . '.mp4';
            $finalPath = $dir . DIRECTORY_SEPARATOR . $finalName;

            if ($source !== $finalPath) {
                @unlink($source);
            }
            if (is_file($finalPath)) {
                @unlink($finalPath);
            }
            rename($tmp, $finalPath);

            $props = $media->custom_properties ?? [];
            $props['web_optimized'] = true;
            $props['media_type'] = 'video';

            $media->file_name = $finalName;
            $media->mime_type = 'video/mp4';
            $media->size = filesize($finalPath);
            $media->custom_properties = $props;
            $media->save();

            Cache::flush();

            return true;
        } catch (\Throwable $e) {
            Log::warning("VideoOptimizer: exception for media #{$media->id}: " . $e->getMessage());
            return false;
        }
    }
}
