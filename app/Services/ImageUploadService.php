<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Безопасно съхранение на изображения: генерирано име, проверка на реалния
 * тип и намаляване на размера, когато GD е налична.
 */
class ImageUploadService
{
    public function store(UploadedFile $file, string $directory): string
    {
        $name = Str::uuid()->toString().'.'.$this->extension($file);
        $path = trim($directory, '/').'/'.$name;

        $resized = $this->resize($file);

        if ($resized !== null) {
            Storage::disk('public')->put($path, $resized);
        } else {
            Storage::disk('public')->putFileAs(trim($directory, '/'), $file, $name);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function extension(UploadedFile $file): string
    {
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());

        return in_array($extension, config('nt.uploads.mimes'), true) ? $extension : 'jpg';
    }

    /** Връща преоразмереното изображение или null, ако обработка не е нужна/възможна. */
    protected function resize(UploadedFile $file): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $maxWidth = (int) config('nt.uploads.max_width');
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth) {
            imagedestroy($image);

            return null;
        }

        $newHeight = (int) round($height * ($maxWidth / $width));
        $resized = imagescale($image, $maxWidth, $newHeight);
        imagedestroy($image);

        if ($resized === false) {
            return null;
        }

        ob_start();
        imagejpeg($resized, null, 82);
        $output = (string) ob_get_clean();
        imagedestroy($resized);

        return $output;
    }
}
