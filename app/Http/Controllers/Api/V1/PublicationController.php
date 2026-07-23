<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\PublicationResource;
use App\Models\Archive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * GET /publications — newspaper/magazine archive issues (PDF covers).
 */
class PublicationController extends ApiController
{
    public function index(Request $request)
    {
        try {
            if (! Schema::hasTable('archives')) {
                return response()->json(['data' => [], 'meta' => $this->emptyMeta()]);
            }

            $query = Archive::query()
                ->where('is_published', true)
                ->orderBy('id', 'DESC');

            $year = $request->query('year');

            if (filled($year) && ctype_digit((string) $year)) {
                $this->applyYearFilter($query, (int) $year);
            }

            $paginator = $query->paginate($this->perPage($request))->withQueryString();

            return PublicationResource::collection($paginator);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['data' => [], 'meta' => $this->emptyMeta()]);
        }
    }

    private function applyYearFilter($query, int $year): void
    {
        // Prefer the dedicated archive_date; fall back to publish_at.
        if (Schema::hasColumn('archives', 'archive_date')) {
            $query->where(function ($q) use ($year) {
                $q->whereYear('archive_date', $year)
                    ->orWhere(function ($inner) use ($year) {
                        $inner->whereNull('archive_date')->whereYear('publish_at', $year);
                    });
            });

            return;
        }

        $query->whereYear('publish_at', $year);
    }

    private function emptyMeta(): array
    {
        return ['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 0];
    }
}
