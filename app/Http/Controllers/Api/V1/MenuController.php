<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\MenuResource;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;

/**
 * GET /menu — navigation/services tree built from the Category hierarchy.
 */
class MenuController extends ApiController
{
    public function index()
    {
        try {
            if (! Schema::hasTable('categories')) {
                return response()->json(['data' => []]);
            }

            $roots = Category::with('childrenRecursive')
                ->whereNull('parent_id')
                ->orderBy('id')
                ->get();

            return MenuResource::collection($roots);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['data' => []]);
        }
    }
}
