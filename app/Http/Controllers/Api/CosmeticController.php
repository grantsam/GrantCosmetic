<?php

namespace App\Http\Controllers\Api;

use App\Models\Cosmetic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CosmeticApiResource;

class CosmeticController extends Controller
{
    public function index(Request $request)
    {
        $cosmetics = Cosmetic::with('brand', 'category');

        if($request->has('category_id'))
        {
            $cosmetics = $cosmetics->where('category_id', $request->input('category_id'));
        }
        if($request->has('brand_id'))
        {
            $cosmetics = $cosmetics->where('brand_id', $request->input('brand_id'));
        }
        if($request->has('is_popular'))
        {
            $cosmetics = $cosmetics->where('is_popular', $request->input('is_popular'));
        }
        if($request->has('limit'))
        {
            $cosmetics = $cosmetics->limit($request->input('limit'));
        }

        return CosmeticApiResource::collection($cosmetics);
    }

    public function show(Cosmetic $cosmetic)
    {
        $cosmetic->load('brand', 'category','benefits','testimonials','photo');

        return new CosmeticApiResource($cosmetic);
    }
}
