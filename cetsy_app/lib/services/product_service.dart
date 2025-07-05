import 'dart:convert';
import 'package:http/http.dart' as http;

import '../config/constants.dart';
import '../models/product.dart';

class ProductService {
  static Future<List<Product>> fetchProducts() async {
    final url = Uri.parse('${Constants.baseUrl}/products');
    final response = await http.get(url, headers: {
      'Accept': 'application/json',
    });

    if (response.statusCode == 200) {
      final List<dynamic> data = jsonDecode(response.body)['data'] ?? jsonDecode(response.body);
      return data.map((item) => Product.fromJson(item)).toList();
    } else {
      throw Exception("Failed to load products");
    }
  }
}
