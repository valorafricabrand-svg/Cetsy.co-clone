import 'dart:convert';
import 'package:http/http.dart' as http;

import '../config/constants.dart';

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
}

