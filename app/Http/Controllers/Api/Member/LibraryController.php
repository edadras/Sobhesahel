<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use Illuminate\Http\Request;

/**
 * کتابخانه (نشان‌شده‌ها، نویسندگان دنبال‌شده، ادامه مطالعه) — جدول‌های عضو
 * (bookmarks/author_follows/reading_progress) هنوز وجود ندارند؛ پاسخ‌های
 * گارد‌شده و خالیِ امن تا اپ کرش نکند.
 */
class LibraryController extends Controller
{
    use MemberApiResponses;

    /**
     * GET /library/bookmarks?type= — {data:[], meta}
     */
    public function bookmarks(Request $request)
    {
        return $this->emptyPaged();
    }

    /**
     * POST /library/bookmarks/toggle — {bookmarked:bool}
     */
    public function toggleBookmark(Request $request)
    {
        return $this->data(['bookmarked' => false]);
    }

    /**
     * GET /library/authors — {data:[]}
     */
    public function authors(Request $request)
    {
        return $this->data([]);
    }

    /**
     * POST /library/authors/{id}/toggle — {following:bool}
     */
    public function toggleAuthor(Request $request, int $id)
    {
        return $this->data(['following' => false]);
    }

    /**
     * GET /library/reading — {data:[]}
     */
    public function reading(Request $request)
    {
        return $this->data([]);
    }

    /**
     * POST /library/reading — ثبت پیشرفت مطالعه (no-op تا جدول ساخته شود).
     */
    public function updateReading(Request $request)
    {
        return $this->data(['ok' => true]);
    }
}
