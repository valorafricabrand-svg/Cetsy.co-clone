import 'dart:convert';
import 'package:http/http.dart' as http;

import '../config/constants.dart';
import '../models/user.dart';

class AuthService {
  /// Login with email & password
  static Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/login");

    final response = await http.post(
      url,
      headers: {'Accept': 'application/json'},
      body: {
        'email': email,
        'password': password,
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);

      if (data['token'] != null && data['user'] != null) {
        return {
          'token': data['token'],
          'user': User.fromJson(data['user']),
        };
      } else {
        throw Exception('Invalid login response from server.');
      }
    } else {
      try {
        final error = jsonDecode(response.body);
        throw Exception(error['message'] ?? 'Login failed. Please try again.');
      } catch (_) {
        throw Exception('Login failed. Please check your credentials.');
      }
    }
  }

  /// Register a new user and return token + user
  static Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String userType,
    String? phone,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/register");

    final response = await http.post(
      url,
      headers: {'Accept': 'application/json'},
      body: {
        'name': name,
        'email': email,
        'password': password,
        'user_type': userType,
        if (phone != null) 'phone': phone,
      },
    );

    if (response.statusCode == 201 || response.statusCode == 200) {
      final data = jsonDecode(response.body);

      if (data['token'] != null && data['user'] != null) {
        return {
          'token': data['token'],
          'user': User.fromJson(data['user']),
        };
      } else {
        throw Exception('Invalid register response from server.');
      }
    } else {
      try {
        final error = jsonDecode(response.body);
        throw Exception(error['message'] ?? 'Registration failed.');
      } catch (_) {
        throw Exception('Registration failed. Please check your input.');
      }
    }
  }
}
