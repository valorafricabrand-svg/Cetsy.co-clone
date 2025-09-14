import 'dart:convert';
import 'package:http/http.dart' as http;

import '../config/constants.dart';
import '../models/wallet.dart';

class WalletService {
  static Future<Map<String, dynamic>> summary(String token) async {
    final url = Uri.parse("${Constants.baseUrl}/wallet/summary");
    final res = await http.get(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) {
      return jsonDecode(res.body) as Map<String, dynamic>;
    }
    throw Exception('Failed to load wallet summary');
  }

  static Future<Map<String, dynamic>> startMpesaStk({
    required String token,
    required double amount,
    required String phone,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/wallet/deposit/mpesa/stk");
    final res = await http.post(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    }, body: {
      'usd_amount': amount.toString(),
      'phone': phone,
    });
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode == 200 && data['ref'] != null) return data;
    if (res.statusCode == 200 && data['success'] == true) return data;
    throw Exception(data['message'] ?? 'Failed to start STK');
  }

  static Future<void> paypalDeposit({
    required String token,
    required double amount,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/wallet/deposit/paypal");
    final res = await http.post(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    }, body: {
      'amount': amount.toString(),
    });
    if (res.statusCode == 200) return;
    final data = jsonDecode(res.body);
    if (data is Map && data['success'] == true) return;
    throw Exception(data['error'] ?? data['message'] ?? 'PayPal deposit failed');
  }

  static Future<Map<String, dynamic>> getPaypalConfig(String token) async {
    final url = Uri.parse("${Constants.baseUrl}/wallet/paypal/config");
    final res = await http.get(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) {
      return jsonDecode(res.body) as Map<String, dynamic>;
    }
    throw Exception('Failed to load PayPal config');
  }

  static Future<Map<String, dynamic>> mpesaStatus(String token, String ref) async {
    final url = Uri.parse("${Constants.baseUrl}/wallet/deposit/mpesa/status/$ref");
    final res = await http.get(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) {
      return jsonDecode(res.body) as Map<String, dynamic>;
    }
    throw Exception('Failed to fetch status');
  }

  static Future<Map<String, dynamic>> requestPayout({
    required String token,
    required double amount,
    int? paymentMethodId,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/wallet/payout");
    final res = await http.post(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    }, body: {
      'amount': amount.toString(),
      if (paymentMethodId != null) 'payment_method_id': paymentMethodId.toString(),
    });
    final data = jsonDecode(res.body);
    if (res.statusCode == 201 && data is Map<String, dynamic>) {
      return data;
    }
    if (data is Map && data['requires_otp'] == true) return data as Map<String, dynamic>;
    throw Exception((data is Map ? (data['message'] ?? data['error']) : null) ?? 'Payout request failed');
  }

  static Future<void> verifyPayoutOtp({
    required String token,
    required int payoutId,
    required String code,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/wallet/payout/$payoutId/verify");
    final res = await http.post(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    }, body: {
      'code': code,
    });
    if (res.statusCode == 200) return;
    final data = jsonDecode(res.body);
    throw Exception(data['message'] ?? 'Verification failed');
  }

  static Future<void> resendPayoutOtp({
    required String token,
    required int payoutId,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/wallet/payout/$payoutId/resend-otp");
    final res = await http.post(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) return;
    final data = jsonDecode(res.body);
    throw Exception(data['message'] ?? 'Failed to resend code');
  }

  static Future<void> cancelPayout({
    required String token,
    required int payoutId,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/wallet/payout/$payoutId/cancel");
    final res = await http.post(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) return;
    final data = jsonDecode(res.body);
    throw Exception(data['message'] ?? 'Failed to cancel payout');
  }

  static Future<WalletPage> fetchTransactionsPage(
    String token, {
    int page = 1,
    String? type, // 'credit' | 'debit'
    String? from,
    String? to,
  }) async {
    final q = <String, String>{'page': '$page'};
    if (type != null && type.isNotEmpty) q['type'] = type;
    if (from != null) q['from'] = from;
    if (to != null) q['to'] = to;
    final url = Uri.parse("${Constants.baseUrl}/wallet/transactions").replace(queryParameters: q);
    final res = await http.get(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      if (data is Map<String, dynamic>) return WalletPage.fromPaginatedJson(data);
      final list = (data as List?)?.map((e) => WalletTxn.fromJson(e as Map<String, dynamic>)).toList() ?? const <WalletTxn>[];
      return WalletPage(items: list, hasNext: false);
    }
    throw Exception('Failed to load transactions');
  }
}



