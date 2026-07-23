<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use Illuminate\Http\Request;

/**
 * اعلان‌ها — جدول اعلان‌های عضو و ترجیحات آن هنوز وجود ندارد؛ پاسخ‌های
 * گارد‌شده و خالیِ امن. لیست با unread_count=0 مطابق قرارداد اپ برمی‌گردد.
 */
class NotificationController extends Controller
{
    use MemberApiResponses;

    /**
     * GET /notifications?filter=all|unread — {data:[], meta, unread_count}
     */
    public function index(Request $request)
    {
        return $this->emptyPaged(['unread_count' => 0]);
    }

    /**
     * POST /notifications/{id}/read
     */
    public function markRead(Request $request, int $id)
    {
        return $this->data(['ok' => true]);
    }

    /**
     * POST /notifications/read-all
     */
    public function markAllRead(Request $request)
    {
        return $this->data(['ok' => true]);
    }

    /**
     * GET /notifications/preferences — {points, subscription, announcements}
     */
    public function preferences(Request $request)
    {
        return $this->data($this->defaultPreferences());
    }

    /**
     * PUT /notifications/preferences — echo the submitted prefs (no store yet).
     */
    public function updatePreferences(Request $request)
    {
        $defaults = $this->defaultPreferences();

        return $this->data([
            'points' => $request->boolean('points', $defaults['points']),
            'subscription' => $request->boolean('subscription', $defaults['subscription']),
            'announcements' => $request->boolean('announcements', $defaults['announcements']),
        ]);
    }

    /**
     * @return array{points:bool, subscription:bool, announcements:bool}
     */
    protected function defaultPreferences(): array
    {
        return [
            'points' => true,
            'subscription' => true,
            'announcements' => true,
        ];
    }
}
