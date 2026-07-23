<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * گردشگری — فید محتوای دسته «گردشگری» از روی جدول اخبار موجود. اگر دسته‌ای
 * یافت نشود پاسخ خالیِ امن برمی‌گردد (نشان‌کردن هنوز بک‌اند عضو ندارد → false).
 */
class TourismController extends Controller
{
    use MemberApiResponses;

    /**
     * GET /tourism — {data:[{id,title,image_url,excerpt,url,bookmarked}], meta}
     */
    public function feed(Request $request)
    {
        if (! Schema::hasTable('news') || ! Schema::hasTable('categories')) {
            return $this->emptyPaged();
        }

        try {
            $category = $this->tourismCategory();

            if ($category === null) {
                return $this->emptyPaged();
            }

            $paginator = News::query()
                ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
                ->where('is_published', true)
                ->orderByDesc('publish_at')
                ->paginate(15);

            $items = collect($paginator->items())
                ->map(fn (News $n) => $this->tourismItem($n))
                ->all();

            return $this->paginated($items, $paginator);
        } catch (\Throwable) {
            return $this->emptyPaged();
        }
    }

    protected function tourismCategory(): ?Category
    {
        try {
            return Category::query()
                ->where('slug', 'tourism')
                ->orWhere('en_slug', 'tourism')
                ->orWhere('title', 'like', '%گردشگری%')
                ->orWhere('en_title', 'like', '%Tourism%')
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function tourismItem(News $n): array
    {
        return [
            'id' => (int) $n->id,
            'title' => (string) $n->title,
            'image_url' => $this->imageUrl($n),
            'excerpt' => $this->excerpt($n),
            'url' => $this->url($n),
            'bookmarked' => false,
        ];
    }

    protected function imageUrl(News $n): ?string
    {
        try {
            return $n->getImageUrl('medium');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function excerpt(News $n): string
    {
        $text = (string) ($n->short_description ?? $n->meta_desc ?? '');

        return Str::limit(trim(strip_tags($text)), 200, '…');
    }

    protected function url(News $n): string
    {
        try {
            return (string) $n->getUrl();
        } catch (\Throwable) {
            return '';
        }
    }
}
