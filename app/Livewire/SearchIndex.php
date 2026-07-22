<?php

namespace App\Livewire;

use App\Models\Author;
use App\Models\Category;
use App\Services\SiteSearchService;
use Livewire\Component;
use Livewire\WithPagination;

class SearchIndex extends Component
{
    use WithPagination;

    public $categories;

    public $authors;

    public $query;

    public $options = [
        'category' => 'all',
        'post_type' => 'all',
        'from_date' => '',
        'to_date' => '',
        'order_type' => 'DESC',
        'author' => 'all',
    ];

    protected $listeners = [
        'updateFromDate' => 'setFromDate',
        'updateToDate' => 'setToDate',
    ];

    public function mount(): void
    {
        $this->categories = Category::select('id', 'title')->get();
        $this->authors = Author::select('id', 'name')->orderBy('name')->get();
        $this->query = request()->get('q', '');

        $this->fillOptionsFromRequest();
    }

    protected function fillOptionsFromRequest(): void
    {
        $request = request();

        $sort = strtolower((string) $request->get('sort', 'newest'));
        $this->options['order_type'] = $sort === 'oldest' ? 'ASC' : 'DESC';

        $postType = (string) $request->get('post_type', $request->get('type', 'all'));

        if (in_array($postType, ['news', 'note', 'photo', 'gallery', 'image', 'video', 'podcast'], true)) {
            $this->options['post_type'] = $postType;
        }

        $category = (string) $request->get('category', 'all');

        if ($category !== '' && $category !== 'all') {
            $this->options['category'] = $category;
        }

        $author = (string) $request->get('author', 'all');

        if ($author !== '' && $author !== 'all') {
            $this->options['author'] = $author;
        }

        $this->options['from_date'] = (string) $request->get('from_date', '');
        $this->options['to_date'] = (string) $request->get('to_date', '');
    }

    public function sortUrl(string $sort): string
    {
        $params = array_filter([
            'q' => $this->query,
            'sort' => $sort,
            'post_type' => $this->options['post_type'] !== 'all' ? $this->options['post_type'] : null,
            'category' => $this->options['category'] !== 'all' ? $this->options['category'] : null,
            'author' => $this->options['author'] !== 'all' ? $this->options['author'] : null,
            'from_date' => $this->options['from_date'] !== '' ? $this->options['from_date'] : null,
            'to_date' => $this->options['to_date'] !== '' ? $this->options['to_date'] : null,
        ], fn ($value) => $value !== null && $value !== '');

        return route('website.rtl.search').'?'.http_build_query($params);
    }

    public function updatedQuery(): void
    {
        if (request()->has('q') && request()->get('q') !== $this->query) {
            $this->query = request()->get('q');
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
        $searchTerm = request()->has('q') ? request()->get('q', '') : ($this->query ?? '');

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
        if (request()->has('q') && request()->get('q') !== $this->query) {
            $this->query = request()->get('q');
        }

        return view('livewire.search-index', [
            'posts' => $this->getData(),
            'authorResults' => app(SiteSearchService::class)->searchAuthors((string) $this->query),
        ]);
    }
}
