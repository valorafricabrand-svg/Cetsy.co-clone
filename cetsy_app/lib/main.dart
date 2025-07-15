// lib/main.dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';
import 'screens/product_detail_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final authProvider = AuthProvider();
  await authProvider.loadUserFromPrefs();

  runApp(
    ChangeNotifierProvider.value(
      value: authProvider,
      child: const CetsyApp(),
    ),
  );
}

class CetsyApp extends StatelessWidget {
  const CetsyApp({super.key});

  @override
  Widget build(BuildContext context) {
    final isLoggedIn = context.select<AuthProvider, bool>((p) => p.isAuthenticated);

    return MaterialApp(
      title: 'Cetsy App',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(primarySwatch: Colors.green, useMaterial3: true),

      // keep your home: as-is
      home: isLoggedIn ? const HomeScreen() : const LoginScreen(),

      // ← register the detail screen here:
      routes: {
        ProductDetailScreen.route: (_) => const ProductDetailScreen(),
        // add more named routes if you have them:
        // HomeScreen.route: (_) => const HomeScreen(),
        // LoginScreen.route: (_) => const LoginScreen(),
      },
    );
  }
}
