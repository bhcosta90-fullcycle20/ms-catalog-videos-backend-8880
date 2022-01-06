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
                'video' => $this->video_file_url,
                'banner' => $this->banner_file_url,
                'trailer' => $this->trailer_file_url,
                'thumb' => $this->thumb_file_url,
            ]
        ];
    }
}
