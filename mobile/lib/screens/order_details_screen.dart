import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../services/order_service.dart';

class OrderDetailsScreen extends StatefulWidget {
  final int orderId;
  const OrderDetailsScreen({super.key, required this.orderId});

  @override
  State<OrderDetailsScreen> createState() => _OrderDetailsScreenState();
}

class _OrderDetailsScreenState extends State<OrderDetailsScreen> {
  Map<String, dynamic>? _order;
  bool _loading = true;

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
    final fmt = NumberFormat.decimalPattern();
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
                        _row('Subtotal', 'KES ${fmt.format(_order!['subtotal'] ?? 0)}'),
                        _row('Total', 'KES ${fmt.format(_order!['total_amount'] ?? 0)}'),
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
                    _itemsSection(fmt),
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

  Widget _itemsSection(NumberFormat fmt) {
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
                    Text('x$qty  KES ${fmt.format(price)}'),
                  ],
                ),
              );
            }).toList(),
          ],
        ),
      ),
    );
  }
}

