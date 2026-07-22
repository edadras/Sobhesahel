<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Traits\HasCommonNewsTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NewsRelationManager extends RelationManager
{
    use HasCommonNewsTable;

    protected static string $relationship = 'news';

    public static function getPluralModelLabel(): string
    {
        return 'اخبار';
    }

    protected static ?string $title = 'اخبار';

    protected static ?string $label = 'اخبار';

    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns(self::getCommonNewsColumns())
            ->filters([
                Tables\Filters\Filter::make('id')
                    ->form([
                        Forms\Components\TextInput::make('id')->label('کد خبر'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['id'], fn($query, $id) => $query->where('id', $id));
                    }),

                Tables\Filters\Filter::make('publish_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->jalali()->label('از تاریخ'),
                        Forms\Components\DatePicker::make('to')->jalali()->label('تا تاریخ'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($query, $from) => $query->whereDate('publish_at', '>=', $from))
                            ->when($data['to'] ?? null, fn($query, $to) => $query->whereDate('publish_at', '<=', $to));
                    }),
            ])
            ->headerActions([

            ])
            ->actions(
                self::getCommonNewsActions()
            )
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('id','DESC');
    }
}
