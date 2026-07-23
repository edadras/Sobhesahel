# صبح ساحل — اپلیکیشن اعضا | Sobhe Sahel Member App

A Flutter mobile app for the **member area** of the Persian (RTL) news site
**Sobhe Sahel** (Hormozgan province). It covers the whole member experience:
login/OTP, dashboard, points & levels, club (streak/missions/wheel), badges,
shop, subscription, digital newspaper archive, library, tourism, notifications
and settings.

## Highlights

- **Glassmorphism UI** — frosted cards, blurred app bar & bottom nav, gradient
  scaffold backgrounds, soft shadows and the site's brand red/plum/gold/teal
  palette (extracted directly from `docs/templates/user-ui/assets/styles.css`).
- **Day / night mode** — Material 3 light & dark themes, toggle in Settings,
  persisted with `shared_preferences` (`light` / `dark` / `system`).
- **Bilingual** — Persian (default, full RTL) and English (LTR). Language toggle
  in Settings, persisted; drives `Directionality` automatically. All strings
  live in the ARB files (`lib/l10n/app_*.arb`).
- **Vazirmatn** font bundled (7 weights, 100–900).
- **Persian digits** for user-facing numbers via `lib/core/persian.dart`.
- **Runs with no backend** — see the mock mode below.

## Running

```bash
cd mobile
flutter pub get      # also generates lib/l10n/app_localizations.dart
flutter run
```

> **Note:** No Flutter/Dart SDK was available in the environment where this code
> was written, so it was authored by convention and **not compile-verified**.
> Run `flutter analyze` after `flutter pub get` and fix any environment-specific
> issues (the l10n file is generated on first `pub get`).

## Configuration — `lib/config.dart`

| Constant | Default | Meaning |
|---|---|---|
| `AppConfig.useMock` | `true` | Run against bundled Persian **mock data** (no backend needed). Set to `false` to hit the real API. |
| `AppConfig.baseUrl` | `https://api.sobhesahel.ir` | API host; every request is prefixed with `/api/member`. |
| `AppConfig.otpLength` | `5` | OTP code length (matches the site's login template). |
| `AppConfig.otpResendSeconds` | `119` | Resend countdown (API rate limit is 1 SMS / 2 min). |

### Mock mode (default)

With `useMock = true` the app is fully demoable offline: every repository
returns realistic Persian sample data (`lib/data/mock/`). In mock mode **any OTP
code is accepted** and any email/password logs you in. Flip `useMock` to `false`
and set `baseUrl` to run against the real Laravel/Sanctum backend described in
`docs/MEMBER_API.md`.

## Architecture

```
lib/
  main.dart                 App root, ProviderScope, MaterialApp.router
  config.dart               Compile-time config (useMock, baseUrl, OTP)
  router.dart               go_router: auth redirect + glass bottom-nav shell
  core/
    persian.dart            Persian-digit / number helpers
    providers.dart          themeModeProvider, localeProvider (persisted)
  l10n/                     app_en.arb, app_fa.arb (single source of strings)
  theme/
    app_colors.dart         Site palette tokens (light + dark) as ThemeExtension
    app_theme.dart          AppTheme.light() / .dark() (Material 3, Vazirmatn)
    glass.dart              GlassContainer, GlassCard, GlassAppBar,
                            GlassBottomNav, GlassScaffold, BrandHeroCard
  widgets/
    common.dart             GradientButton, SectionTitle, ProgressRing, states…
    app_shell.dart          Bottom-nav shell + splash
  data/
    api_client.dart         Dio wrapper (bearer + Accept-Language, {data} unwrap)
    token_storage.dart      flutter_secure_storage token
    models/models.dart      All DTOs (null-safe fromJson)
    repositories/           Abstract repos + Dio-backed implementations
    mock/                   Persian sample data + mock repositories
    providers.dart          Riverpod providers switching mock/real by useMock
  features/<name>/<name>_screen.dart   One screen per file
```

State management is **Riverpod**; navigation is **go_router**. Repositories are
swapped between real and mock by a single `useMock` flag, wired in
`lib/data/providers.dart`.

## Screen status

**Fully implemented:** login (OTP + email tabs), dashboard, settings.

**Stubs** (a feature agent fills the body; class names & file paths are stable —
see the `TODO(feature-agent)` in each file): points, club, badges, shop,
shop detail, subscription, archive, library, tourism, notifications.

Every stub is a self-contained `GlassScaffold` + `GlassAppBar` with the correct
localized title and a `ComingSoonPlaceholder`, and documents which repository /
API endpoints / template page to build against.
