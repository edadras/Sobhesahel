<?php

namespace App\Livewire;

use App\Models\Archive;
use App\Models\ArchiveCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

class ArchiveIndex extends Component
{
    use WithPagination;

    public $options = [
        'archive' => 'all',
        'type' => 'all',
        'number' => '',
        'from_date' => '',
        'to_date' => '',
    ];

    public $list = [];

    public $types = [];

    public function mount()
    {
        $this->list = ArchiveCategory::distinct()->pluck('title', 'id')->toArray();
        $this->types = Archive::TYPES;
    }

    public function updatedOptions()
    {
        $this->resetPage(); // Reset pagination when filters change
    }

    private function convertToEnglishNumbers($string)
    {
        $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace(
            array_merge($persianNumbers, $arabicNumbers),
            $englishNumbers,
            $string
        );
    }

    private function convertJalaliToGregorian($jalaliDate)
    {
        if (empty($jalaliDate)) {
            return null;
        }

        // Ensure Persian numbers are converted to English
        $jalaliDate = $this->convertToEnglishNumbers($jalaliDate);

        try {
            // Parse Jalali date (assuming format YYYY-MM-DD)
            $jalali = Jalalian::fromFormat('Y/m/d', $jalaliDate);
            // Convert to Gregorian and return in YYYY-MM-DD format
            return $jalali->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            // Handle invalid date gracefully
            return null;
        }
    }


    public function getData()
    {
        $query = Archive::query();

        if ($this->options['archive'] !== 'all') {
            $query->where('category_id', $this->options['archive']);
        }

        if (($this->options['type'] ?? 'all') !== 'all') {
            $query->where('type', $this->options['type']);
        }

        if (!empty($this->options['number'])) {
            // Convert Persian numbers to English for archive_number
            $number = $this->convertToEnglishNumbers($this->options['number']);
            $query->where('archive_number', $number);
        }

        if (!empty($this->options['from_date'])) {
            // Convert Jalali from_date to Gregorian
            $gregorianFromDate = $this->convertJalaliToGregorian($this->options['from_date']);
            if ($gregorianFromDate) {
                $query->whereDate('archive_date', '>=', $gregorianFromDate);
            }
        }

        if (!empty($this->options['to_date'])) {
            // Convert Jalali to_date to Gregorian
            $gregorianToDate = $this->convertJalaliToGregorian($this->options['to_date']);
            if ($gregorianToDate) {
                $query->whereDate('archive_date', '<=', $gregorianToDate);
            }
        }

        return $query->orderBy('sort_order')
            ->orderBy('archive_date', 'DESC')
            ->latest()
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.archive-index', [
            'archives' => $this->getData(),
        ]);
    }
}
