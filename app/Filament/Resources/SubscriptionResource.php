<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * اشتراک‌های ویژه (درآمدزایی).
 */
class SubscriptionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $label = 'اشتراک';

    protected static ?string $navigationGroup = 'درآمدزایی';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 3;

    public static function getPluralModelLabel(): string
    {
        return 'اشتراک‌ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('mobile')
                        ->label('شماره موبایل')
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->label('ایمیل (اختیاری)')
                        ->email(),
                    Forms\Components\Select::make('plan_id')
                        ->label('پلن اشتراک')
                        ->options(SubscriptionPlan::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->label('وضعیت')
                        ->options(Subscription::statusOptions())
                        ->default(Subscription::STATUS_PENDING)
                        ->required(),
                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('تاریخ شروع'),
                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('تاریخ پایان'),
                    Forms\Components\Placeholder::make('access_code_view')
                        ->label('کد اشتراک')
                        ->content(fn (?Subscription $record) => $record?->access_code ?? 'پس از ذخیره ساخته می‌شود'),
                    Forms\Components\Placeholder::make('payment_status')
                        ->label('وضعیت پرداخت')
                        ->content(fn (?Subscription $record) => $record?->payment !== null
                            ? (Payment::statusOptions()[$record->payment->status] ?? $record->payment->status) . ' — ' . $record->payment->formattedAmount() . ($record->payment->ref_code ? ' — کد رهگیری: ' . $record->payment->ref_code : '')
                            : 'پرداختی ثبت نشده'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mobile')
                    ->label('شماره موبایل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('پلن')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('access_code')
                    ->label('کد اشتراک')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('کد اشتراک کپی شد'),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Subscription::statusOptions()[$state] ?? 'نامشخص')
                    ->color(fn ($state) => match ($state) {
                        Subscription::STATUS_PENDING => 'warning',
                        Subscription::STATUS_ACTIVE => 'success',
                        Subscription::STATUS_EXPIRED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment.status')
                    ->label('پرداخت')
                    ->badge()
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state) => Payment::statusOptions()[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        Payment::STATUS_PAID => 'success',
                        Payment::STATUS_MANUAL_REVIEW => 'warning',
                        Payment::STATUS_FAILED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('تاریخ انقضا')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('پلن')
                    ->options(SubscriptionPlan::pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(Subscription::statusOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->label('تایید و فعال‌سازی')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Subscription $record) => $record->status === Subscription::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('فعال‌سازی اشتراک')
                    ->modalDescription(fn (Subscription $record) => 'پرداخت '
                        . ($record->payment?->formattedAmount() ?? '—')
                        . ($record->payment?->ref_code ? ' — کد رهگیری: ' . $record->payment->ref_code : ' — کد رهگیری ثبت نشده')
                        . ' تایید و اشتراک از امروز فعال می‌شود')
                    ->action(function (Subscription $record) {
                        $record->payment?->markPaid();
                        $record->activate();

                        $sms_sent = $record->sendAccessCodeSms();

                        Notification::make()
                            ->title('اشتراک فعال شد')
                            ->body('کد اشتراک: ' . $record->access_code
                                . ($sms_sent ? ' (با پیامک برای مشترک ارسال شد)' : ' (ارسال پیامک غیرفعال است — کد را به مشترک اطلاع دهید)'))
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                Tables\Actions\Action::make('extend')
                    ->label('تمدید')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Subscription $record) => in_array($record->status, [Subscription::STATUS_ACTIVE, Subscription::STATUS_EXPIRED], true))
                    ->form([
                        Forms\Components\TextInput::make('days')
                            ->label('تعداد روز تمدید')
                            ->numeric()
                            ->minValue(1)
                            ->default(fn (Subscription $record) => $record->plan?->duration_days ?? 30)
                            ->required(),
                    ])
                    ->action(function (Subscription $record, array $data) {
                        $days = (int) $data['days'];

                        // Extend from the current expiry when still active,
                        // otherwise restart from now.
                        $base = ($record->ends_at !== null && $record->ends_at->isFuture())
                            ? $record->ends_at
                            : now();

                        $record->forceFill([
                            'status' => Subscription::STATUS_ACTIVE,
                            'starts_at' => $record->starts_at ?? now(),
                            'ends_at' => $base->copy()->addDays($days),
                        ])->save();

                        Notification::make()->title('اشتراک تمدید شد')->success()->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('resend_sms')
                        ->label('ارسال مجدد کد با پیامک')
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->visible(fn (Subscription $record) => $record->status === Subscription::STATUS_ACTIVE
                            && (bool) config('payments.sms.enabled', false))
                        ->action(function (Subscription $record) {
                            $sent = $record->sendAccessCodeSms();

                            Notification::make()
                                ->title($sent ? 'پیامک ارسال شد' : 'ارسال پیامک ناموفق بود')
                                ->{$sent ? 'success' : 'danger'}()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'DESC');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
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
