<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request) + [
            'url' => [
                'video_file' => $this->video_file_url,
                'banner_file' => $this->banner_file_url,
                'trailer_file' => $this->trailer_file_url,
                'thumb_file' => $this->thumb_file_url,
            ]
        ];
    }
}
