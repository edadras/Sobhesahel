<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Edwink\FilamentUserActivity\Models\UserActivity;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Morilog\Jalali\Jalalian;

class OnlineUsersWidget extends Widget
{
    use HasWidgetShield;

    protected static string $view = 'filament.widgets.online-users-widget';

    protected static bool $isLazy = false;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'onlineUsers' => $this->onlineUsers(),
        ];
    }

    /**
     * Panel users active in the last 5 minutes, with their last activity time.
     */
    protected function onlineUsers(): Collection
    {
        try {
            return UserActivity::query()
                ->where('created_at', '>=', now()->subMinutes(5))
                ->selectRaw('user_id, MAX(created_at) as last_activity')
                ->groupBy('user_id')
                ->orderByDesc('last_activity')
                ->with('user')
                ->get()
                ->filter(fn ($row) => $row->user !== null)
                ->map(function ($row) {
                    $lastActivity = Carbon::parse($row->last_activity);

                    return [
                        'name' => (string) ($row->user->name ?? 'کاربر ناشناس'),
                        'avatar' => rescue(fn () => $row->user->getFilamentAvatarUrl(), null, false),
                        'last_activity_time' => Jalalian::fromCarbon($lastActivity)->format('H:i:s'),
                        'last_activity_diff' => (int) $lastActivity->diffInMinutes(now()),
                    ];
                })
                ->values();
        } catch (\Throwable $e) {
            // Activity table missing or query failure — show an empty state.
            return collect();
        }
    }
}
