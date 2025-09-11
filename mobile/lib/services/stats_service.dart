import 'dart:convert';
import 'package:http/http.dart' as http;

import '../config/constants.dart';

class StatsService {
  static Future<Map<String, dynamic>> sellerStats(String token) async {
    final url = Uri.parse("${Constants.baseUrl}/seller/stats");
    final res = await http.get(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) {
      return jsonDecode(res.body) as Map<String, dynamic>;
    }
    throw Exception('Failed to load seller stats');
  }
}

