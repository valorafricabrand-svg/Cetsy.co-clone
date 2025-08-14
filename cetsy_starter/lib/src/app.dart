import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';

import 'webview/cetsy_webview_screen.dart';

class CetsyApp extends StatelessWidget {
  const CetsyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Cetsy',
      debugShowCheckedModeBanner: false,
      themeMode: ThemeMode.system,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF2563EB)),
        useMaterial3: true,
      ),
      darkTheme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF2563EB),
          brightness: Brightness.dark,
        ),
        useMaterial3: true,
      ),
      home: const CetsyWebViewScreen(
        initialUrl: 'https://cetsy.co/dashboard',
      ),
    );
  }
}

/// Simple helper for launching external intents (tel, mailto, maps, etc.)
Future<void> launchExternal(Uri uri) async {
  if (!await canLaunchUrl(uri)) return;
  await launchUrl(uri, mode: LaunchMode.externalApplication);
}

/// Optional: Close app programmatically (Android only). On web this is a no-op.
Future<void> trySystemPop() async {
  try {
    await SystemNavigator.pop();
  } catch (_) {
    // no-op for platforms that do not support it
  }
}
