import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/constants.dart';

class VariationService {
  static Future<void> saveTypes({required String token, required int productId, required List<Map<String, dynamic>> types}) async {
    final uri = Uri.parse("${Constants.baseUrl}/products/$productId/variations");
    final res = await http.post(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'types': types}),
    );
    if (res.statusCode >= 400) {
      throw Exception('Failed to save variation types: ${res.body}');
    }
  }

  static Future<void> saveVariants({required String token, required int productId, required List<Map<String, dynamic>> variants}) async {
    final uri = Uri.parse("${Constants.baseUrl}/products/$productId/variations");
    final res = await http.post(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'variants': variants}),
    );
    if (res.statusCode >= 400) {
      throw Exception('Failed to save variants: ${res.body}');
    }
  }
}

