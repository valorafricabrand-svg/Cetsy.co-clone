import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../utils/currency_utils.dart';
import '../services/settings_service.dart';

class CurrencyProvider extends ChangeNotifier {
  static const _prefsKey = 'currency_code';
  static const _ratesKey = 'currency_rates_json';

  String _code = 'USD';
  String get code => _code;
  String get symbol => CurrencyUtils.symbolFor(_code);
  Map<String, double> _rates = const {'USD': 1.0};
  double get rate => _rates[_code.toUpperCase()] ?? 1.0;
  double convert(double amountUsd) => amountUsd * rate;

  Future<void> load() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final saved = prefs.getString(_prefsKey);
      if (saved != null && saved.isNotEmpty) {
        _code = saved.toUpperCase();
        notifyListeners();
        return;
      }
      final remote = await SettingsService.getCurrency();
      if (remote != null && remote.isNotEmpty) {
        _code = remote.toUpperCase();
        notifyListeners();
      }
      // Load cached rates
      final cachedRates = prefs.getString(_ratesKey);
      if (cachedRates != null && cachedRates.isNotEmpty) {
        final map = Map<String, dynamic>.from(jsonDecode(cachedRates) as Map);
        _rates = map.map((k, v) => MapEntry(k, (v as num).toDouble()));
        notifyListeners();
      }
      // Fetch fresh rates (non-blocking)
      _refreshRates();
    } catch (_) {}
  }

  Future<void> setCode(String code, {String? token, bool updateServer = false}) async {
    _code = code.toUpperCase();
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_prefsKey, _code);
    } catch (_) {}
    if (updateServer && token != null) {
      try { await SettingsService.setCurrency(token: token, currency: _code); } catch (_) {}
    }
  }

  Future<void> _refreshRates() async {
    try {
      final rates = await SettingsService.fetchUsdRates();
      _rates = rates;
      notifyListeners();
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_ratesKey, jsonEncode(rates));
    } catch (_) {}
  }
}

