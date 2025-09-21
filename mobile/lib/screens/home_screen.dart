import 'package:provider/provider.dart';
import 'package:fl_chart/fl_chart.dart';
// import '../providers/currency_provider.dart';
// lib/screens/home_screen.dart
import 'package:flutter/material.dart';

import '../providers/auth_provider.dart';
import '../models/product.dart';
import '../models/order.dart';
import '../models/user.dart';
import '../services/wallet_service.dart';
import '../services/order_service.dart';
import '../services/stats_service.dart';
import 'package:shared_preferences/shared_preferences.dart';
// import '../config/constants.dart';
import '../utils/money_utils.dart';
import 'login_screen.dart';
import 'paypal_checkout_screen.dart';
import 'product_list_screen.dart';
import 'payout_otp_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key, required this.onShop});
  final VoidCallback onShop;

  static const Color cetsyGreen = Color(0xFF198754);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _wallet;
  Map<String, dynamic>? _sellerStats;
  int _ordersCount = 0;
  List<OrderSummary> _recentOrders = const [];
  List<double> _salesSeries = const [];
  List<String> _salesLabels = const [];
  bool _loading = true;

  double _asDouble(dynamic value) {
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  int _asInt(dynamic value) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static const List<Product> demoProducts = [
    Product(id: 1, name: 'Sample T-Shirt', price: 29.99, image: 'assets/images/placeholder.png'),
    Product(id: 2, name: 'Trendy Shoes', price: 59.49, image: 'assets/images/placeholder.png'),
    Product(id: 3, name: 'Classic Watch', price: 120.00, image: 'assets/images/placeholder.png'),
    Product(id: 4, name: 'Elegant Bag', price: 75.00, image: 'assets/images/placeholder.png'),
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated || auth.token == null) {
      setState(() => _loading = false);
      return;
    }
    try {
      final w = await WalletService.summary(auth.token!);
      final orders = await OrderService.fetchOrdersPage(auth.token!, page: 1);
      if (auth.user != null && auth.user!.userType == 'seller') {
        try {
          final stats = await StatsService.sellerStats(auth.token!);
          _sellerStats = stats;
          final chart = (stats['chart'] is Map) ? stats['chart'] as Map : null;
          if (chart != null) {
            final rev = (chart['revenue'] as List?)?.map((e) => _asDouble(e)).toList() ?? const <double>[];
            final lbl = (chart['labels'] as List?)?.map((e) => e.toString()).toList() ?? const <String>[];
            _salesSeries = rev;
            _salesLabels = lbl;
          }
        } catch (_) {}
      }
      if (!mounted) return;
      setState(() {
        _wallet = w;
        _ordersCount = orders.total;
        _recentOrders = orders.orders.take(3).toList();
        if (_salesSeries.isEmpty && _recentOrders.isNotEmpty) {
          _salesSeries = _recentOrders.map((o) => o.total).toList().reversed.toList();
          _salesLabels = _recentOrders.map((o) => '#${o.id}').toList().reversed.toList();
        }
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _logout(BuildContext context) async {
    final auth = context.read<AuthProvider>();
    final nav = Navigator.of(context);
    final messenger = ScaffoldMessenger.of(context);
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Log out?'),
        content: const Text('You will be signed out of your account.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          ElevatedButton(onPressed: () => Navigator.pop(context, true), child: const Text('Log out')),
        ],
      ),
    );
    if (ok != true) return;
    await auth.logout();
    messenger.showSnackBar(const SnackBar(content: Text('Logged out successfully.')));
    await nav.pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (r) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    final isAuth = auth.isAuthenticated;

    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFFEFF7F3), Color(0xFFEAF5F0)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: SingleChildScrollView(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 520),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    if (_loading)
                      const Center(child: CircularProgressIndicator())
                    else if (isAuth && user != null)
                      _buildDashboard(context, user)
                    else
                      _buildGuestCard(context),
                    // Removed featured products section for a cleaner dashboard
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildGuestCard(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: _cardDecoration(),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text('Welcome to Cetsy!', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
          const SizedBox(height: 18),
          Row(children: [
            Expanded(child: ElevatedButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen())), child: const Text('Login'))),
          ]),
          const SizedBox(height: 10),
          Row(children: [
            Expanded(child: OutlinedButton(onPressed: () => Navigator.pushNamed(context, '/register'), child: const Text('Register'))),
          ]),
        ],
      ),
    );
  }

  Widget _buildDashboard(BuildContext context, User user) {
    final bal = context.money(_asDouble(_wallet?['balance']));
    final hold = context.money(_asDouble(_wallet?['on_hold']));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Header
        Container(
          padding: const EdgeInsets.all(22),
          decoration: _cardDecoration(),
          child: Row(
            children: [
              CircleAvatar(radius: 28, backgroundColor: HomeScreen.cetsyGreen.withValues(alpha: .12), child: const Icon(Icons.person, color: HomeScreen.cetsyGreen, size: 30)),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Welcome, ${user.name}!', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                    const SizedBox(height: 4),
                    Text(user.email, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.black54)),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        // Seller mode banner
        if (user.userType == 'seller')
          Container(
            padding: const EdgeInsets.all(12),
            decoration: _cardDecoration(),
            child: Row(
              children: const [
                Icon(Icons.storefront, color: HomeScreen.cetsyGreen),
                SizedBox(width: 10),
                Expanded(
                  child: Text('Seller Mode is active — manage your listings and track sales.',
                      style: TextStyle(fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ),
        const SizedBox(height: 12),
        // Wallet
        Container(
          padding: const EdgeInsets.all(16),
          decoration: _cardDecoration(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Wallet', style: TextStyle(fontWeight: FontWeight.w800)),
              const SizedBox(height: 8),
              Row(children: [
                const Icon(Icons.account_balance_wallet, color: HomeScreen.cetsyGreen),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Balance: $bal'),
                      Text('On Hold: $hold'),
                    ],
                  ),
                ),
              ]),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: ElevatedButton.icon(onPressed: () => _showTopUp(context), icon: const Icon(Icons.add_card), label: const Text('Top Up'))),
                const SizedBox(width: 10),
                Expanded(child: OutlinedButton.icon(onPressed: () => _showPayout(context), icon: const Icon(Icons.money_off_csred), label: const Text('Payout'))),
              ]),
            ],
          ),
        ),
        const SizedBox(height: 12),
        // Sales chart (seller)
        if (user.userType == 'seller' && _salesSeries.isNotEmpty) ...[
          Container(
            padding: const EdgeInsets.all(16),
            decoration: _cardDecoration(),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Sales (recent)', style: TextStyle(fontWeight: FontWeight.w800)),
                const SizedBox(height: 10),
                SizedBox(
                  height: 180,
                  child: LineChart(
                    LineChartData(
                      titlesData: FlTitlesData(
                        leftTitles: AxisTitles(sideTitles: SideTitles(showTitles: true, reservedSize: 28)),
                        rightTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                        topTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                        bottomTitles: AxisTitles(sideTitles: SideTitles(showTitles: true, getTitlesWidget: (v, meta) {
                          final i = v.toInt();
                          if (i < 0 || i >= _salesLabels.length) return const SizedBox.shrink();
                          return Padding(
                            padding: const EdgeInsets.only(top: 4),
                            child: Text(_salesLabels[i], style: const TextStyle(fontSize: 10)),
                          );
                        })),
                      ),
                      gridData: FlGridData(show: true, drawVerticalLine: false),
                      borderData: FlBorderData(show: false),
                      lineBarsData: [
                        LineChartBarData(
                          isCurved: true,
                          color: HomeScreen.cetsyGreen,
                          barWidth: 2,
                          dotData: FlDotData(show: false),
                          spots: List.generate(_salesSeries.length, (i) => FlSpot(i.toDouble(), _salesSeries[i])),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
        ],
        // Orders summary
        Container(
          padding: const EdgeInsets.all(16),
          decoration: _cardDecoration(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                const Icon(Icons.receipt_long_outlined, color: HomeScreen.cetsyGreen),
                const SizedBox(width: 10),
                Expanded(child: Text('Recent Orders ($_ordersCount)', style: const TextStyle(fontWeight: FontWeight.w800))),
                TextButton(onPressed: () => Navigator.pushNamed(context, '/orders'), child: const Text('View all')),
              ]),
              const SizedBox(height: 8),
              if (_recentOrders.isEmpty)
                const Text('No recent orders.', style: TextStyle(color: Colors.black54))
              else ..._recentOrders.map((o) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Row(children: [
                      const Icon(Icons.receipt_long, size: 18, color: HomeScreen.cetsyGreen),
                      const SizedBox(width: 8),
                      Expanded(child: Text('#${o.id} • ${o.status}')),
                      Text(context.money(o.total)),
                    ]),
                  )),
            ],
          ),
        ),
        const SizedBox(height: 12),
        // Recent transactions
        if ((_wallet?['recent'] is List) && (_wallet!['recent'] as List).isNotEmpty) ...[
          Container(
            padding: const EdgeInsets.all(16),
            decoration: _cardDecoration(),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Recent Transactions', style: TextStyle(fontWeight: FontWeight.w800)),
                const SizedBox(height: 8),
                ...((_wallet!['recent'] as List).take(3).map((e) {
                        final credit = _asDouble(e['credit']);
                        final debit = _asDouble(e['debit']);
                        final net = credit - debit;
                        final isCredit = net >= 0;
                        final description = ((e['description'] ?? e['method']) ?? '').toString();
                        return Row(children: [
                          Icon(isCredit ? Icons.call_received : Icons.call_made, color: isCredit ? Colors.green : Colors.red, size: 18),
                          const SizedBox(width: 8),
                          Expanded(child: Text(description)),
                          Text(context.money(net)),
                        ]);
                      })),
                Align(
                  alignment: Alignment.centerRight,
                  child: TextButton(onPressed: () => Navigator.pushNamed(context, '/wallet'), child: const Text('View All')),
                )
              ],
            ),
          ),
          const SizedBox(height: 12),
        ],
        // Seller KPIs + quick links
        if (_sellerStats != null) ...[
          Container(
            padding: const EdgeInsets.all(16),
            decoration: _cardDecoration(),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Seller Dashboard', style: TextStyle(fontWeight: FontWeight.w800)),
                const SizedBox(height: 10),
                Row(children: [
                  Expanded(child: _miniKpi(icon: Icons.inventory_2, label: 'Listings', value: '${_sellerStats!['product_count'] ?? 0}')),
                  const SizedBox(width: 8),
                  Expanded(child: _miniKpi(icon: Icons.pending_actions, label: 'Pending', value: '${_sellerStats!['pending_orders'] ?? 0}')),
                  const SizedBox(width: 8),
                  Expanded(child: _miniKpi(icon: Icons.wallet, label: 'Wallet', value: context.money(_asDouble(_wallet?['balance'])))),
                ]),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(child: OutlinedButton.icon(onPressed: () => Navigator.pushNamed(context, '/add-listing'), icon: const Icon(Icons.add_box_outlined), label: const Text('Add Listing'))),
                  const SizedBox(width: 8),
                  Expanded(child: OutlinedButton.icon(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ProductListScreen())), icon: const Icon(Icons.storefront_outlined), label: const Text('Manage'))),
                  const SizedBox(width: 8),
                  Expanded(child: OutlinedButton.icon(onPressed: () => Navigator.pushNamed(context, '/orders'), icon: const Icon(Icons.receipt_long_outlined), label: const Text('Orders'))),
                ]),
              ],
            ),
          ),
          const SizedBox(height: 12),
        ],
        // Shop now + Digital shortcut
        Row(children: [
          Expanded(
            child: ElevatedButton.icon(
              onPressed: widget.onShop,
              icon: const Icon(Icons.shopping_bag),
              label: const Text('Shop Now'),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: OutlinedButton.icon(
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => const ProductListScreen(initialType: 'digital'),
                ),
              ),
              icon: const Icon(Icons.cloud_download_outlined),
              label: const Text('Digital Downloads'),
            ),
          ),
        ]),
        const SizedBox(height: 10),
        // Quick shortcuts: Products & Services
        Row(children: [
          Expanded(
            child: OutlinedButton.icon(
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => const ProductListScreen(initialType: 'physical'),
                ),
              ),
              icon: const Icon(Icons.storefront_outlined),
              label: const Text('Products'),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: OutlinedButton.icon(
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => const ProductListScreen(initialType: 'service'),
                ),
              ),
              icon: const Icon(Icons.design_services_outlined),
              label: const Text('Services'),
            ),
          ),
        ]),
      ],
    );
  }

  BoxDecoration _cardDecoration() => BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: const [BoxShadow(blurRadius: 28, offset: Offset(0, 16), color: Color(0x1A000000))],
      );

  Widget _miniKpi({required IconData icon, required String label, required String value}) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: HomeScreen.cetsyGreen.withValues(alpha: .12)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: HomeScreen.cetsyGreen),
          const SizedBox(height: 6),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 2),
          Text(label, style: const TextStyle(color: Colors.black54)),
        ],
      ),
    );
  }

  Widget _buildProductGrid() {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: demoProducts.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, crossAxisSpacing: 12, mainAxisSpacing: 12, childAspectRatio: 0.75),
      itemBuilder: (_, i) {
        final p = demoProducts[i];
        return Card(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            Expanded(
              child: ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(14)),
                child: Image.asset(p.image ?? 'assets/images/placeholder.png', fit: BoxFit.cover),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(p.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600)),
                const SizedBox(height: 4),
                Text(context.money(p.price), style: const TextStyle(color: HomeScreen.cetsyGreen, fontWeight: FontWeight.w700)),
              ]),
            ),
          ]),
        );
      },
    );
  }

  void _showTopUp(BuildContext context) {
    final amountCtrl = TextEditingController();
    final phoneCtrl = TextEditingController(text: context.read<AuthProvider>().user?.phone ?? '');
    String method = 'mpesa';
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(18))),
      builder: (_) => StatefulBuilder(
        builder: (ctx, setModal) {
          // Load last used method once when the modal builds
          () async {
            try {
              final prefs = await SharedPreferences.getInstance();
              final last = prefs.getString('last_topup_method');
              if (last == 'mpesa' || last == 'paypal') {
                setModal(() { method = last!; });
              }
            } catch (_) {}
          }();
          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text('Top Up Wallet', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      ChoiceChip(
                        label: const Text('M-Pesa'),
                        selected: method == 'mpesa',
                        onSelected: (_) => setModal(() => method = 'mpesa'),
                      ),
                      const SizedBox(width: 8),
                      ChoiceChip(
                        label: const Text('PayPal'),
                        selected: method == 'paypal',
                        onSelected: (_) => setModal(() => method = 'paypal'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: amountCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Amount (USD)'),
                  ),
                  if (method == 'mpesa') ...[
                    const SizedBox(height: 8),
                    TextField(
                      controller: phoneCtrl,
                      keyboardType: TextInputType.phone,
                      decoration: const InputDecoration(labelText: 'Phone (07.. or 2547..)'),
                    ),
                  ],
                  const SizedBox(height: 12),
                  ElevatedButton(
                    onPressed: () async {
                      final messenger = ScaffoldMessenger.of(context);
                      final token = context.read<AuthProvider>().token;
                      if (token == null) return;
                      final amt = double.tryParse(amountCtrl.text.trim());
                      if (amt == null || amt <= 0) return;
                      Navigator.pop(ctx);
                      try {
                        final nav = Navigator.of(context);
                        final messenger = ScaffoldMessenger.of(context);
                        // persist last used method
                        try {
                          final prefs = await SharedPreferences.getInstance();
                          await prefs.setString('last_topup_method', method);
                        } catch (_) {}
                        if (method == 'paypal') {
                          final ok = await nav.push(
                            MaterialPageRoute(
                              builder: (_) => PaypalCheckoutScreen(amountUsd: amt),
                            ),
                          );
                          if (ok == true) {
                            messenger.showSnackBar(
                              const SnackBar(content: Text('PayPal payment completed')),
                            );
                            _load();
                          }
                          return;
                        }
                        // M-Pesa STK flow
                        final phone = phoneCtrl.text.trim();
                        if (phone.isEmpty) return;
                        final res = await WalletService.startMpesaStk(token: token, amount: amt, phone: phone);
                        messenger.showSnackBar(
                          const SnackBar(content: Text('STK sent. Awaiting payment...')),
                        );
                        final ref = (res['ref'] ?? '') as String;
                        if (ref.isNotEmpty) {
                          final started = DateTime.now();
                          while (DateTime.now().difference(started).inSeconds < 60) {
                            await Future.delayed(const Duration(seconds: 3));
                            try {
                              final st = await WalletService.mpesaStatus(token, ref);
                              final status = (st['status'] ?? 'pending') as String;
                              if (status == 'success') {
                                messenger.showSnackBar(
                                  const SnackBar(content: Text('Payment confirmed')),
                                );
                                _load();
                                break;
                              } else if (status == 'failed') {
                                messenger.showSnackBar(
                                  SnackBar(content: Text('Payment failed: ${st['message'] ?? ''}')),
                                );
                                break;
                              }
                            } catch (_) {}
                          }
                        } else {
                          _load();
                        }
                      } catch (e) {
                        messenger.showSnackBar(SnackBar(content: Text('Error: $e')));
                      }
                    },
                    child: const Text('Top Up'),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  void _showPayout(BuildContext context) {
    final amountCtrl = TextEditingController();
    final max = _asDouble(_wallet?['payout']?['max_amount']);
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Request Payout'),
        content: TextField(controller: amountCtrl, keyboardType: TextInputType.number, decoration: InputDecoration(labelText: 'Amount (Max ${max.toStringAsFixed(2)})')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              final token = context.read<AuthProvider>().token;
              if (token == null) return;
              final amt = double.tryParse(amountCtrl.text.trim());
              if (amt == null || amt <= 0) return;
              Navigator.pop(context);
              try {
                final resp = await WalletService.requestPayout(token: token, amount: amt);
                if (!mounted) return;
                if ((resp['requires_otp'] ?? false) == true) {
                  final payoutId = (resp['payout_id'] ?? 0) as int;
                  final nav = Navigator.of(context);
                  await nav.push(
                    MaterialPageRoute(
                      builder: (_) => PayoutOtpScreen(payoutId: payoutId),
                    ),
                  );
                  // After returning, refresh wallet summary
                  _load();
                } else {
                  final messenger = ScaffoldMessenger.of(context);
                  messenger.showSnackBar(const SnackBar(content: Text('Payout requested')));
                  _load();
                }
              } catch (e) {
                final messenger = ScaffoldMessenger.of(context);
                messenger.showSnackBar(SnackBar(content: Text('Error: $e')));
              }
            },
            child: const Text('Submit'),
          ),
        ],
      ),
    );
  }
}








// ignore_for_file: prefer_const_constructors, use_build_context_synchronously, unused_element







































