<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'datetime' => date('H:i d.m.Y', strtotime($this->created_at)),
            'author' => $this->author,
            'text' => $this->text,
        ];
    }
}
