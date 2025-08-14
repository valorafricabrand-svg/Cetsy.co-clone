// lib/screens/checkout_screen.dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../providers/cart_provider.dart';

class CheckoutScreen extends StatelessWidget {
  const CheckoutScreen({super.key});

  static const Color cetsyGreen = Color(0xFF198754);

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final fmt = NumberFormat.decimalPattern();

    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFEFF7F3), Color(0xFFEAF5F0)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Expanded(
                child: ListView(
                  children: cart.items.values.map((item) {
                    final p = item.product;
                    final unit = p.discountPrice ?? p.price;
                    return Column(
                      children: [
                        ListTile(
                          leading: const Icon(Icons.shopping_bag),
                          title: Text(p.name),
                          subtitle:
                              Text('x${item.qty}  @ KES ${fmt.format(unit)}'),
                        ),
                        if (p.shippingProfiles.isNotEmpty)
                          Padding(
                            padding:
                                const EdgeInsets.fromLTRB(16, 0, 16, 12),
                            child: DropdownButtonFormField<int>(
                              value: item.shippingProfile?.id ??
                                  p.shippingProfiles.first.id,
                              decoration: const InputDecoration(
                                  labelText: 'Shipping'),
                              items: p.shippingProfiles
                                  .map((sp) => DropdownMenuItem(
                                        value: sp.id,
                                        child: Text(
                                            '${sp.name} (${fmt.format(sp.baseRate)})'),
                                      ))
                                  .toList(),
                              onChanged: (id) {
                                if (id == null) return;
                                final profile = p.shippingProfiles
                                    .firstWhere((sp) => sp.id == id);
                                context
                                    .read<CartProvider>()
                                    .setShippingProfile(p.id, profile);
                              },
                            ),
                          ),
                      ],
                    );
                  }).toList(),
                ),
              ),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  boxShadow: const [
                    BoxShadow(
                      blurRadius: 20,
                      color: Color(0x14000000),
                      offset: Offset(0, 10),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      'Items: KES ${fmt.format(cart.total)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        color: cetsyGreen,
                      ),
                    ),
                    Text(
                      'Shipping: KES ${fmt.format(cart.shippingTotal)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        color: cetsyGreen,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Total: KES ${fmt.format(cart.grandTotal)}',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: cetsyGreen,
                      ),
                    ),
                    const SizedBox(height: 12),
                    ElevatedButton(
                      onPressed: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Order placed (mock).')),
                        );
                        Navigator.pop(context);
                      },
                      child: const Text('Place Order'),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
