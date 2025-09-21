import 'package:provider/provider.dart';
import '../utils/money_utils.dart';
// lib/screens/checkout_screen.dart
import 'package:flutter/material.dart';
// import 'package:intl/intl.dart';

import '../providers/cart_provider.dart';
import '../providers/auth_provider.dart';
import '../services/order_service.dart';
import 'order_details_screen.dart';

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
  void initState() {
    super.initState();
    // Prefill name, email, phone, and country from authenticated user
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      final u = auth.user;
      if (u != null) {
        if (_name.text.trim().isEmpty) _name.text = u.name;
        if (_email.text.trim().isEmpty) _email.text = u.email;
        if (_phone.text.trim().isEmpty && (u.phone ?? '').isNotEmpty) _phone.text = u.phone!;
        if ((_countryId.text.trim().isEmpty || _countryId.text.trim() == '0') && u.countryId != null) {
          _countryId.text = u.countryId!.toString();
        }
      }
    });
  }

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
    final nav = Navigator.of(context);
    final messenger = ScaffoldMessenger.of(context);
    final token = auth.token;
    if (token == null) {
      messenger.showSnackBar(const SnackBar(content: Text('Please login first')));
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

      final u = auth.user;
      final shipping = {
        // Prefer authenticated user details while placing the order
        'full_name': (u?.name.isNotEmpty == true) ? u!.name : _name.text.trim(),
        'email': (u?.email.isNotEmpty == true) ? u!.email : _email.text.trim(),
        'phone': (u?.phone != null && u!.phone!.isNotEmpty) ? u.phone : _phone.text.trim(),
        'country_id': (u?.countryId ?? int.tryParse(_countryId.text.trim()) ?? 0),
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
      final orderId = (result['order_id'] as num?)?.toInt()
          ?? (result['id'] as num?)?.toInt()
          ?? ((result['order'] is Map) ? ((result['order']['id'] as num?)?.toInt()) : null);

      if (orderId != null) {
        messenger.showSnackBar(SnackBar(content: Text('Order #$orderId created')));
        // Take user to Order Details
        await nav.pushReplacement(
          MaterialPageRoute(builder: (_) => OrderDetailsScreen(orderId: orderId)),
        );
      } else {
        // Fallback to orders list if no id found
        messenger.showSnackBar(const SnackBar(content: Text('Order created')));
        await nav.pushReplacementNamed('/orders');
      }
    } catch (e) {
      messenger.showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    // final fmt = NumberFormat.decimalPattern();

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
                      // use item.unitPrice directly in formatting
                      return Column(
                        children: [
                          ListTile(
                            leading: const Icon(Icons.shopping_bag),
                            title: Text(p.name),
                            subtitle: Text(
                              'x${item.qty}  @ ${context.money(item.unitPrice)}'
                              '${(item.variationLabel != null && item.variationLabel!.isNotEmpty) ? '\n${item.variationLabel}' : ''}',
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
                                initialValue: item.shippingProfile?.id ?? p.shippingProfiles.first.id,
                                decoration: const InputDecoration(labelText: 'Shipping'),
                                items: p.shippingProfiles
                                    .map((sp) => DropdownMenuItem(
                                          value: sp.id,
                                          child: Text('${sp.name} (${context.money(sp.baseRate)})'),
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
                    }),
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
                            initialValue: _payment,
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
                      'Items: ${context.money(cart.total)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        color: CheckoutScreen.cetsyGreen,
                      ),
                    ),
                    Text(
                      'Shipping: ${context.money(cart.shippingTotal)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        color: CheckoutScreen.cetsyGreen,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Total: ${context.money(cart.grandTotal)}',
                      style: const TextStyle(
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












