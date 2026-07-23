<?php

namespace App\Livewire;

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Support\Facades\Auth;

class PollComponent extends Component
{
    use LivewireAlert;

    public $poll;
    public $selectedOption;
    public $results = [];

    public function mount(Poll $poll)
    {
        $this->poll = $poll;
        $this->fetchResults();
    }

    public function vote()
    {
        if (! $this->poll->isOpen()) {
            $this->fetchResults();
            $this->alert('error', 'این نظرسنجی پایان یافته است.', [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه',
                'confirmButtonColor' => '#3085d6'
            ]);
            return;
        }

        if ($this->selectedOption == '' || $this->selectedOption == null){
            $this->alert('error', 'شما هیچ گزینه ای را انتخاب نکرده اید!', [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه',
                'confirmButtonColor' => '#3085d6'
            ]);
            return;
        }

        if ($this->poll->login_required && !Auth::check()) {
            $this->alert('error', 'برای رأی دادن باید وارد شوید.', [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه',
                'confirmButtonColor' => '#3085d6'
            ]);
            return;
        }

        $userId = Auth::id();

        $voted = PollVote::castVote(
            $this->poll->id,
            (int) $this->selectedOption,
            $userId,
            request()->ip(),
            request()->userAgent(),
            (bool) $this->poll->login_required
        );

        if (! $voted) {
            $this->alert('error', 'شما قبلاً در این نظرسنجی شرکت کرده‌اید!', [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه',
                'confirmButtonColor' => '#3085d6'
            ]);
            return;
        }

        $this->fetchResults();
        $this->alert('success', 'رأی شما ثبت شد!', [
            'toast' => false,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'باشه',
            'confirmButtonColor' => '#3085d6'
        ]);
    }

    public function fetchResults()
    {
        $this->results = collect($this->poll->options)->mapWithKeys(function ($option) {
            return [$option->id => $option->votes()->count()];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.poll-component', [
            'loginRequired' => $this->poll->login_required ? route('login') : null,
            'isEnded' => $this->poll->hasEnded(),
        ]);
    }
}
