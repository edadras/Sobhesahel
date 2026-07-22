<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @livewire(App\Filament\Resources\UserResource\Widgets\UserPostsSummaryWidget::class,['record' => $getState()])
</x-dynamic-component>


