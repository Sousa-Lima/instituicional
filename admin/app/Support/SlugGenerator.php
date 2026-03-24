<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugGenerator
{
    /**
     * @param class-string<Model> $modelClass
     */
    public static function uniqueForModel(string $modelClass, string $source, ?string $ignoreId = null): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $counter = 2;

        while (self::slugExists($modelClass, $slug, $ignoreId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private static function slugExists(string $modelClass, string $slug, ?string $ignoreId = null): bool
    {
        $query = $modelClass::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
