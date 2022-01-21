<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'image' => $this->image,
            'description' => $this->description,
            'user' => new UserResource($this->user),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'likes' => LikeResource::collection($this->likes),
            'comments' => CommentResource::collection($this->comments)
        ];
    }
}