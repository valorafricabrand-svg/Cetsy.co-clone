// lib/screens/cart_screen.dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../providers/cart_provider.dart';
import 'checkout_screen.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({super.key});

  static const Color cetsyGreen = Color(0xFF198754);

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final fmt = NumberFormat.decimalPattern();

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Cart'),
        actions: [
          if (cart.itemCount > 0)
            IconButton(
              tooltip: 'Clear cart',
              onPressed: () => _confirmClear(context),
              icon: const Icon(Icons.delete_outline),
            ),
        ],
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFEFF7F3), Color(0xFFEAF5F0)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: cart.itemCount == 0
            ? _empty(context)
            : ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  ...cart.items.values.map((item) {
                    final p = item.product;
                    final unitPrice = p.discountPrice ?? p.price;
                    return Card(
                      child: ListTile(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        leading: CircleAvatar(
                          backgroundColor: cetsyGreen.withOpacity(.1),
                          child: const Icon(Icons.shopping_bag, color: cetsyGreen),
                        ),
                        title: Text(p.name, maxLines: 1, overflow: TextOverflow.ellipsis),
                        subtitle: Text('KES ${fmt.format(unitPrice)} each'),
                        trailing: SizedBox(
                          width: 132,
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.end,
                            children: [
                              IconButton(
                                tooltip: 'Decrease',
                                onPressed: () => context.read<CartProvider>().setQty(p.id, item.qty - 1),
                                icon: const Icon(Icons.remove_circle_outline),
                              ),
                              Text('${item.qty}', style: const TextStyle(fontWeight: FontWeight.w700)),
                              IconButton(
                                tooltip: 'Increase',
                                onPressed: () => context.read<CartProvider>().setQty(p.id, item.qty + 1),
                                icon: const Icon(Icons.add_circle_outline),
                              ),
                            ],
                          ),
                        ),
                        onLongPress: () => context.read<CartProvider>().remove(p.id),
                      ),
                    );
                  }),
                  const SizedBox(height: 16),
                  _totalBar(context, cart.total, fmt),
                ],
              ),
      ),
    );
  }

  Widget _empty(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(22),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.shopping_cart_outlined, size: 64, color: Colors.grey.shade500),
            const SizedBox(height: 12),
            Text('Your cart is empty',
                style: TextStyle(fontSize: 18, color: Colors.grey.shade700, fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            Text('Browse products and add items to your cart.',
                textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade600)),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: () => Navigator.popUntil(context, (r) => r.isFirst),
              child: const Text('Continue Shopping'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _totalBar(BuildContext context, double total, NumberFormat fmt) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: const [BoxShadow(blurRadius: 20, color: Color(0x14000000), offset: Offset(0, 10))],
      ),
      child: Row(
        children: [
          const Icon(Icons.receipt_long),
          const SizedBox(width: 10),
          Expanded(
            child: Text('Total: KES ${fmt.format(total)}',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: cetsyGreen)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const CheckoutScreen()),
              );
            },
            child: const Text('Checkout'),
          ),
        ],
      ),
    );
  }

  void _confirmClear(BuildContext context) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Clear cart?'),
        content: const Text('This will remove all items from your cart.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          ElevatedButton(onPressed: () => Navigator.pop(context, true), child: const Text('Clear')),
        ],
      ),
    );
    if (ok == true && context.mounted) {
      context.read<CartProvider>().clear();
    }
  }
}
