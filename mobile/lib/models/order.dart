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

  const OrderPage({
    required this.orders,
    required this.hasNext,
    this.nextPage,
  });

  factory OrderPage.fromPaginatedJson(Map<String, dynamic> json) {
    final list = (json['data'] as List?) ?? const [];
    final orders = list.map((e) => OrderSummary.fromJson(e as Map<String, dynamic>)).toList();
    final meta = json['meta'] as Map<String, dynamic>?;
    final current = (meta?['current_page'] as num?)?.toInt();
    final last = (meta?['last_page'] as num?)?.toInt();
    final hasNext = (current != null && last != null) ? current < last : false;
    final next = hasNext && current != null ? current + 1 : null;
    return OrderPage(orders: orders, hasNext: hasNext, nextPage: next);
  }
}
