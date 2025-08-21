import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';

import 'package:cetsy_app/main.dart';
import 'package:cetsy_app/providers/auth_provider.dart';
import 'package:cetsy_app/providers/cart_provider.dart';

void main() {
  testWidgets('renders home tab', (WidgetTester tester) async {
    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<AuthProvider>(create: (_) => AuthProvider()),
          ChangeNotifierProvider<CartProvider>(create: (_) => CartProvider()),
        ],
        child: const CetsyApp(),
      ),
    );

    await tester.pumpAndSettle();

    // The bottom navigation bar should include a Home destination.
    expect(find.text('Home'), findsOneWidget);
  });
}
