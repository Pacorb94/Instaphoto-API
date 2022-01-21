<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class UserResource extends JsonResource
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
            'nick' => $this->nick,
            'email' => $this->email,
            'profileImage' => $this->profile_image,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at
        ];
    }
}
