<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('image_url')) {
    /**
     * URL aman untuk gambar yang disimpan di disk 'public'.
     * Mengembalikan null jika path kosong atau file tidak benar-benar ada,
     * sehingga pemanggil wajib menyiapkan placeholder (tidak ada broken image / 404).
     */
    function image_url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        // Jangan bangun URL dari nilai yang ternyata sudah URL absolut.
        if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }
        if (! Storage::disk('public')->exists($path)) {
            return null;
        }
        return asset('storage/' . $path);
    }
}

if (! function_exists('image_tag')) {
    /**
     * HTML <img> dengan placeholder inisial: tidak pernah menghasilkan broken image.
     */
    function image_tag(?string $path, string $alt = '', string $class = 'w-full h-full object-cover', string $fallbackText = ''): string
    {
        $url = image_url($path);
        if ($url === null) {
            $initials = e(Str::upper(Str::substr($fallbackText !== '' ? $fallbackText : $alt, 0, 2)));
            return '<span class="flex items-center justify-center w-full h-full bg-blue-100 text-blue-700 font-bold text-sm">' . $initials . '</span>';
        }
        $lazy = ' loading="lazy" decoding="async"';
        return '<img src="' . e($url) . '" alt="' . e($alt) . '" class="' . e($class) . '"' . $lazy . '>';
    }
}
