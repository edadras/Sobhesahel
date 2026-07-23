<?php

namespace App\Filament\Pages;

use App\Services\AiService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * دستیار هوش مصنوعی — content-team AI toolbox (roadmap: هوش مصنوعی).
 *
 * Tools: ad/campaign copy generation from a brief (AiService::generateAdCopy),
 * plus headline suggestions and summarization for pasted text. Access is
 * gated by Filament Shield (HasPageShield); every call is click-driven and
 * failures only show a Persian notification — nothing here is required for
 * the editorial flow.
 */
class AiAssistant extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'محتوا';

    protected static ?string $navigationLabel = 'دستیار هوش مصنوعی';

    protected static ?string $title = 'دستیار هوش مصنوعی';

    protected static ?string $slug = 'ai-assistant';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.ai-assistant';

    public ?array $data = [];

    public ?string $adResult = null;

    /** @var array<int, string> */
    public array $headlineResults = [];

    public ?string $summaryResult = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('تولید متن تبلیغاتی')
                    ->description('بریف آگهی یا کمپین را بنویسید تا یک متن تبلیغاتی فارسی آماده انتشار تولید شود.')
                    ->schema([
                        Textarea::make('adBrief')
                            ->label('بریف تبلیغ / کمپین')
                            ->placeholder('مثلاً: معرفی جشنواره فروش تابستانه یک فروشگاه در بندرعباس، با تأکید بر تخفیف ۳۰ درصدی و ارسال رایگان…')
                            ->rows(4),
                    ]),
                Section::make('ابزارهای متن')
                    ->description('متن یا خبر خود را اینجا بچسبانید و از ابزار پیشنهاد تیتر یا خلاصه‌سازی استفاده کنید.')
                    ->schema([
                        Textarea::make('sourceText')
                            ->label('متن ورودی')
                            ->placeholder('متن خبر یا هر متن دیگری را اینجا قرار دهید…')
                            ->rows(8),
                    ]),
            ]);
    }

    /**
     * Is the AI backend configured? Used by the blade to disable buttons and
     * show a Persian hint instead of failing silently.
     */
    public function aiEnabled(): bool
    {
        return app(AiService::class)->enabled();
    }

    public function generateAd(): void
    {
        $brief = trim((string) ($this->data['adBrief'] ?? ''));

        if ($brief === '') {
            Notification::make()
                ->title('بریف تبلیغ را وارد کنید')
                ->warning()
                ->send();

            return;
        }

        $result = app(AiService::class)->generateAdCopy($brief);

        if ($result === null) {
            $this->notifyFailure('تولید متن تبلیغاتی ناموفق بود');

            return;
        }

        $this->adResult = $result;

        Notification::make()
            ->title('متن تبلیغاتی تولید شد')
            ->success()
            ->send();
    }

    public function generateHeadlines(): void
    {
        $text = trim((string) ($this->data['sourceText'] ?? ''));

        if ($text === '') {
            Notification::make()
                ->title('ابتدا متن ورودی را وارد کنید')
                ->warning()
                ->send();

            return;
        }

        $headlines = app(AiService::class)->suggestHeadlines('', $text);

        if ($headlines === []) {
            $this->notifyFailure('پیشنهاد تیتر ناموفق بود');

            return;
        }

        $this->headlineResults = $headlines;

        Notification::make()
            ->title('تیترهای پیشنهادی آماده شد')
            ->success()
            ->send();
    }

    public function generateSummary(): void
    {
        $text = trim((string) ($this->data['sourceText'] ?? ''));

        if ($text === '') {
            Notification::make()
                ->title('ابتدا متن ورودی را وارد کنید')
                ->warning()
                ->send();

            return;
        }

        $summary = app(AiService::class)->summarize($text);

        if ($summary === null) {
            $this->notifyFailure('خلاصه‌سازی ناموفق بود');

            return;
        }

        $this->summaryResult = $summary;

        Notification::make()
            ->title('خلاصه متن آماده شد')
            ->success()
            ->send();
    }

    protected function notifyFailure(string $title): void
    {
        Notification::make()
            ->title($title)
            ->body('سرویس هوش مصنوعی در دسترس نیست یا پاسخ معتبری نداد؛ لطفاً دوباره تلاش کنید.')
            ->danger()
            ->send();
    }
}
