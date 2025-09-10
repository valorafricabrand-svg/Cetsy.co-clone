import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/product.dart';
import '../config/constants.dart';

class ProductService {
  static Future<List<Product>> fetchProducts({
    int page = 1,
    String? keyword,
    double? minPrice,
    double? maxPrice,
  }) async {
    final queryParams = {
      'page': page.toString(),
      if (keyword != null && keyword.isNotEmpty) 'search': keyword,
      if (minPrice != null) 'min_price': minPrice.toString(),
      if (maxPrice != null) 'max_price': maxPrice.toString(),
    };

    final uri = Uri.parse("${Constants.baseUrl}/products")
        .replace(queryParameters: queryParams);

    final response = await http.get(uri, headers: {
      'Accept': 'application/json',
    });

    if (response.statusCode == 200) {
      final decoded = jsonDecode(response.body);

      if (decoded is List) {
        return decoded.map((item) => Product.fromJson(item)).toList();
      } else if (decoded is Map && decoded['data'] is List) {
        return (decoded['data'] as List)
            .map((item) => Product.fromJson(item))
            .toList();
      } else {
        throw Exception("Unexpected product format.");
      }
    } else {
      throw Exception("Failed to load products");
    }
  }

  static Future<Product> getProduct(int id) async {
    final uri = Uri.parse("${Constants.baseUrl}/products/$id");
    final response = await http.get(uri, headers: {'Accept': 'application/json'});
    if (response.statusCode == 200) {
      final decoded = jsonDecode(response.body);
      return Product.fromJson(decoded as Map<String, dynamic>);
    }
    throw Exception('Failed to load product');
  }
}
