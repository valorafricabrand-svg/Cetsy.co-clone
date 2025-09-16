/// Global constants used throughout the app
class Constants {
  // Base URL for Laravel API (adjust if you're on a real device or hosting)
  static const String baseUrl = "https://dev.cetsy.co/api"; // Use 10.0.2.2 for Android emulator

  // If you're testing on a real device connected to same network:
  // static const String baseUrl = "http://192.168.1.100:8000/api"; // Replace with your IP

  // Shared Preferences Keys
  static const String authTokenKey = "auth_token";
  static const String userKey = "logged_in_user";

  // Default currency code (used by UI labels)
  static const String currency = "USD";

  // Branding (used for accents in dashboards)
  // Use 0xFF27B105 (seller brand green) by default.
  static const int brandPrimaryHex = 0xFF27B105;
}
