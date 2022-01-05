<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video as Model;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use RefreshDatabase;

    private Model $model;

    private $sendData = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 1990,
            'rating' => Model::RATINGS[0],
            'duration' => 60,
        ];
    }

    public function testRollbackStore()
    {
        try {
            Model::create($this->sendData + ['categories_id' => [1]]);
        } catch(QueryException $e) {
            $this->assertEquals(23000, $e->getCode());
            $this->assertEquals(0, Model::count());
        }
    }

    public function testRollbackUpdate()
    {
        $objModel = Model::factory()->create();
        $videoTitle = $objModel->title;

        try {
            $objModel->update($this->sendData + ['categories_id' => [1]]);
        } catch (QueryException $e) {
            $this->assertDatabaseHas('videos', [
                'id' => $objModel->id,
                'title' => $videoTitle,
            ]);
            $this->assertEquals(23000, $e->getCode());
        }
    }

    public function testCreateWithBasicFields()
    {
        $video = Model::create($this->sendData);
        $video->refresh();
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->sendData + ['id' => $video->id, 'opened' => false]);

        $video = Model::create($this->sendData + ['opened' => true]);
        $video->refresh();
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->sendData + ['id' => $video->id, 'opened' => true]);
    }

    public function testCreateWithRelations()
    {
        $category = Category::factory()->create();
        $genre = Genre::factory()->create();

        $video = Model::create($this->sendData + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testUpdatedWithBasicFields()
    {
        $video = Model::factory()->create(['opened' => false]);
        $video->update($this->sendData);
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->sendData + ['id' => $video->id, 'opened' => false]);

        $video = Model::factory()->create(['opened' => false]);
        $video->update($this->sendData + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->sendData + ['id' => $video->id, 'opened' => true]);
    }

    public function testUpdatedWithRelations()
    {
        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $video = Model::factory()->create(['opened' => false]);

        $video->update($this->sendData + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testHandleRelations()
    {
        /** @var Model $video */
        $video = Model::factory()->create();
        $video->handleRelations([]);

        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = Category::factory()->create();
        $video->handleRelations([
            'categories_id' => [$category->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);

        $genre = Genre::factory()->create();
        $video->handleRelations([
            'genres_id' => [$genre->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->genres);

        $video->categories()->delete();
        $video->genres()->delete();

        $video->handleRelations([
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ]);

        $video->refresh();
        $this->assertCount(1, $video->categories);
        $this->assertCount(1, $video->genres);
    }

    public function testSyncCategories()
    {
        $categoriesId = Category::factory(3)->create()->pluck('id')->toArray();
        $video = Model::factory()->create();
        $video->handleRelations([
            'categories_id' => [$categoriesId[0]]
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);

        $video->handleRelations([
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ]);
        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2],
            'video_id' => $video->id
        ]);
    }

    public function testSyncGenres()
    {
        $genresId = Genre::factory(3)->create()->pluck('id')->toArray();
        $video = Model::factory()->create();
        $video->handleRelations([
            'genres_id' => [$genresId[0]]
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $video->id
        ]);

        $video->handleRelations([
            'genres_id' => [$genresId[1], $genresId[2]]
        ]);
        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[2],
            'video_id' => $video->id
        ]);
    }

    public function testDelete()
    {
        $data = Model::factory()->create();
        $data->delete();
        $this->assertNull(Model::find($data->id));

        $data->restore();
        $this->assertNotNull(Model::find($data->id));
    }

    protected function assertHasCategory($idVideo, $idCategory)
    {
        $this->assertDatabaseHas('category_video', [
            'category_id' => $idCategory,
            'video_id' => $idVideo,
        ]);
    }

    protected function assertHasGenre($idVideo, $idGenre)
    {
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $idGenre,
            'video_id' => $idVideo,
        ]);
    }
}
