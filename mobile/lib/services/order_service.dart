import 'dart:convert';
import 'package:http/http.dart' as http;

import '../config/constants.dart';
import '../models/order.dart';

class OrderService {
  static Future<Map<String, dynamic>> placeOrder({
    required String token,
    required List<Map<String, dynamic>> items,
    required Map<String, dynamic> shipping,
    String paymentMethod = 'cod',
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/orders");
    final res = await http.post(
      url,
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'items': items,
        'shipping': shipping,
        'payment_method': paymentMethod,
      }),
    );
    final data = jsonDecode(res.body);
    if (res.statusCode == 201) return data;
    throw Exception(data['message'] ?? 'Failed to place order');
  }

  static Future<List<OrderSummary>> fetchOrders(String token, {int page = 1}) async {
    final url = Uri.parse("${Constants.baseUrl}/orders?page=$page");
    final res = await http.get(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      // Laravel paginator: { data: [...], links: {...}, meta: {...} }
      final list = (data is Map && data['data'] is List) ? data['data'] as List : (data as List? ?? const []);
      return list.map((e) => OrderSummary.fromJson(e as Map<String, dynamic>)).toList();
    }
    throw Exception('Failed to load orders');
  }

  static Future<OrderPage> fetchOrdersPage(String token, {int page = 1}) async {
    final url = Uri.parse("${Constants.baseUrl}/orders?page=$page");
    final res = await http.get(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      if (data is Map<String, dynamic>) {
        return OrderPage.fromPaginatedJson(data);
      }
      // If API returns bare array
      final list = (data as List?)?.map((e) => OrderSummary.fromJson(e as Map<String, dynamic>)).toList() ?? const <OrderSummary>[];
      final total = list.length;
      return OrderPage(
        orders: list,
        hasNext: false,
        total: total,
        nextPage: null,
        currentPage: total > 0 ? 1 : null,
        lastPage: total > 0 ? 1 : null,
        perPage: total > 0 ? total : null,
      );
    }
    throw Exception('Failed to load orders');
  }

  static Future<Map<String, dynamic>> fetchOrder(String token, int id) async {
    final url = Uri.parse("${Constants.baseUrl}/orders/$id");
    final res = await http.get(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) {
      return jsonDecode(res.body) as Map<String, dynamic>;
    }
    throw Exception('Failed to load order');
  }

  /// Pay an order using wallet balance (API mirrors web route)
  static Future<Map<String, dynamic>> payOrderWithWallet(String token, int orderId) async {
    final url = Uri.parse("${Constants.baseUrl}/orders/$orderId/wallet");
    final res = await http.post(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    final data = jsonDecode(res.body);
    if (res.statusCode >= 200 && res.statusCode < 300) {
      return (data is Map<String, dynamic>) ? data : <String, dynamic>{'success': true};
    }
    throw Exception((data is Map ? (data['message'] ?? data['error']) : null) ?? 'Failed to pay order');
  }
}
