<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\LiveStreamResource;
use App\Http\Resources\Api\PriceResource;
use App\Models\LiveStream;
use App\Models\MarketPrice;
use Illuminate\Support\Facades\Schema;

/**
 * GET /prices — active market prices (flattened, category order preserved).
 * GET /live    — active live streams.
 */
class MiscController extends ApiController
{
    public function prices()
    {
        try {
            if (! Schema::hasTable('market_prices')) {
                return response()->json(['data' => []]);
            }

            // activeGrouped() returns a Collection<string, Collection<MarketPrice>>
            // in the site's category display order; flatten it for the app.
            $items = MarketPrice::activeGrouped()->flatten();

            return PriceResource::collection($items);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['data' => []]);
        }
    }

    public function live()
    {
        try {
            if (! Schema::hasTable('live_streams')) {
                return response()->json(['data' => []]);
            }

            $streams = LiveStream::activeStreams();

            return LiveStreamResource::collection($streams);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['data' => []]);
        }
    }
}
