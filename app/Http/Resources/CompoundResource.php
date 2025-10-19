<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompoundResource extends JsonResource
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
            'company_id' => $this->company_id,
            'project' => $this->project,
            'name' => $this->name,
            'location' => $this->location,
            'images' => $this->images_urls,
            'is_sold' => $this->is_sold,
            'total_units' => $this->when(isset($this->total_units), $this->total_units),
            'sold_units' => $this->when(isset($this->sold_units), $this->sold_units),
            'available_units' => $this->when(isset($this->available_units), $this->available_units),
            'company_name' => $this->when(isset($this->company_name), $this->company_name),
            'company_logo' => $this->when(isset($this->company_logo), $this->company_logo),
            'company_logo_url' => $this->when(isset($this->company_logo_url), $this->company_logo_url),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
