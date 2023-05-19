<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends JsonResource
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
        $categories = $user->categories->pluck('id')->all();
        $category = $this;

        return [
            'id' => $category->id,
            'name' => $category->name,
            'selected' => in_array($category->id, $categories)
        ];
    }
}
