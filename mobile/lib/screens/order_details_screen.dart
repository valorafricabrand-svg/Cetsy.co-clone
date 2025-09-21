import 'package:provider/provider.dart';
// import '../providers/currency_provider.dart';
import 'package:flutter/material.dart';
// import 'package:intl/intl.dart';
import '../utils/money_utils.dart';
// import '../config/constants.dart';

import '../providers/auth_provider.dart';
import '../services/order_service.dart';
import '../services/wallet_service.dart';

class OrderDetailsScreen extends StatefulWidget {
  final int orderId;
  const OrderDetailsScreen({super.key, required this.orderId});

  @override
  State<OrderDetailsScreen> createState() => _OrderDetailsScreenState();
}

class _OrderDetailsScreenState extends State<OrderDetailsScreen> {
  Map<String, dynamic>? _order;
  bool _loading = true;
  bool _paying = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final token = context.read<AuthProvider>().token;
      if (token == null) {
        setState(() => _loading = false);
        return;
      }
      try {
        final data = await OrderService.fetchOrder(token, widget.orderId);
        if (mounted) setState(() { _order = data; _loading = false; });
      } catch (_) { if (mounted) setState(() => _loading = false); }
    });
  }

  @override
  Widget build(BuildContext context) {
    // final fmt = NumberFormat.decimalPattern();
    return Scaffold(
      appBar: AppBar(title: Text('Order #${widget.orderId}')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _order == null
              ? const Center(child: Text('Unable to load order'))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    _section(
                      'Summary',
                      [
                        _row('Status', (_order!['status'] ?? '').toString().toUpperCase()),
                        _row('Payment', (_order!['payment_method'] ?? '').toString().toUpperCase()),
                        _row('Subtotal', context.money(((_order!['subtotal'] ?? 0) as num).toDouble())),
                        _row('Total', context.money(((_order!['total_amount'] ?? 0) as num).toDouble())),
                        _row('Date', (_order!['created_at'] ?? '').toString()),
                      ],
                    ),
                    const SizedBox(height: 12),
                    _section(
                      'Shipping',
                      [
                        _row('Name', _order!['shipping']?['full_name'] ?? ''),
                        _row('Phone', _order!['shipping']?['phone'] ?? ''),
                        _row('Email', _order!['shipping']?['email'] ?? ''),
                        _row('Address 1', _order!['shipping']?['address_1'] ?? ''),
                        if ((_order!['shipping']?['address_2'] ?? '').toString().isNotEmpty)
                          _row('Address 2', _order!['shipping']?['address_2'] ?? ''),
                        _row('City/State', '${_order!['shipping']?['city'] ?? ''} ${_order!['shipping']?['state'] ?? ''}'.trim()),
                        if ((_order!['shipping']?['postal_code'] ?? '').toString().isNotEmpty)
                          _row('Postal', _order!['shipping']?['postal_code'] ?? ''),
                      ],
                    ),
                    const SizedBox(height: 12),
                    _itemsSection(),
                    const SizedBox(height: 12),
                    _paymentActions(),
                  ],
                ),
    );
  }

  Widget _section(String title, List<Widget> children) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            ...children,
          ],
        ),
      ),
    );
  }

  Widget _paymentActions() {
    final status = (_order!['status'] ?? 'pending') as String;
    if (status != 'pending') return const SizedBox.shrink();

    // Calculate outstanding amount (fallbacks if API does not provide paid total)
    final total = ((_order!['total_amount'] ?? _order!['total'] ?? 0.0) as num).toDouble();
    double paid = 0.0;
    if (_order!['paid_amount'] != null) {
      paid = ((_order!['paid_amount']) as num).toDouble();
    } else if (_order!['payments'] is List) {
      for (final p in (_order!['payments'] as List)) {
        if (p is Map) {
          if (p['total_amount'] != null) {
            paid += (p['total_amount'] as num).toDouble();
          } else if (p['amount'] != null) {
            paid += (p['amount'] as num).toDouble();
          }
        }
      }
    }
    double outstanding = total - paid;
    if (outstanding < 0) outstanding = 0.0;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text('Pay for this Order', style: TextStyle(fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            Row(children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: _paying ? null : () => _payWithWallet(outstanding),
                  icon: const Icon(Icons.account_balance_wallet_outlined),
                  label: _paying
                      ? const Text('Processing...')
                      : Text('Pay with Wallet  ${context.money(outstanding)}'),
                ),
              ),
            ]),
            const SizedBox(height: 8),
            Row(children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: _paying ? null : () => _startMpesa(outstanding),
                  icon: const Icon(Icons.phone_iphone),
                  label: Text('Pay via M-Pesa  ${context.money(outstanding)}'),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: _paying ? null : () => _startPaypal(outstanding),
                  icon: const Icon(Icons.account_balance),
                  label: Text('Pay via PayPal  ${context.money(outstanding)}'),
                ),
              ),
            ]),
          ],
        ),
      ),
    );
  }

  Future<void> _payWithWallet(double total) async {
    setState(() => _paying = true);
    try {
      final token = context.read<AuthProvider>().token;
      if (token == null) throw Exception('Login required');
      await OrderService.payOrderWithWallet(token, widget.orderId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Order paid with wallet.')));
      // Reload order
      final data = await OrderService.fetchOrder(token, widget.orderId);
      if (mounted) setState(() { _order = data; });
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Payment failed: $e')));
      }
    } finally { if (mounted) setState(() => _paying = false); }
  }

  Future<void> _startMpesa(double total) async {
    final phoneCtrl = TextEditingController(text: context.read<AuthProvider>().user?.phone ?? '');
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Pay via M-Pesa'),
        content: TextField(controller: phoneCtrl, decoration: const InputDecoration(labelText: 'Phone (e.g. 07xx...)')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              setState(() => _paying = true);
              try {
                final token = context.read<AuthProvider>().token;
                if (token == null) throw Exception('Login required');
                final res = await WalletService.startMpesaStk(token: token, amount: total, phone: phoneCtrl.text.trim());
                final ref = (res['ref'] ?? '') as String;
                if (ref.isEmpty) throw Exception('STK init failed');
                // simple poll for up to ~60 seconds
                final started = DateTime.now();
                while (DateTime.now().difference(started).inSeconds < 60) {
                  await Future.delayed(const Duration(seconds: 3));
                  final st = await WalletService.mpesaStatus(token, ref);
                  final s = (st['status'] ?? 'pending') as String;
                  if (s == 'success') {
                    await OrderService.payOrderWithWallet(token, widget.orderId);
                    if (!mounted) return;
                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Payment confirmed')));
                    final data = await OrderService.fetchOrder(token, widget.orderId);
                    if (mounted) setState(() { _order = data; });
                    break;
                  }
                  if (s == 'failed') throw Exception(st['message'] ?? 'Payment failed');
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('M-Pesa error: $e')));
                }
              } finally { if (mounted) setState(() => _paying = false); }
            },
            child: const Text('Start STK'),
          ),
        ],
      ),
    );
  }

  Future<void> _startPaypal(double total) async {
    setState(() => _paying = true);
    try {
      final token = context.read<AuthProvider>().token;
      if (token == null) throw Exception('Login required');
      await WalletService.paypalDeposit(token: token, amount: total);
      await OrderService.payOrderWithWallet(token, widget.orderId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Paid via PayPal')));
      final data = await OrderService.fetchOrder(token, widget.orderId);
      if (mounted) setState(() { _order = data; });
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('PayPal error: $e')));
      }
    } finally { if (mounted) setState(() => _paying = false); }
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          SizedBox(width: 120, child: Text(label, style: const TextStyle(color: Colors.black54))),
          Expanded(child: Text(value, style: const TextStyle(fontWeight: FontWeight.w600))),
        ],
      ),
    );
  }

  Widget _itemsSection() {
    final items = (_order!['items'] as List?) ?? const [];
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Items', style: TextStyle(fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            ...items.map((e) {
              final name = (e['product']?['name'] ?? 'Item').toString();
              final qty = (e['quantity'] as num?)?.toInt() ?? 0;
              final price = double.tryParse('${e['price']}') ?? 0.0;
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Row(
                  children: [
                    const Icon(Icons.shopping_bag_outlined),
                    const SizedBox(width: 8),
                    Expanded(child: Text(name)),
                    Text('x$qty  ${context.money(price)}'),
                  ],
                ),
              );
            }),
          ],
        ),
      ),
    );
  }
}








