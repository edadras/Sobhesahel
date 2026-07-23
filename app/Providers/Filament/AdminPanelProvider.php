<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\NoteResource;
use App\Filament\Widgets\CmsChangesWidget;
use App\Filament\Widgets\StatisticsWidget;
use App\Http\Middleware\BlockLoginIps;
use App\Http\Middleware\RedirectWwwToNonWww;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Edwink\FilamentUserActivity\FilamentUserActivityPlugin;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => '#ED3354',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,

            ])
            ->darkMode(true)
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                StatisticsWidget::class,
                CmsChangesWidget::class
            ])
            ->sidebarCollapsibleOnDesktop()
            ->middleware([
//                RedirectWwwToNonWww::class,
                BlockLoginIps::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                \App\Http\Middleware\SetUserPreferences::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \Edwink\FilamentUserActivity\Http\Middleware\RecordUserActivity::class,
            ]) 
            ->renderHook(PanelsRenderHook::BODY_END, fn () => view('filament.shortcuts'))
            ->brandLogo(fn () => Storage::url(rescue(fn () => setting('general.fa_logo'), '', false)))
            ->brandLogoHeight('40px')
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                FilamentEditProfilePlugin::make()
                    ->setTitle('ویرایش پروفایل')
                    ->setNavigationLabel('ویرایش پروفایل')
                    ->setNavigationGroup('تنظیمات')
                    ->setIcon('heroicon-o-user')
                    ->setSort(100)
//                    ->canAccess(fn () => auth()->user()->can('page_EditProfilePage'))
                    ->shouldRegisterNavigation(true)
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(),
//                TwoFactorAuthPlugin::make(),
                FilamentUserActivityPlugin::make(),
                QuickCreatePlugin::make()
                    ->includes([
                        \App\Filament\Resources\NewsResource::class,
                        \App\Filament\Resources\NoteResource::class,
                        \App\Filament\Resources\GalleryResource::class,
                        \App\Filament\Resources\VideoResource::class,
                        \App\Filament\Resources\PodcastResource::class,
                        \App\Filament\Resources\ArchiveResource::class,
                    ])->label('انتشار'),
                EasyFooterPlugin::make()->withSentence('قدرت گرفته از Develogist CMS'),
                // PWA support (tomatophp/filament-pwa v1) — guarded so a
                // missing/removed package can never fatal the panel.
                ...(class_exists(\TomatoPHP\FilamentPWA\FilamentPWAPlugin::class)
                    ? [\TomatoPHP\FilamentPWA\FilamentPWAPlugin::make()]
                    : []),
//                AuthUIEnhancerPlugin::make()
//                    ->showEmptyPanelOnMobile(false)
//                    ->formPanelPosition('right')
//                    ->formPanelWidth('50%')
//                    ->emptyPanelBackgroundImageOpacity('50%')
//                    ->emptyPanelBackgroundImageUrl('https://images.pexels.com/photos/466685/pexels-photo-466685.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'),
//                \BezhanSalleh\FilamentGoogleAnalytics\FilamentGoogleAnalyticsPlugin::make()
            ])
//            ->renderHook(
//            // PanelsRenderHook::BODY_END,
//                PanelsRenderHook::FOOTER,
//                fn() => view('footer')
//            )
            ->authMiddleware([
                Authenticate::class,
            ])->brandName('صبح ساحل')
            ->font('Vazirmatn',provider: GoogleFontProvider::class);
    }
}
