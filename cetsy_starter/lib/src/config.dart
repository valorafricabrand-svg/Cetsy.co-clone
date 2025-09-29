class AppConfig {
  // Base URL for the web app (no trailing slash required). Override at build time:
  // flutter run --dart-define=APP_BASE_URL=https://your-domain.com
  static const String _base = String.fromEnvironment(
    'APP_BASE_URL',
    defaultValue: 'https://cetsy.co',
  );

  static String get baseUrl {
    // Normalize: ensure scheme present and remove trailing slashes
    var v = _base.trim();
    if (!v.startsWith('http://') && !v.startsWith('https://')) {
      v = 'https://$v';
    }
    while (v.endsWith('/')) {
      v = v.substring(0, v.length - 1);
    }
    return v;
  }

  static String get initialUrl {
    // Optional path override; defaults to root
    const path = String.fromEnvironment('APP_INITIAL_PATH', defaultValue: '/');
    final normalizedPath = path.startsWith('/') ? path : '/$path';
    return '$baseUrl$normalizedPath';
  }

  // Optional custom user agent. Provide via:
  // flutter run --dart-define=APP_UA="Your UA string"
  static const String userAgent = String.fromEnvironment('APP_UA', defaultValue: '');
}
