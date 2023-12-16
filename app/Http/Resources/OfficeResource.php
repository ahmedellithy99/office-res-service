<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class OfficeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'user' => UserResource::make($this->user),

            $this->merge(Arr::except(parent::toArray($request), ['deleted_at' , 'updated_at' , 'created_at' , 'user_id']))
        ];
    
    }
}
