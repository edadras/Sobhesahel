<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryResource\Pages;
use App\Filament\Resources\GalleryResource\RelationManagers;
use App\Models\Author;
use App\Models\Gallery;
use App\Models\News;
use App\Traits\FilamentContent;
use App\Traits\HasContentField;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;

class GalleryResource extends Resource implements HasShieldPermissions
{
    use FilamentContent,HasContentField;

    protected static ?string $model = Gallery::class;

    protected static ?string $navigationIcon = 'fas-images';



    protected static ?string $label = 'گالری';

    protected static ?string $navigationGroup = 'محتوا';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'گالری';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)->schema([
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('sub_title')->label('رو تیتر')->columnSpanFull(),
                        ...self::liveTitleAndSlugInputs(),
                        Forms\Components\RichEditor::make('short_description')->label('متن کوتاه')->columnSpanFull()    ->toolbarButtons([
                            'link',
                            'redo',
                            'strike',
                            'underline',
                            'undo',
                        ]),
                        TiptapEditor::make('body')
                            ->label('متن گالری')
                            ->output(TiptapOutput::Html)
                            ->maxContentWidth('5xl')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('attachments')->multiple()->image()->imageEditor()->label('تصاویر')->columnSpanFull()->reorderable()
                    ])->columnSpan(8),
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\FileUpload::make('image_large')
                            ->label('تصویر گالری')
                            ->columnSpanFull()
                            ->image()
                            ->imageEditor()
                            ->required(),
                        Forms\Components\Section::make('تنظیمات انتشار')->schema([
                            Forms\Components\SpatieTagsInput::make('tags')
                                ->label('تگ‌ها')->type('fa'),
                            Forms\Components\ToggleButtons::make('status')
                                ->options([
                                    'draft' => 'پیش نویس',
                                    'scheduled' => 'زمان بندی شده',
                                    'published' => 'منتشر شده'
                                ])
                                ->icons([
                                    'draft' => 'heroicon-o-pencil',
                                    'scheduled' => 'heroicon-o-clock',
                                    'published' => 'heroicon-o-check-circle',
                                ])
                                ->colors([
                                    'draft' => 'warning',
                                    'scheduled' => 'danger',
                                    'published' => 'success',
                                ])
                                ->label('وضعیت انتشار')
                                ->inline()
                                ->required()
                                ->reactive(),

                            Forms\Components\DateTimePicker::make('publish_at')
                                ->jalali()
                                ->label('تاریخ انتشار')
                                ->visible(fn ($get) => in_array($get('status'), ['scheduled']))
                        ]),
                        Forms\Components\Section::make('نویسنده')->schema([
                            Forms\Components\Select::make('author_id')->options([78 => 'بدون نام'] + Author::where('id','!=',78)->pluck('name', 'id')->toArray())->label('')->searchable()->required(),
                        ])->description('محتوا با نام چه کسی در سایت منتشر شود؟'),
                        Forms\Components\Section::make('سئو')->schema([
                            Forms\Components\TextInput::make('seo_title')
                                ->label('عنوان سئو')
                                ->helperText('عنوان گالری به عنوان پیشفرض'),
                            Forms\Components\Textarea::make('meta_desc')->label('کلمات کلیدی')
                        ])->description('تنظیمات گالری در گوگل')
                    ])->columnSpan(4),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_large')->label('تصویر'),
                Tables\Columns\TextColumn::make('title')->label('عنوان')->searchable()
                    ->description(fn (Gallery $record): string => substr(strip_tags($record->short_description),0,128) . '..'),
                Tables\Columns\TextColumn::make('visits')->label('بازدید'),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت انتشار')->badge()
               ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'پیش نویس',
                        'scheduled' => 'زمان بندی شده',
                        'published' => 'منتشر شده',
                        default => 'نامشخص'
                    })
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'warning',
                        'scheduled' => 'danger',
                        'published' => 'success',
                        default => 'gray'
                    })
                    ->description(fn (Gallery $record): string => Jalalian::fromCarbon(Carbon::parse($record->created_at))->format('H:i Y/m/d')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاریخ بروزرسانی')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->jalaliDateTime('H:i Y/m/d'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('copy_link')
                        ->label('لینک پست')
                        ->icon('heroicon-o-clipboard')
//                        ->action(fn (News $record) => Component::dispatchBrowserEvent('copyToClipboard', ['text' => $record->getUrl()]))
                        ->url(fn($record) => $record->getUrl())
                        ->color('success')
                        ->openUrlInNewTab(true),
                    Tables\Actions\Action::make('copy_link')
                        ->label('لینک کوتاه')
                        ->icon('heroicon-o-clipboard')
//                        ->action(fn (News $record) => Component::dispatchBrowserEvent('copyToClipboard', ['text' => $record->getUrl()]))
                        ->url(fn($record) => $record->getShortUrl())
                        ->openUrlInNewTab(true),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id','DESC');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit' => Pages\EditGallery::route('/{record}/edit'),
        ];
    }
}
