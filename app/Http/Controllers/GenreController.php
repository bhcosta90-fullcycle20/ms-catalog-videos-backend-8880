<?php

namespace App\Http\Controllers;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    private $rules = [
        'name' => 'required|min:3|max:100',
        'is_active' => 'nullable|boolean',
    ];

    public function index()
    {
        return GenreResource::collection(Genre::paginate());
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->rules);

        $genre = Genre::create($data);
        $genre->refresh();

        return new GenreResource($genre);
    }

    public function show(Genre $genre)
    {
        return new GenreResource($genre);
    }

    public function update(Request $request, Genre $genre)
    {
        $data = $this->validate($request, $this->rules);
        $genre->update($data);

        return new GenreResource($genre);
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();

        return response()->noContent();
    }
}
