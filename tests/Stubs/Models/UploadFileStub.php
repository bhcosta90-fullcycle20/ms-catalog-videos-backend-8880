<?php

namespace Tests\Stubs\Models;

use App\Models\Traits\UploadFile;
use Illuminate\Database\Eloquent\Model;

class UploadFileStub extends Model
{
    use UploadFile;

    public function fileDir(): string
    {
        return '1';
    }

    public static function fileFields(): array
    {
        return [
            'file1',
            'file2'
        ];
    }
}
