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

