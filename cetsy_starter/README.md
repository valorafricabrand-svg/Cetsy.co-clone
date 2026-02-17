# Cetsy Starter (Flutter WebView)

Lightweight Flutter wrapper around the Cetsy website with:
- Same‑origin in‑app browsing, external links open in native apps
- Pull‑to‑refresh, top progress strip, tap‑loader overlay
- Offline banner with quick retry
- File upload support on Android (basic)
- Web fallback (redirects the tab to the site when running on Flutter Web)

## Configure the base URL

The app reads the base URL at build time. Default is `https://cetsy.co`.

Override with `--dart-define`:

```
flutter run --dart-define=APP_BASE_URL=https://your-domain.com \
           --dart-define=APP_INITIAL_PATH=/
```

or for release builds:

```
flutter build apk  --dart-define=APP_BASE_URL=https://your-domain.com --release
flutter build ios  --dart-define=APP_BASE_URL=https://your-domain.com --release
```

Notes:
- `APP_INITIAL_PATH` defaults to `/` (home). Set to another path if desired.
- Android allows cleartext for `localhost`/`10.0.2.2` (dev). iOS includes ATS exceptions for the same.

## Common Schemes and External Apps

Links like `tel:`, `sms:`, `mailto:`, `whatsapp:`, and maps (`geo:`/`maps:`) open with native apps.
Other domains (not matching your base URL host) also open externally.

## iOS/Android project notes

- AndroidManifest declares `<queries>` for the common schemes (Android 11+).
- iOS `Info.plist` adds `LSApplicationQueriesSchemes` and dev ATS exceptions.
- If you use only HTTPS in production, no further changes are needed.

## Play Store signing (Android)

Release signing is configured via `android/key.properties`.

1. Generate an upload keystore (from project root):

```powershell
keytool -genkeypair -v -keystore android/keystore.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```

2. Create `android/key.properties` from `android/key.properties.example` and set real passwords.
3. Build signed artifacts:

```powershell
flutter build appbundle --release
flutter build apk --release
```

Output files:
- `build/app/outputs/bundle/release/app-release.aab` (Play Console upload)
- `build/app/outputs/flutter-apk/app-release.apk` (direct distribution/testing)

## Splash screen

This project is set up to use `flutter_native_splash` with brand colors.

Update or confirm the splash images in `assets/app_icons/` then run:

```
flutter pub get
flutter pub run flutter_native_splash:create
```

Config lives in `pubspec.yaml` under `flutter_native_splash:` and uses:
- Light: background `#2563EB` with `assets/app_icons/cetsy_icon_1024.png`
- Dark: background `#0B1220` with same image
- Android 12: uses the 512px variant for the foreground icon
