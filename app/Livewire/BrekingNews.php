<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\News;
use Illuminate\Support\Facades\Cache;

class BreakingNews extends Component
{ 
    public $newsIds = [1, 2, 3, 4, 5]; // Hardcoded for now
    public $currentNews;
    public $index = 0;

    public function mount()
    {
        $this->loadNews();
    }

    public function loadNews()
    {
        if (!empty($this->newsIds)) {
            $newsItem = News::find($this->newsIds[$this->index] ?? null);
            if ($newsItem) {
                $this->currentNews = $newsItem->title;
            }
            $this->index = ($this->index + 1) % count($this->newsIds);
        }
    }

    public function render()
    {
        return view('livewire.breaking-news');
    }
}
