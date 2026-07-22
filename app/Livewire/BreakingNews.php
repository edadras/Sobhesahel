<?php

namespace App\Livewire;

use App\Models\FeaturedNews;
use Livewire\Component;
use App\Models\News;
use Illuminate\Support\Facades\Cache;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class BreakingNews extends Component
{
    public $newsIds = []; // Hardcoded for now
    public $currentNews;
    public $index = 0;

    #[On('refreshBreakingNews')]
    public function refreshBreakingNews()
    {
        $this->newsIds = FeaturedNews::getNewsIdsBySection('urgent');

        $this->loadNews();
    }

    public function mount()
    {
        $this->newsIds = FeaturedNews::getNewsIdsBySection('urgent');

        $this->loadNews();
    }

    public function loadNews()
    {
        if (!empty($this->newsIds)) {
            $randomKey = array_rand($this->newsIds);
            $newsItem = News::find($this->newsIds[$randomKey] ?? null);

            if ($newsItem) {
                $this->currentNews = [
                    'title' => $newsItem->title,
                    'url' => $newsItem->getUrl()
                ];
                $this->dispatch('update-news', news: [
                    'title' => $newsItem->title,
                    'url' => $newsItem->getUrl()
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.breking-news');
    }
}
