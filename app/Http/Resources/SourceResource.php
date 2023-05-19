<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = Auth::guard('api')->user();
        $sources = $user->sources->pluck('id')->all();
        $source = $this;

        return [
            'id' => $source->id,
            'name' => $source->name,
            'selected' => in_array($source->id, $sources)
        ];
    }
}
