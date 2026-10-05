<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('media:optimize-videos {--all : Re-process videos even if already optimized}', function () {
    $ffmpeg = \App\Support\VideoOptimizer::findFfmpeg();
    if (!$ffmpeg) {
        $this->error('ffmpeg haipatikani kwenye server hii. Videos zimeachwa kama zilivyo.');
        return 1;
    }
    $this->info("Using ffmpeg: {$ffmpeg}");

    $videos = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('collection_name', 'cms_media')
        ->get()
        ->filter(function ($m) {
            $ext = strtolower(pathinfo($m->file_name, PATHINFO_EXTENSION));
            return str_starts_with(strtolower($m->mime_type ?? ''), 'video/')
                || in_array($ext, ['mp4', 'mov', 'm4v', 'webm', 'mkv', 'avi', '3gp', 'qt']);
        });

    foreach ($videos as $m) {
        if (!$this->option('all') && ($m->custom_properties['web_optimized'] ?? false)) {
            $this->line("skip #{$m->id} {$m->file_name} (already optimized)");
            continue;
        }
        $before = round(($m->size ?? 0) / 1048576, 2);
        $this->line("optimizing #{$m->id} {$m->file_name} ({$before} MB) ...");
        $ok = \App\Support\VideoOptimizer::optimize($m);
        $m->refresh();
        $after = round(($m->size ?? 0) / 1048576, 2);
        $this->line($ok ? "  done -> {$m->file_name} ({$after} MB)" : '  unchanged (already light or ffmpeg failed, see laravel.log)');
    }

    \Illuminate\Support\Facades\Cache::flush();
    $this->info('Finished.');
    return 0;
})->purpose('Compress uploaded CMS videos into smooth-streaming web MP4 (same footage)');
