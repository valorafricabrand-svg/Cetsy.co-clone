import 'package:provider/provider.dart';
import '../providers/currency_provider.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../config/constants.dart';
import 'package:provider/provider.dart';

import '../models/order.dart';
import '../providers/auth_provider.dart';
import '../services/order_service.dart';
import 'order_details_screen.dart';

class OrderHistoryScreen extends StatefulWidget {
  const OrderHistoryScreen({super.key});

  @override
  State<OrderHistoryScreen> createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen> {
  final List<OrderSummary> _orders = [];
  bool _loading = true;
  bool _loadingMore = false;
  bool _hasMore = false;
  int _nextPage = 2;
  final _scroll = ScrollController();

  @override
  void initState() {
    super.initState();
    _scroll.addListener(_onScroll);
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final token = context.read<AuthProvider>().token;
      if (token == null) {
        setState(() => _loading = false);
        return;
      }
      try {
        final page = await OrderService.fetchOrdersPage(token, page: 1);
        if (!mounted) return;
        setState(() {
          _orders.clear();
          _orders.addAll(page.orders);
          _hasMore = page.hasNext;
          _nextPage = page.nextPage ?? 2;
          _loading = false;
        });
      } catch (_) {
        if (mounted) setState(() => _loading = false);
      }
    });
  }

  void _onScroll() {
    if (!_hasMore || _loadingMore) return;
    if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 200) {
      _loadMore();
    }
  }

  Future<void> _loadMore() async {
    setState(() => _loadingMore = true);
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      setState(() => _loadingMore = false);
      return;
    }
    try {
      final page = await OrderService.fetchOrdersPage(token, page: _nextPage);
      if (!mounted) return;
      setState(() {
        _orders.addAll(page.orders);
        _hasMore = page.hasNext;
        _nextPage = page.nextPage ?? (_nextPage + 1);
        _loadingMore = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loadingMore = false);
    }
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
              : RefreshIndicator(
                  onRefresh: () async {
                    setState(() { _loading = true; _orders.clear(); _hasMore = false; _nextPage = 2; });
                    final token = context.read<AuthProvider>().token;
                    if (token == null) { setState(() => _loading = false); return; }
                    try {
                      final page = await OrderService.fetchOrdersPage(token, page: 1);
                      if (!mounted) return;
                      setState(() {
                        _orders.addAll(page.orders);
                        _hasMore = page.hasNext;
                        _nextPage = page.nextPage ?? 2;
                        _loading = false;
                      });
                    } catch (_) { if (mounted) setState(() => _loading = false); }
                  },
                  child: ListView.separated(
                    controller: _scroll,
                    padding: const EdgeInsets.all(12),
                  itemBuilder: (_, i) {
                    if (i == _orders.length) {
                      return _loadingMore
                          ? const Padding(
                              padding: EdgeInsets.symmetric(vertical: 12),
                              child: Center(child: CircularProgressIndicator()),
                            )
                          : const SizedBox.shrink();
                    }
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
                            Text('\ ${fmt.format(o.total)}',
                                style: const TextStyle(fontWeight: FontWeight.w800)),
                            Text('Items: ${o.items.length}',
                                style: const TextStyle(color: Colors.black54)),
                          ],
                        ),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => OrderDetailsScreen(orderId: o.id),
                            ),
                          );
                        },
                      ),
                    );
                  },
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemCount: _orders.length + 1,
                  ),
                ),
    );
  }
}






