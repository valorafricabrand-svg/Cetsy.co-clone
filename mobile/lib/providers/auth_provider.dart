import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import '../config/constants.dart';

class AuthProvider with ChangeNotifier {
  User? _user;
  String? _token;

  bool get isAuthenticated => _token != null;
  String? get token => _token;
  User? get user => _user;

  /// Call this after app launches to load saved user/token
  Future<void> loadUserFromPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(Constants.authTokenKey);
    final userJson = prefs.getString(Constants.userKey);

    if (token != null && userJson != null) {
      _token = token;
      _user = User.fromJson(jsonDecode(userJson));
      notifyListeners();
    }
  }

  /// Save token and user data to memory and shared preferences
  Future<void> login(String token, User user) async {
    final prefs = await SharedPreferences.getInstance();

    _token = token;
    _user = user;

    await prefs.setString(Constants.authTokenKey, token);
    await prefs.setString(Constants.userKey, jsonEncode(user.toJson()));

    notifyListeners();
  }

  /// Logout and clear everything
  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(Constants.authTokenKey);
    await prefs.remove(Constants.userKey);

    _token = null;
    _user = null;

    notifyListeners();
  }
}
