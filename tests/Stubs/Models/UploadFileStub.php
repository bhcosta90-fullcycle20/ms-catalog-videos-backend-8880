<?php

namespace Tests\Stubs\Models;

use App\Models\Traits\UploadFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UploadFileStub extends Model
{
    use UploadFile;

    protected $table = 'upload_files';

    protected $fillable = [
        'name',
        'file1',
        'file2',
    ];

    public function fileDir(): string
    {
        return '1';
    }

    public static function fileFields(): array
    {
        return [
            'file1',
            'file2',
        ];
    }

    public static function createTable()
    {
        Schema::create('upload_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file1')->nullable();
            $table->string('file2')->nullable();
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        Schema::dropIfExists('upload_files');
    }
}
