import 'package:http/http.dart' as http;
import '../config/constants.dart';

class ShippingService {
  static Future<void> saveProfile({
    required String token,
    required int productId,
    required String profileName,
    required int countryId,
    required String originPostal,
    bool setDefault = true,
    String processingTimeId = 'custom',
    int? processingMin,
    int? processingMax,
    required String shippingRulesJson,
  }) async {
    final uri = Uri.parse("${Constants.baseUrl}/products/$productId/shipping");
    final res = await http.post(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
      body: {
        'profile_name': profileName,
        'set_default': setDefault ? '1' : '0',
        'country_id': countryId.toString(),
        'origin_postal_code': originPostal,
        'processing_time_id': processingTimeId,
        if (processingTimeId == 'custom' && processingMin != null) 'processing_custom_min': processingMin.toString(),
        if (processingTimeId == 'custom' && processingMax != null) 'processing_custom_max': processingMax.toString(),
        'shipping_rules_json': shippingRulesJson,
      },
    );
    if (res.statusCode >= 400) {
      throw Exception('Failed to save shipping: ${res.body}');
    }
  }
}

