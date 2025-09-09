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

  /// Send password reset link to email.
  ///
  /// Laravel API (Fortify/Starter kits) commonly uses POST /forgot-password
  /// and returns 200 with a { "status": "We have emailed your password reset link!" }
  /// message. Some setups may return 202.
  static Future<void> forgotPassword({
    required String email,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/forgot-password");

    final response = await http.post(
      url,
      headers: {'Accept': 'application/json'},
      body: {'email': email},
    );

    // Accept 200/202 as success; decode to surface backend error messages.
    if (response.statusCode == 200 || response.statusCode == 202) {
      // Optionally inspect message:
      // final data = jsonDecode(response.body);
      return;
    } else {
      try {
        final error = jsonDecode(response.body);
        throw Exception(error['message'] ?? 'Password reset request failed.');
      } catch (_) {
        throw Exception('Password reset request failed. Please try again.');
      }
    }
  }

  /// (Optional helper) Complete the reset using token from email.
  /// Typical Laravel route: POST /reset-password
  /// Body: email, token, password, password_confirmation
  static Future<void> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/reset-password");

    final response = await http.post(
      url,
      headers: {'Accept': 'application/json'},
      body: {
        'email': email,
        'token': token,
        'password': password,
        'password_confirmation': passwordConfirmation,
      },
    );

    if (response.statusCode == 200) {
      return;
    } else {
      try {
        final error = jsonDecode(response.body);
        throw Exception(error['message'] ?? 'Password reset failed.');
      } catch (_) {
        throw Exception('Password reset failed. Please try again.');
      }
    }
  }

  /// Change password for the authenticated user
  static Future<void> changePassword({
    required String token,
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/change-password");

    final response = await http.post(
      url,
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: {
        'current_password': currentPassword,
        'password': newPassword,
        'password_confirmation': confirmPassword,
      },
    );

    if (response.statusCode == 200) {
      return;
    }
    try {
      final error = jsonDecode(response.body);
      throw Exception(error['message'] ?? 'Failed to change password.');
    } catch (_) {
      throw Exception('Failed to change password.');
    }
  }
}
