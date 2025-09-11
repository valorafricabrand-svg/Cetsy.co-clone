import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/constants.dart';

class SettingsService {
  static Future<String?> getCurrency() async {
    final uri = Uri.parse("${Constants.baseUrl}/settings/currency");
    final res = await http.get(uri, headers: {'Accept':'application/json'});
    if (res.statusCode == 200) {
      final map = jsonDecode(res.body) as Map<String, dynamic>;
      return (map['currency'] as String?)?.toUpperCase();
    }
    return null;
  }

  static Future<void> setCurrency({required String token, required String currency}) async {
    final uri = Uri.parse("${Constants.baseUrl}/settings/currency");
    final res = await http.post(uri,
      headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'},
      body: {'currency': currency.toUpperCase()},
    );
    if (res.statusCode >= 400) {
      throw Exception('Failed to update currency: ${res.body}');
    }
  }

  // Fetch public USD-based exchange rates (fallback if server not available)
  static Future<Map<String, double>> fetchUsdRates() async {
    final uri = Uri.parse('https://api.exchangerate.host/latest?base=USD');
    final res = await http.get(uri, headers: {'Accept': 'application/json'});
    if (res.statusCode == 200) {
      final map = jsonDecode(res.body) as Map<String, dynamic>;
      final rates = (map['rates'] as Map<String, dynamic>?) ?? const {};
      return rates.map((k, v) => MapEntry(k, (v as num).toDouble()));
    }
    throw Exception('Failed to fetch rates');
  }
}
