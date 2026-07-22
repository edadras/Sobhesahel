<?php

namespace App\Livewire;

use App\Models\Category;
use App\Services\SiteSearchService;
use Livewire\Component;
use Livewire\WithPagination;

class SearchIndex extends Component
{
    use WithPagination;

    public $categories;

    public $query;

    public $options = [
        'category' => 'all',
        'post_type' => 'all',
        'from_date' => '',
        'to_date' => '',
        'order_type' => 'DESC',
    ];

    protected $listeners = [
        'updateFromDate' => 'setFromDate',
        'updateToDate' => 'setToDate',
    ];

    public function mount(): void
    {
        $this->categories = Category::select('id', 'title')->get();
        $this->query = request()->get('q', '');
    }

    public function updatedQuery(): void
    {
        $requestQuery = request()->get('q', '');

        if ($requestQuery !== $this->query) {
            $this->query = $requestQuery;
        }

        $this->resetPage();
    }

    public function updatedOptionsOrderType(): void
    {
        $this->resetPage();
    }

    public function updatedOptions(): void
    {
        $this->resetPage();
    }

    public function setFromDate(string $date): void
    {
        $this->options['from_date'] = $date;
        $this->resetPage();
    }

    public function setToDate(string $date): void
    {
        $this->options['to_date'] = $date;
        $this->resetPage();
    }

    public function getData()
    {
        $searchTerm = request()->get('q', $this->query ?? '');

        if ($searchTerm !== $this->query) {
            $this->query = $searchTerm;
        }

        return app(SiteSearchService::class)->paginate(
            $searchTerm,
            $this->options,
            (int) request()->get('page', 1),
            10
        );
    }

    public function render()
    {
        $requestQuery = request()->get('q', '');

        if ($requestQuery !== $this->query) {
            $this->query = $requestQuery;
        }

        return view('livewire.search-index', [
            'posts' => $this->getData(),
        ]);
    }
}
