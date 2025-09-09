import 'dart:io';
import 'package:flutter/foundation.dart';

/// Returns the base URL (WITHOUT a trailing `/api`) that the current
/// platform can actually reach.
String resolveBackendHost() {
  if (kIsWeb) {
    // when running `flutter run -d chrome`
    return 'http://127.0.0.1:8000';
  }
  if (Platform.isAndroid) {
    // Android emulator loopback
    return 'http://10.0.2.2:8000';
  }
  if (Platform.isIOS) {
    // iOS simulator loopback
    return 'http://127.0.0.1:8000';
  }
  // real device on same LAN (adjust to your machine’s IP)
  return 'http://192.168.1.42:8000';
}
