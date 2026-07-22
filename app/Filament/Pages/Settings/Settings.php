<?php

namespace App\Filament\Pages\Settings;

use App\Models\Advertise;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class Settings extends BaseSettings
{
    use HasPageShield;

    public static function getNavigationLabel(): string
    {
        return 'تنظیمات';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'تنظیمات';
    }

    public function getTitle(): string
    {
        return 'تنظیمات';
    }

    protected static ?int $navigationSort = 101;

    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('عمومی')
                        ->schema([
                            FileUpload::make('general.fa_logo')
                                ->required()
                                ->image()
                                ->label('لوگو فارسی وبسایت'),
                            TextInput::make('general.fa_brand_name')
                                ->required()
                                ->label('تیر فارسی وبسایت'),
                            TextInput::make('general.fa_brand_about')
                                ->required()
                                ->label('شعار فارسی وبسایت'),
                            Textarea::make('general.fa_brand_description')
                                ->label('توضیحات فارسی وبسایت'),
                            TextInput::make('general.en_brand_name')
                                ->required()
                                ->label('تیر انگلیسی وبسایت'),
                            TextInput::make('general.en_brand_about')
                                ->required()
                                ->label('شعار انگلیسی وبسایت'),
                            Textarea::make('general.en_brand_description')
                                ->label('توضیحات انگلیسی وبسایت'),
                        ]),
                    Tabs\Tab::make('ارتباط با ما')
                        ->schema([
                            TextInput::make('contact.fa_whatsapp')
                                ->required()
                                ->label('لینک واتساپ فارسی'),
                            Textarea::make('contact.fa_address')
                                ->required()
                                ->label('آدرس فارسی'),
                            TextInput::make('contact.fa_phone')
                                ->required()
                                ->numeric()
                                ->label('شماره تماس ایران'),
                            TextInput::make('contact.fa_email')
                                ->email()
                                ->label('ایمیل ایران'),
                            TextInput::make('contact.en_whatsapp')
                                ->required()
                                ->label('لینک واتساپ انگلیسی'),
                            Textarea::make('contact.en_address')
                                ->required()
                                ->label('آدرس انگلیسی'),
                            TextInput::make('contact.en_phone')
                                ->required()
                                ->numeric()
                                ->label('شماره تماس ایمیل'),
                            TextInput::make('contact.en_email')
                                ->email()
                                ->label('ایمیل بین المللی'),
                        ]),
                    Tabs\Tab::make('شبکه های اجتماعی')
                        ->schema([
                            TextInput::make('social.x')
                                ->label('توییتر | X'),
                            TextInput::make('social.telegram')
                                ->label('تلگرام'),
                            TextInput::make('social.instagram')
                                ->label('اینستاگرام'),
                            TextInput::make('social.youtube')
                                ->label('یوتیوب'),
                            TextInput::make('social.linkedin')
                                ->label('Linkedin'),
                            TextInput::make('social.bale')
                                ->label('بله'),
                            TextInput::make('social.eita')
                                ->label('ایتا'),
                        ]),
                    Tabs\Tab::make('درباره ما')
                        ->schema([
                            RichEditor::make('about.fa')->label('درباره ما فارسی')->required(),
                            RichEditor::make('about.en')->label('درباره ما انگلیسی')->required(),
                        ]),
                    Tabs\Tab::make('شخصی سازی')
                        ->schema([
                            Select::make('breaking')
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $query) => \App\Models\News::search($query)
                                    ->take(10)
                                    ->get()
                                    ->pluck('title', 'id'))
                                ->getOptionLabelUsing(fn($value) => \App\Models\News::find($value)?->title ?? 'نامشخص')
                                ->multiple()
//                                ->preload()
                                ->label('اخبار فوری'),
                            Select::make('top_advertise_image')->searchable()->options(Advertise::where('image', '!=', null)->pluck('name', 'id'))
                                ->multiple()->preload()
                                ->label('تبلیغات تصویری منو بالا'),
                            Select::make('button_advertise_image')->searchable()->options(Advertise::where('image', '!=', null)->pluck('name', 'id'))
                                ->multiple()->preload()
                                ->label('تبلیغات تصویری منو پایین'),
                            Select::make('text_advertise')->searchable()->options(Advertise::where('image', null)->pluck('name', 'id'))
                                ->multiple()->preload()
                                ->label('تبلیغات متنی منو'),
                        ]),
                ]),
        ];
    }
}
