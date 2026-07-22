<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OrganizationResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'fas-building';

    protected static ?string $label = 'شرکت / سازمان';

    protected static ?string $navigationGroup = 'کاربران';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'شرکت‌ها و سازمان‌ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('نام')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set, ?string $old, ?Organization $record) {
                        if ($record === null && filled($state)) {
                            $set('slug', Str::slug($state, '-', null));
                        }
                    }),
                Forms\Components\TextInput::make('slug')
                    ->label('نامک (آدرس)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('در آدرس صفحه عمومی استفاده می‌شود: org/نامک'),
                Forms\Components\FileUpload::make('logo')
                    ->label('لوگو')
                    ->image(),
                Forms\Components\TextInput::make('field_of_activity')
                    ->label('حوزه فعالیت'),
                Forms\Components\Textarea::make('description')
                    ->label('توضیحات و معرفی')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('products_services')
                    ->label('محصولات و خدمات')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('website')
                    ->label('وب‌سایت')
                    ->url(),
                Forms\Components\TextInput::make('parent_company')
                    ->label('شرکت مادر'),
                Forms\Components\TextInput::make('employees_count')
                    ->label('تعداد کارکنان')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
                Forms\Components\Repeater::make('social_links')
                    ->label('شبکه‌های اجتماعی')
                    ->schema([
                        Forms\Components\Select::make('network')
                            ->label('شبکه')
                            ->options(Organization::SOCIAL_NETWORKS)
                            ->native(false)
                            ->required(),
                        Forms\Components\TextInput::make('url')->label('آدرس (لینک)')->url()->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('افزودن شبکه اجتماعی')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->circular()->label('لوگو'),
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('field_of_activity')->label('حوزه فعالیت')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
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
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'create',
            'update',
            'delete',
        ];
    }
}
