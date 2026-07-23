<?php

namespace App\Filament\Resources\PollResource\Pages;

use App\Filament\Resources\PollResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPoll extends ViewRecord
{
    protected static string $resource = PollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('results')
                ->label('نتایج')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading('نتایج نظرسنجی')
                ->modalContent(fn () => view('filament.poll-results', ['poll' => $this->record]))
                ->modalSubmitAction(false)
                ->modalCancelAction(fn ($action) => $action->label('بستن')),
            Actions\EditAction::make(),
        ];
    }
}
