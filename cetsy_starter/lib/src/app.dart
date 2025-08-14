import 'package:flutter/material.dart';

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
      home: const CetsyWebViewScreen(initialUrl: 'https://cetsy.co/dashboard'),
    );
  }
}
