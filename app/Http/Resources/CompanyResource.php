<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->logo,
            'logo_url' => $this->logo_url,
            'email' => $this->email,
            'number_of_compounds' => $this->number_of_compounds,
            'number_of_available_units' => $this->number_of_available_units,
            'created_at' => $this->created_at,
            'compounds' => CompoundResource::collection($this->whenLoaded('compounds')),
            'users' => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}
