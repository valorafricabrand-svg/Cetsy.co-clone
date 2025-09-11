// lib/screens/main_shell.dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/cart_provider.dart';
import '../providers/auth_provider.dart';
import 'cart_screen.dart';
import 'home_screen.dart';
import 'product_list_screen.dart';
import 'profile_screen.dart';
// import 'search_screen.dart';
import 'order_history_screen.dart';

class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => MainShellState();
}

class MainShellState extends State<MainShell> {
  static const Color cetsyGreen = Color(0xFF198754);

  int _index = 0;

  late final List<Widget> _tabs;

  void _setIndex(int i) => setState(() => _index = i);

  @override
  void initState() {
    super.initState();
    _tabs = [
      HomeScreen(onShop: () => _setIndex(1)),
      const ProductListScreen(),
      const OrderHistoryScreen(),
      const CartScreen(),
      const ProfileScreen(),
    ];
  }

  @override
  Widget build(BuildContext context) {
    final cartCount = context.watch<CartProvider>().itemCount;

    return Scaffold(
      body: IndexedStack(
        index: _index,
        children: _tabs,
      ),
      floatingActionButton: _buildFab(context),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      bottomNavigationBar: NavigationBar(
        height: 65,
        selectedIndex: _index,
        indicatorColor: cetsyGreen.withOpacity(.12),
        onDestinationSelected: _setIndex,
        destinations: [
          const NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Home',
          ),
          const NavigationDestination(
            icon: Icon(Icons.storefront_outlined),
            selectedIcon: Icon(Icons.storefront),
            label: 'Shop',
          ),
          const NavigationDestination(
            icon: Icon(Icons.receipt_long_outlined),
            selectedIcon: Icon(Icons.receipt_long),
            label: 'Orders',
          ),
          NavigationDestination(
            icon: cartCount > 0
                ? Badge(label: Text('$cartCount'), child: const Icon(Icons.shopping_cart_outlined))
                : const Icon(Icons.shopping_cart_outlined),
            selectedIcon: cartCount > 0
                ? Badge(label: Text('$cartCount'), child: const Icon(Icons.shopping_cart))
                : const Icon(Icons.shopping_cart),
            label: 'Cart',
          ),
          const NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Profile',
          ),
        ],
      ),
    );
  }

  Widget? _buildFab(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final isSeller = auth.user?.userType == 'seller';
    if (!isSeller) return null;
    return FloatingActionButton.extended(
      onPressed: () => Navigator.pushNamed(context, '/add-listing'),
      icon: const Icon(Icons.add_box_outlined),
      label: const Text('Add Listing'),
    );
  }
}

