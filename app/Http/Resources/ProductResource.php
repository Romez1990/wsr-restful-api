<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'datetime' => date('H:i d.m.Y', strtotime($this->created_at)),
            'manufacturer' => $this->manufacturer,
            'text' => $this->text,
            'image' => url(Storage::url($this->image)),
        ];
    }
}
