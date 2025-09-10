import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/order.dart';
import '../providers/auth_provider.dart';
import '../services/order_service.dart';

class OrderHistoryScreen extends StatefulWidget {
  const OrderHistoryScreen({super.key});

  @override
  State<OrderHistoryScreen> createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen> {
  final List<OrderSummary> _orders = [];
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
        final data = await OrderService.fetchOrders(token);
        if (!mounted) return;
        setState(() {
          _orders.clear();
          _orders.addAll(data);
          _loading = false;
        });
      } catch (_) {
        if (mounted) setState(() => _loading = false);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final fmt = NumberFormat.decimalPattern();
    return Scaffold(
      appBar: AppBar(title: const Text('My Orders')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _orders.isEmpty
              ? const Center(child: Text('No orders yet'))
              : ListView.separated(
                  padding: const EdgeInsets.all(12),
                  itemBuilder: (_, i) {
                    final o = _orders[i];
                    return Card(
                      child: ListTile(
                        title: Text('Order #${o.id}'),
                        subtitle: Text(
                          '${o.status.toUpperCase()} • ${o.paymentMethod.toUpperCase()}\n${o.createdAt}',
                        ),
                        isThreeLine: true,
                        trailing: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text('KES ${fmt.format(o.total)}',
                                style: const TextStyle(fontWeight: FontWeight.w800)),
                            Text('Items: ${o.items.length}',
                                style: const TextStyle(color: Colors.black54)),
                          ],
                        ),
                      ),
                    );
                  },
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemCount: _orders.length,
                ),
    );
  }
}

