<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GenresHasCategoriesRule implements Rule
{
    private array $genresId;

    public function __construct(private array $categoriesId)
    {
        $this->categoriesId = array_unique($this->categoriesId);
    }

    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            $value = [];
        }

        $this->genresId = array_unique($value);

        if (!count($this->genresId) || !count($this->categoriesId)) {
            return false;
        }

        $categoriesFound = [];
        foreach ($this->genresId as $genreId) {
            $rows = $this->getRows($genreId);
            if (!$rows->count()) {
                return false;
            }
            array_push($categoriesFound, ...$rows->pluck('category_id')->toArray());
        }
        $categoriesFound = array_unique($categoriesFound);
        if (count($categoriesFound) !== count($this->categoriesId)) {
            return false;
        }
        return true;
    }

    protected function getRows($genreId): Collection
    {
        return DB::table('category_genre')
            ->select(['category_id'])
            ->where('genre_id', $genreId)
            ->whereIn('category_id', $this->categoriesId)
            ->get();
    }

    public function message()
    {
        return trans('validation.genres_has_categories');
    }
}
