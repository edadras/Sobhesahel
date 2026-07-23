<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\NewsCardResource;
use App\Models\Gallery;
use App\Models\Podcast;
use App\Models\Video;
use Illuminate\Http\Request;

/**
 * Multimedia listings: GET /videos, /podcasts, /galleries.
 */
class MediaController extends ApiController
{
    public function videos(Request $request)
    {
        return $this->list($request, Video::class);
    }

    public function podcasts(Request $request)
    {
        return $this->list($request, Podcast::class);
    }

    public function galleries(Request $request)
    {
        return $this->list($request, Gallery::class);
    }

    /**
     * @param  class-string  $modelClass
     */
    private function list(Request $request, string $modelClass)
    {
        $paginator = $this->publicQuery($modelClass)
            ->orderBy('id', 'DESC')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return NewsCardResource::collection($paginator);
    }
}
