<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('lang:sync-json {locale : Locale slug (e.g. es)} {--from= : Source locale (defaults to APP_FALLBACK_LOCALE)}', function () {
    $locale = strtolower(trim((string) $this->argument('locale')));
    $sourceLocale = strtolower(trim((string) ($this->option('from') ?: config('app.fallback_locale', 'en'))));

    if ($locale === '') {
        $this->error('Locale cannot be empty.');

        return self::FAILURE;
    }

    $langPath = resource_path('lang');

    if (! File::isDirectory($langPath)) {
        File::makeDirectory($langPath, 0755, true);
    }

    $sourcePath = $langPath.'/'.$sourceLocale.'.json';
    $targetPath = $langPath.'/'.$locale.'.json';

    $readJsonFile = function (string $path): array {
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid JSON in '.$path);
        }

        return $decoded;
    };

    try {
        $source = $readJsonFile($sourcePath);
        $target = $readJsonFile($targetPath);
    } catch (RuntimeException $exception) {
        $this->error($exception->getMessage());

        return self::FAILURE;
    }

    if (! File::exists($sourcePath)) {
        $this->warn('Source file not found: '.$sourcePath.'. Creating an empty locale file.');
    }

    $added = 0;

    foreach ($source as $key => $value) {
        if (! array_key_exists($key, $target)) {
            $target[$key] = is_string($value) ? $value : (string) $key;
            $added++;
        }
    }

    ksort($target, SORT_NATURAL | SORT_FLAG_CASE);

    File::put(
        $targetPath,
        json_encode($target, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL
    );

    $this->info("Locale file synced: {$targetPath}");
    $this->line('Total keys: '.count($target));
    $this->line('New keys added: '.$added);

    return self::SUCCESS;
})->purpose('Create or update resources/lang/{locale}.json from a source locale JSON file');
