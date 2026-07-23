<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointTransactionResource\Pages;
use App\Models\PointRule;
use App\Models\PointTransaction;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

/**
 * دفتر کل امتیاز اعضا — فقط‌خواندنی (ledger تغییرناپذیر است).
 * جست‌وجو بر اساس عضو، فیلتر قانون و خروجی CSV.
 */
class PointTransactionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PointTransaction::class;

    protected static ?string $navigationIcon = 'fas-coins';

    protected static ?string $label = 'تراکنش امتیاز';

    protected static ?string $navigationGroup = 'باشگاه اعضا';

    protected static ?int $navigationSort = 5;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'تراکنش‌های امتیاز';
    }

    public static function getPluralLabel(): ?string
    {
        return 'تراکنش‌های امتیاز';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    /**
     * برچسب فارسی قوانین برای نمایش کد قانون (در نبود جدول، خود کد).
     *
     * @return array<string, string>
     */
    public static function ruleOptions(): array
    {
        try {
            if (Schema::hasTable('point_rules')) {
                return PointRule::query()->orderBy('sort')->pluck('title', 'code')->all();
            }
        } catch (\Throwable) {
            // جدول در دسترس نیست
        }

        return [];
    }

    public static function table(Table $table): Table
    {
        $ruleOptions = static::ruleOptions();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),

                Tables\Columns\TextColumn::make('member_id')
                    ->label('شناسه عضو')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('points')
                    ->label('امتیاز')
                    ->badge()
                    ->color(fn ($state) => (int) $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => ((int) $state > 0 ? '+' : '') . $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('موجودی پس از تراکنش'),

                Tables\Columns\TextColumn::make('rule_code')
                    ->label('قانون')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => $ruleOptions[$state] ?? $state)
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('توضیح')
                    ->limit(50)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rule_code')
                    ->label('قانون')
                    ->options($ruleOptions),

                Tables\Filters\Filter::make('member')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('member_id')
                            ->label('شناسه عضو')
                            ->numeric(),
                    ])
                    ->query(fn ($query, array $data) => $query->when(
                        filled($data['member_id'] ?? null),
                        fn ($q) => $q->where('member_id', (int) $data['member_id']),
                    ))
                    ->indicateUsing(fn (array $data) => filled($data['member_id'] ?? null)
                        ? 'عضو: ' . $data['member_id']
                        : null),

                Tables\Filters\Filter::make('earned')
                    ->label('فقط کسب‌شده')
                    ->query(fn ($query) => $query->where('points', '>', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('spent')
                    ->label('فقط خرج‌شده')
                    ->query(fn ($query) => $query->where('points', '<', 0))
                    ->toggle(),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPointTransactions::route('/'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
        ];
    }
}
