<?php
/**
 * GitHub: RoyalHaze
 * Date: 3/30/25
 * Time: 5:42 PM
 **/

namespace App\Traits;

use Filament\Forms\Components\TextInput;

trait HasContentField
{
    public static function liveTitleAndSlugInputs(string $titleField = 'title', string $slugField = 'slug'): array
    {
        return [
            TextInput::make($titleField)
                ->label('عنوان')
                ->columnSpanFull()
//                ->live(onBlur: true)
//                ->afterStateUpdated(fn($state, callable $set) => $set('slug', ContentTrait::generateSlug($state)))
                ->required(),
            TextInput::make($slugField)
                ->label('SLUG')
                ->helperText('در صورتی که این فید را پر نکنید به صورت خودکار با عنوان پر خواهد شد')
                ->columnSpanFull(),
        ];
    }
}
