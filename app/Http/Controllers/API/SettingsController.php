<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\SourceResource;
use Illuminate\Http\Request;
use App\Models\Source;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function getMyFilters(Request $request)
    {
        $user = Auth::guard('api')->user();
        if(!$user) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid user'
            ]);
        }
       
        return response()->json([
            'sources' => SourceResource::collection($user->sources),
            'categories' => CategoryResource::collection($user->categories)
        ]);
    }

    public function getCategoriesAndSources(Request $request)
    {
        $sources = Source::orderBy('name', 'asc')->get();
        $categories = Category::orderBy('name', 'asc')->get();

        return response()->json([
            'sources' => SourceResource::collection($sources),
            'categories' => CategoryResource::collection($categories)
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::guard('api')->user();
        if(!$user) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid user'
            ]);
        }

        $user->categories()->sync(explode(',', $request->categories));
        $user->sources()->sync(explode(',', $request->sources));

        return response()->json([
            'status' => 200
        ]);
    }
}
