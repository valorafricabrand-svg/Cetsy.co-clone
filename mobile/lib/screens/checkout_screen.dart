// lib/screens/checkout_screen.dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../providers/cart_provider.dart';
import '../providers/auth_provider.dart';
import '../services/order_service.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  static const Color cetsyGreen = Color(0xFF198754);

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _address1 = TextEditingController();
  final _address2 = TextEditingController();
  final _city = TextEditingController();
  final _state = TextEditingController();
  final _postal = TextEditingController();
  final _countryId = TextEditingController(text: '110');
  String _payment = 'cod';
  bool _submitting = false;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _address1.dispose();
    _address2.dispose();
    _city.dispose();
    _state.dispose();
    _postal.dispose();
    _countryId.dispose();
    super.dispose();
  }

  Future<void> _placeOrder(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final cart = context.read<CartProvider>();
    final auth = context.read<AuthProvider>();
    final token = auth.token;
    if (token == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please login first')));
      return;
    }
    setState(() => _submitting = true);
    try {
      final items = cart.items.entries.map((e) {
        final item = e.value;
        final variantId = item.variantId;
        return {
          'product_id': item.product.id,
          'qty': item.qty,
          if (variantId != null && variantId > 0) 'variant_id': variantId,
          'shipping_profile_id': item.shippingProfile?.id,
        };
      }).toList();

      final shipping = {
        'full_name': _name.text.trim(),
        'email': _email.text.trim(),
        'phone': _phone.text.trim(),
        'country_id': int.tryParse(_countryId.text.trim()) ?? 0,
        'address_1': _address1.text.trim(),
        'address_2': _address2.text.trim().isEmpty ? null : _address2.text.trim(),
        'city': _city.text.trim(),
        'state': _state.text.trim().isEmpty ? null : _state.text.trim(),
        'postal_code': _postal.text.trim().isEmpty ? null : _postal.text.trim(),
      };

      final result = await OrderService.placeOrder(
        token: token,
        items: items,
        shipping: shipping,
        paymentMethod: _payment,
      );

      if (!mounted) return;
      cart.clear();
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Order #${result['order_id']} created')));
      Navigator.pop(context);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

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
                  children: [
                    ...cart.items.entries.map((entry) {
                      final key = entry.key;
                      final item = entry.value;
                      final p = item.product;
                      final unit = item.unitPrice;
                      return Column(
                        children: [
                          ListTile(
                            leading: const Icon(Icons.shopping_bag),
                            title: Text(p.name),
                            subtitle: Text(
                              'x${item.qty}  @ KES ${fmt.format(unit)}' +
                                  (item.variationLabel != null && item.variationLabel!.isNotEmpty
                                      ? '\n${item.variationLabel}'
                                      : ''),
                            ),
                            trailing: IconButton(
                              icon: const Icon(Icons.delete_outline),
                              onPressed: () => context.read<CartProvider>().removeByKey(key),
                            ),
                          ),
                          if (p.shippingProfiles.isNotEmpty)
                            Padding(
                              padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                              child: DropdownButtonFormField<int>(
                                value: item.shippingProfile?.id ?? p.shippingProfiles.first.id,
                                decoration: const InputDecoration(labelText: 'Shipping'),
                                items: p.shippingProfiles
                                    .map((sp) => DropdownMenuItem(
                                          value: sp.id,
                                          child: Text('${sp.name} (${fmt.format(sp.baseRate)})'),
                                        ))
                                    .toList(),
                                onChanged: (id) {
                                  if (id == null) return;
                                  final profile = p.shippingProfiles.firstWhere((sp) => sp.id == id);
                                  context.read<CartProvider>().setShippingProfileByKey(key, profile);
                                },
                              ),
                            ),
                        ],
                      );
                    }).toList(),
                    const SizedBox(height: 8),
                    Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const Divider(),
                          const Text('Shipping Details', style: TextStyle(fontWeight: FontWeight.w700)),
                          const SizedBox(height: 8),
                          TextFormField(controller: _name, decoration: const InputDecoration(labelText: 'Full Name'), validator: (v) => (v==null||v.trim().isEmpty)?'Required':null),
                          const SizedBox(height: 8),
                          TextFormField(controller: _email, decoration: const InputDecoration(labelText: 'Email'), keyboardType: TextInputType.emailAddress, validator: (v)=> (v==null||!v.contains('@'))?'Enter a valid email':null),
                          const SizedBox(height: 8),
                          TextFormField(controller: _phone, decoration: const InputDecoration(labelText: 'Phone'), keyboardType: TextInputType.phone, validator: (v)=> (v==null||v.trim().isEmpty)?'Required':null),
                          const SizedBox(height: 8),
                          TextFormField(controller: _countryId, decoration: const InputDecoration(labelText: 'Country ID'), keyboardType: TextInputType.number, validator: (v)=> (v==null||int.tryParse(v)==null)?'Required':null),
                          const SizedBox(height: 8),
                          TextFormField(controller: _address1, decoration: const InputDecoration(labelText: 'Address 1'), validator: (v)=> (v==null||v.trim().isEmpty)?'Required':null),
                          const SizedBox(height: 8),
                          TextFormField(controller: _address2, decoration: const InputDecoration(labelText: 'Address 2 (optional)')),
                          const SizedBox(height: 8),
                          TextFormField(controller: _city, decoration: const InputDecoration(labelText: 'City'), validator: (v)=> (v==null||v.trim().isEmpty)?'Required':null),
                          const SizedBox(height: 8),
                          TextFormField(controller: _state, decoration: const InputDecoration(labelText: 'State (optional)')),
                          const SizedBox(height: 8),
                          TextFormField(controller: _postal, decoration: const InputDecoration(labelText: 'Postal Code (optional)')),
                          const SizedBox(height: 12),
                          DropdownButtonFormField<String>(
                            value: _payment,
                            decoration: const InputDecoration(labelText: 'Payment Method'),
                            items: const [
                              DropdownMenuItem(value: 'cod', child: Text('Cash on Delivery')),
                              DropdownMenuItem(value: 'mpesa', child: Text('M-Pesa')),
                              DropdownMenuItem(value: 'paypal', child: Text('PayPal')),
                            ],
                            onChanged: (v) => setState(() => _payment = v ?? 'cod'),
                          ),
                          const SizedBox(height: 8),
                        ],
                      ),
                    ),
                  ],
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
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        color: CheckoutScreen.cetsyGreen,
                      ),
                    ),
                    Text(
                      'Shipping: KES ${fmt.format(cart.shippingTotal)}',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        color: CheckoutScreen.cetsyGreen,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Total: KES ${fmt.format(cart.grandTotal)}',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: CheckoutScreen.cetsyGreen,
                      ),
                    ),
                    const SizedBox(height: 12),
                    ElevatedButton(
                      onPressed: _submitting ? null : () => _placeOrder(context),
                      child: _submitting
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                          : const Text('Place Order'),
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
