<?php

namespace App\Livewire;

use App\Models\CmsChange;
use App\Models\News;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Livewire\Component;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class CmsChangeListComponent extends Component implements HasForms,HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(CmsChange::query())
            ->columns([
                TextColumn::make('title')->label('عنوان'),
                TextColumn::make('date')->label('تاریخ'),
            ])
            ->paginated(false)
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])->defaultSort('id','DESC');
    }

    public function render()
    {
        return view('livewire.cms-change-list-component');
    }
}
