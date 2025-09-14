import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/constants.dart';

class MetaService {
  static Future<List<Map<String, dynamic>>> fetchCountries() async {
    final uri = Uri.parse("${Constants.baseUrl}/meta/countries");
    final res = await http.get(uri, headers: { 'Accept': 'application/json' });
    if (res.statusCode == 200) {
      final list = jsonDecode(res.body) as List;
      return List<Map<String, dynamic>>.from(list);
    }
    throw Exception('Failed to load countries');
  }
}

