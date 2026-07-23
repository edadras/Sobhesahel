<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportageOrderResource\Pages;
use App\Models\News;
use App\Models\Payment;
use App\Models\ReportageOrder;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * سفارش‌های رپورتاژ آگهی (درآمدزایی).
 */
class ReportageOrderResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ReportageOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $label = 'سفارش رپورتاژ';

    protected static ?string $navigationGroup = 'درآمدزایی';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 1;

    public static function getPluralModelLabel(): string
    {
        return 'سفارش‌های رپورتاژ';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ReportageOrder::where('status', ReportageOrder::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)->schema([
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('موضوع رپورتاژ')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\Textarea::make('brief')
                            ->label('شرح سفارش')
                            ->rows(6)
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('نام سفارش‌دهنده')
                            ->required(),
                        Forms\Components\TextInput::make('mobile')
                            ->label('شماره موبایل')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('ایمیل (اختیاری)')
                            ->email(),
                        Forms\Components\TextInput::make('link')
                            ->label('لینک کسب‌وکار (اختیاری)')
                            ->url(),
                        Forms\Components\DatePicker::make('desired_publish_date')
                            ->label('تاریخ انتشار پیشنهادی'),
                        Forms\Components\Textarea::make('reject_reason')
                            ->label('دلیل رد سفارش')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('status') === ReportageOrder::STATUS_REJECTED),
                    ])->columnSpan(8),
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\Section::make('وضعیت و قیمت')->schema([
                            Forms\Components\Select::make('status')
                                ->label('وضعیت')
                                ->options(ReportageOrder::statusOptions())
                                ->default(ReportageOrder::STATUS_NEW)
                                ->required(),
                            Forms\Components\TextInput::make('price')
                                ->label('قیمت (تومان)')
                                ->numeric()
                                ->minValue(0),
                            Forms\Components\Placeholder::make('pay_url')
                                ->label('لینک پرداخت مشتری')
                                ->content(fn (?ReportageOrder $record) => $record?->payUrl() ?? '—')
                                ->visible(fn (?ReportageOrder $record) => $record !== null),
                            Forms\Components\Placeholder::make('payment_status')
                                ->label('وضعیت پرداخت')
                                ->content(fn (?ReportageOrder $record) => $record?->payment !== null
                                    ? (Payment::statusOptions()[$record->payment->status] ?? $record->payment->status) . ' — ' . $record->payment->formattedAmount() . ($record->payment->ref_code ? ' — کد رهگیری: ' . $record->payment->ref_code : '')
                                    : 'پرداختی ثبت نشده')
                                ->visible(fn (?ReportageOrder $record) => $record !== null),
                        ]),
                    ])->columnSpan(4),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('موضوع')
                    ->searchable()
                    ->limit(50)
                    ->description(fn (ReportageOrder $record): string => $record->name . ' — ' . $record->mobile),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ReportageOrder::statusOptions()[$state] ?? 'نامشخص')
                    ->color(fn ($state) => match ($state) {
                        ReportageOrder::STATUS_NEW => 'info',
                        ReportageOrder::STATUS_AWAITING_PAYMENT => 'warning',
                        ReportageOrder::STATUS_PAID => 'success',
                        ReportageOrder::STATUS_PUBLISHED => 'success',
                        ReportageOrder::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('قیمت')
                    ->formatStateUsing(fn ($state) => filled($state) ? Payment::faNumber(number_format((int) $state)) . ' تومان' : '—')
                    ->sortable(),

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

                Tables\Columns\TextColumn::make('desired_publish_date')
                    ->label('تاریخ انتشار پیشنهادی')
                    ->jalaliDate('Y/m/d')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(ReportageOrder::statusOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('set_price')
                    ->label('تعیین قیمت')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->visible(fn (ReportageOrder $record) => in_array($record->status, [ReportageOrder::STATUS_NEW, ReportageOrder::STATUS_AWAITING_PAYMENT], true))
                    ->form([
                        Forms\Components\TextInput::make('price')
                            ->label('قیمت (تومان)')
                            ->numeric()
                            ->required()
                            ->minValue(1000)
                            ->default(fn (ReportageOrder $record) => $record->price),
                    ])
                    ->action(function (ReportageOrder $record, array $data) {
                        $record->forceFill([
                            'price' => (int) $data['price'],
                            'status' => ReportageOrder::STATUS_AWAITING_PAYMENT,
                        ])->save();

                        // A price change invalidates a previously started (still
                        // pending) offline payment — the pay page recreates it.
                        if ($record->payment !== null && ! $record->payment->isPaid()) {
                            $record->payment->delete();
                            $record->forceFill(['payment_id' => null])->save();
                        }

                        Notification::make()
                            ->title('قیمت ثبت شد')
                            ->body('لینک پرداخت برای مشتری: ' . $record->payUrl())
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                Tables\Actions\Action::make('confirm_payment')
                    ->label('تایید پرداخت')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (ReportageOrder $record) => $record->payment !== null
                        && ! $record->payment->isPaid()
                        && $record->status === ReportageOrder::STATUS_AWAITING_PAYMENT)
                    ->requiresConfirmation()
                    ->modalHeading('تایید پرداخت سفارش رپورتاژ')
                    ->modalDescription(fn (ReportageOrder $record) => 'مبلغ ' . $record->payment->formattedAmount()
                        . ($record->payment->ref_code ? ' — کد رهگیری: ' . $record->payment->ref_code : ' — کد رهگیری ثبت نشده'))
                    ->action(function (ReportageOrder $record) {
                        $record->payment->markPaid();
                        $record->forceFill(['status' => ReportageOrder::STATUS_PAID])->save();

                        Notification::make()
                            ->title('پرداخت تایید شد')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('convert_to_news')
                    ->label('تبدیل به خبر')
                    ->icon('heroicon-o-newspaper')
                    ->color('info')
                    ->visible(fn (ReportageOrder $record) => $record->status === ReportageOrder::STATUS_PAID
                        && $record->converted_news_id === null)
                    ->requiresConfirmation()
                    ->modalHeading('تبدیل سفارش به خبر پیش‌نویس')
                    ->modalDescription('یک خبر پیش‌نویس با برچسب «رپورتاژ آگهی» ساخته می‌شود تا تحریریه آن را تکمیل و منتشر کند')
                    ->action(function (ReportageOrder $record) {
                        $body = '';

                        foreach (preg_split('/\r\n|\r|\n/', trim((string) $record->brief)) as $line) {
                            $line = trim($line);

                            if ($line !== '') {
                                $body .= '<p>' . e($line) . '</p>' . "\n";
                            }
                        }

                        if (filled($record->link)) {
                            $body .= '<p><a href="' . e($record->link) . '" target="_blank" rel="noopener noreferrer nofollow">' . e($record->link) . '</a></p>' . "\n";
                        }

                        // Attribution required for paid content transparency.
                        $body .= '<p><strong>رپورتاژ آگهی</strong></p>';

                        $news = new News();
                        $news->title = \Illuminate\Support\Str::limit($record->subject, 250, '…');
                        $news->short_description = 'رپورتاژ آگهی';
                        $news->body = $body;
                        $news->status = 'draft';
                        $news->is_published = 0;
                        $news->lang_id = 1;
                        $news->user_id = auth()->id() ?? 1;
                        $news->save();

                        $record->forceFill(['converted_news_id' => $news->id])->save();

                        Notification::make()
                            ->title('خبر پیش‌نویس ساخته شد')
                            ->body('رپورتاژ به صورت پیش‌نویس در بخش اخبار ثبت شد؛ پس از تکمیل و انتشار، سفارش را «منتشرشده» کنید')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_published')
                    ->label('علامت‌گذاری منتشرشده')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ReportageOrder $record) => $record->status === ReportageOrder::STATUS_PAID
                        && $record->converted_news_id !== null)
                    ->requiresConfirmation()
                    ->action(function (ReportageOrder $record) {
                        $record->forceFill(['status' => ReportageOrder::STATUS_PUBLISHED])->save();

                        Notification::make()->title('سفارش منتشرشده علامت خورد')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('رد سفارش')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ReportageOrder $record) => ! in_array($record->status, [ReportageOrder::STATUS_REJECTED, ReportageOrder::STATUS_PUBLISHED], true))
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->label('دلیل رد')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function (ReportageOrder $record, array $data) {
                        $record->forceFill([
                            'status' => ReportageOrder::STATUS_REJECTED,
                            'reject_reason' => $data['reject_reason'],
                        ])->save();

                        Notification::make()->title('سفارش رد شد')->success()->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('edit_news')
                        ->label('ویرایش خبر رپورتاژ')
                        ->icon('heroicon-o-pencil-square')
                        ->visible(fn (ReportageOrder $record) => $record->converted_news_id !== null)
                        ->url(fn (ReportageOrder $record) => NewsResource::getUrl('edit', ['record' => $record->converted_news_id]))
                        ->openUrlInNewTab(true),
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
            'index' => Pages\ListReportageOrders::route('/'),
            'create' => Pages\CreateReportageOrder::route('/create'),
            'edit' => Pages\EditReportageOrder::route('/{record}/edit'),
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
