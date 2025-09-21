import 'package:provider/provider.dart';
import '../utils/money_utils.dart';
// import '../providers/currency_provider.dart';
import 'package:flutter/material.dart';
// import 'package:intl/intl.dart';

import '../models/wallet.dart';
import '../providers/auth_provider.dart';
import '../services/wallet_service.dart';

class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  final List<WalletTxn> _items = [];
  bool _loading = true;
  bool _loadingMore = false;
  bool _hasMore = false;
  int _nextPage = 2;
  String? _filterType; // 'credit' | 'debit' | null
  final _scroll = ScrollController();

  @override
  void initState() {
    super.initState();
    _scroll.addListener(_onScroll);
    WidgetsBinding.instance.addPostFrameCallback((_) => _load(page: 1, reset: true));
  }

  void _onScroll() {
    if (!_hasMore || _loadingMore) return;
    if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 200) {
      _load(page: _nextPage);
    }
  }

  Future<void> _load({int page = 1, bool reset = false}) async {
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      setState(() => _loading = false);
      return;
    }
    if (reset) setState(() => _loading = true);
    if (!reset) setState(() => _loadingMore = true);
    try {
      final result = await WalletService.fetchTransactionsPage(token, page: page, type: _filterType);
      if (!mounted) return;
      setState(() {
        if (reset) _items.clear();
        _items.addAll(result.items);
        _hasMore = result.hasNext;
        _nextPage = result.nextPage ?? (page + 1);
      });
    } finally {
      if (mounted) setState(() { _loading = false; _loadingMore = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Wallet Transactions')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () => _load(page: 1, reset: true),
              child: ListView.builder(
                controller: _scroll,
                padding: const EdgeInsets.all(12),
                itemCount: _items.length + 2,
                itemBuilder: (_, i) {
                  if (i == 0) return _filters();
                  if (i == _items.length + 1) {
                    return _loadingMore
                        ? const Padding(
                            padding: EdgeInsets.symmetric(vertical: 12),
                            child: Center(child: CircularProgressIndicator()),
                          )
                        : const SizedBox.shrink();
                  }
                  final t = _items[i - 1];
                  final net = t.credit - t.debit;
                  final isCredit = net >= 0;
                  return Card(
                    child: ListTile(
                      leading: Icon(isCredit ? Icons.call_received : Icons.call_made,
                          color: isCredit ? Colors.green : Colors.red),
                      title: Text(t.description.isNotEmpty ? t.description : t.method.toUpperCase()),
                      subtitle: Text(t.createdAt),
                      trailing: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(context.money(net), style: const TextStyle(fontWeight: FontWeight.w800)),
                          Text('Bal: ${context.money(t.balance)}', style: const TextStyle(color: Colors.black54)),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
    );
  }

  Widget _filters() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Wrap(
        spacing: 8,
        children: [
          ChoiceChip(
            label: const Text('All'),
            selected: _filterType == null,
            onSelected: (_) => setState(() { _filterType = null; _load(page: 1, reset: true); }),
          ),
          ChoiceChip(
            label: const Text('Credits'),
            selected: _filterType == 'credit',
            onSelected: (_) => setState(() { _filterType = 'credit'; _load(page: 1, reset: true); }),
          ),
          ChoiceChip(
            label: const Text('Debits'),
            selected: _filterType == 'debit',
            onSelected: (_) => setState(() { _filterType = 'debit'; _load(page: 1, reset: true); }),
          ),
        ],
      ),
    );
  }
}




