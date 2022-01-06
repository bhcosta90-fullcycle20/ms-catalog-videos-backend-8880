<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

trait UploadFile
{
    public array $oldFiles = [];

    protected abstract function fileDir(): string;

    protected abstract static function fileFields(): array;

    public static function bootUploadFile(): void
    {
        static::updating(function (Model $model) {
            $fieldsUpdated = array_keys($model->getDirty());
            $filesUpdated = array_intersect($fieldsUpdated, self::fileFields());
            $filesFiltered = Arr::where($filesUpdated, fn ($f) => $model->getOriginal($f));
            $model->oldFiles = array_map(fn ($f) => $model->getOriginal($f), $filesFiltered);
        });
    }

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

    public function relativePath(string $value)
    {
        return "{$this->fileDir()}/{$value}";
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

    public function deleteOldFiles()
    {
        $this->deleteFiles($this->oldFiles);
    }

    protected function getFileUrl($filename)
    {
        return Storage::url($this->relativePath($filename));
    }
}
