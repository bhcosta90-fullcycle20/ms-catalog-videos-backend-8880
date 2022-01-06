<?php

namespace App\Models\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadFile
{
    protected abstract function fileDir(): string;

    protected abstract static function fileFields(): array;

    /**
     * @param UploadedFile[] $files
     * 
     * @return [type]
     */
    public function uploadFiles(array $files)
    {
        foreach ($files as $file) {
            $this->uploadFile($file);
        }
    }

    public function uploadFile($file)
    {
        $file->store($this->fileDir());
    }

    /**
     * @param string|UploadedFile[] $files
     * 
     * @return [type]
     */
    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->deleteFile($file);
        }
    }

    public function deleteFile(string|UploadedFile $file)
    {
        $nameFile = $file instanceof UploadedFile ? $file->hashName() : $file;
        Storage::delete("{$this->fileDir()}/{$nameFile}");
    }

    public static function extractFiles(array &$atributes = [])
    {
        $files = [];
        foreach (self::fileFields() as $file) {
            if (isset($atributes[$file]) && $atributes[$file] instanceof UploadedFile) {
                $files[] = $atributes[$file];
                $atributes[$file] = $atributes[$file]->hashName();
            }
        }

        return $files;
    }
}
