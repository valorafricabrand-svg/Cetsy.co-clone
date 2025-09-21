class OrderItemSummary {
  final int productId;
  final String name;
  final int qty;
  final double price;
  const OrderItemSummary({
    required this.productId,
    required this.name,
    required this.qty,
    required this.price,
  });
}

class OrderSummary {
  final int id;
  final String status;
  final double total;
  final double subtotal;
  final String createdAt;
  final String paymentMethod;
  final List<OrderItemSummary> items;

  const OrderSummary({
    required this.id,
    required this.status,
    required this.total,
    required this.subtotal,
    required this.createdAt,
    required this.paymentMethod,
    this.items = const [],
  });

  factory OrderSummary.fromJson(Map<String, dynamic> json) {
    final items = <OrderItemSummary>[];
    if (json['items'] is List) {
      for (final it in (json['items'] as List)) {
        final p = it['product'];
        items.add(OrderItemSummary(
          productId: (it['product_id'] as num).toInt(),
          name: (p is Map && p['name'] != null) ? p['name'] as String : 'Item',
          qty: (it['quantity'] as num).toInt(),
          price: double.tryParse('${it['price']}') ?? 0.0,
        ));
      }
    }
    return OrderSummary(
      id: (json['id'] as num).toInt(),
      status: json['status'] ?? 'pending',
      total: double.tryParse('${json['total_amount']}') ?? 0.0,
      subtotal: double.tryParse('${json['subtotal']}') ?? 0.0,
      createdAt: json['created_at'] ?? '',
      paymentMethod: json['payment_method'] ?? 'unknown',
      items: items,
    );
  }
}

class OrderPage {
  final List<OrderSummary> orders;
  final bool hasNext;
  final int? nextPage;
  final int total;
  final int? currentPage;
  final int? lastPage;
  final int? perPage;

  const OrderPage({
    required this.orders,
    required this.hasNext,
    required this.total,
    this.nextPage,
    this.currentPage,
    this.lastPage,
    this.perPage,
  });

  static int _asInt(dynamic value) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  factory OrderPage.fromPaginatedJson(Map<String, dynamic> json) {
    final list = (json['data'] as List?) ?? const [];
    final orders = list.map((e) => OrderSummary.fromJson(e as Map<String, dynamic>)).toList();
    final meta = (json['meta'] as Map<String, dynamic>?) ?? const {};
    final current = _asInt(meta['current_page']);
    final last = _asInt(meta['last_page']);
    final total = _asInt(meta['total']);
    final perPage = _asInt(meta['per_page']);
    final hasNext = (current > 0 && last > 0) ? current < last : false;
    final next = hasNext ? (current + 1) : null;
    return OrderPage(
      orders: orders,
      hasNext: hasNext,
      total: total > 0 ? total : orders.length,
      nextPage: next,
      currentPage: current > 0 ? current : null,
      lastPage: last > 0 ? last : null,
      perPage: perPage > 0 ? perPage : null,
    );
  }
}


