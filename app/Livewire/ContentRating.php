<?php

namespace App\Livewire;

use App\Models\ContentRating as ContentRatingModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

/**
 * Public star rating (1-5) for a content item (امتیازدهی کاربران).
 * One vote per ip per item; feedback follows the PollComponent conventions.
 */
class ContentRating extends Component
{
    use LivewireAlert;

    public $post;

    public $average = 0;

    public $count = 0;

    public $myRating = null;

    public function mount($post)
    {
        $this->post = $post;
        $this->refreshStats();
    }

    protected function ratingsAvailable(): bool
    {
        try {
            return $this->post && ! empty($this->post->id) && Schema::hasTable('content_ratings');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function baseQuery()
    {
        return ContentRatingModel::query()
            ->where('rateable_type', get_class($this->post))
            ->where('rateable_id', $this->post->id);
    }

    public function refreshStats(): void
    {
        if (! $this->ratingsAvailable()) {
            return;
        }

        $this->count = (int) $this->baseQuery()->count();
        $this->average = $this->count > 0
            ? round((float) $this->baseQuery()->avg('rating'), 1)
            : 0;
        $this->myRating = $this->baseQuery()
            ->where('ip', request()->ip())
            ->value('rating');
    }

    public function rate($value)
    {
        $value = (int) $value;

        if ($value < 1 || $value > 5) {
            return;
        }

        if (! $this->ratingsAvailable()) {
            $this->alert('error', 'در حال حاضر امکان ثبت امتیاز وجود ندارد.', [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه',
                'confirmButtonColor' => '#3085d6',
            ]);

            return;
        }

        $ip = request()->ip();

        if ($this->baseQuery()->where('ip', $ip)->exists()) {
            $this->refreshStats();
            $this->alert('error', 'شما قبلاً به این مطلب امتیاز داده‌اید!', [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه',
                'confirmButtonColor' => '#3085d6',
            ]);

            return;
        }

        try {
            ContentRatingModel::create([
                'rateable_type' => get_class($this->post),
                'rateable_id' => $this->post->id,
                'rating' => $value,
                'ip' => $ip,
                'user_id' => Auth::id(),
                'created_at' => now(),
            ]);
        } catch (QueryException $e) {
            // Unique index race: the same ip rated concurrently.
            $this->refreshStats();
            $this->alert('error', 'شما قبلاً به این مطلب امتیاز داده‌اید!', [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه',
                'confirmButtonColor' => '#3085d6',
            ]);

            return;
        }

        $this->refreshStats();
        $this->alert('success', 'امتیاز شما ثبت شد. متشکریم!', [
            'toast' => false,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'باشه',
            'confirmButtonColor' => '#3085d6',
        ]);
    }

    public function render()
    {
        return view('livewire.content-rating');
    }
}
