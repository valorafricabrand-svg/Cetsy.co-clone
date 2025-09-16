import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';
import 'package:mime/mime.dart';

import '../config/constants.dart';
import '../models/user.dart';

// import 'package:flutter/foundation.dart';
class UserService {
  static Future<User> fetchMe(String token) async {
    final url = Uri.parse("${Constants.baseUrl}/user");
    final res = await http.get(url, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      if (data is Map<String, dynamic> && data['id'] != null) {
        return User.fromJson(data);
      }
    }
    throw Exception('Failed to load user');
  }

  static Future<User> updateProfile({
    required String token,
    required String name,
    String? phone,
    File? photo,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/profile");
    if (photo == null) {
      final res = await http.post(url, headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      }, body: {
        'name': name,
        if (phone != null) 'phone': phone,
      });
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['user'] != null) {
        return User.fromJson(data['user']);
      }
      throw Exception(data['message'] ?? 'Failed to update profile');
    } else {
      final req = http.MultipartRequest('POST', url)
        ..headers['Authorization'] = 'Bearer $token'
        ..headers['Accept'] = 'application/json'
        ..fields['name'] = name;
      if (phone != null) req.fields['phone'] = phone;

      final mimeType = lookupMimeType(photo.path) ?? 'image/jpeg';
      req.files.add(await http.MultipartFile.fromPath(
        'photo',
        photo.path,
        contentType: MediaType.parse(mimeType),
      ));
      final streamed = await req.send();
      final body = await streamed.stream.bytesToString();
      final data = jsonDecode(body);
      if (streamed.statusCode == 200 && data['user'] != null) {
        return User.fromJson(data['user']);
      }
      throw Exception(data['message'] ?? 'Failed to update profile');
    }
  }

  /// Upgrade current authenticated account to seller (if supported by backend)
  /// Tries POST /seller/upgrade and returns the updated user. If the API
  /// responds without a user object, it fetches the current user.
  static Future<User> upgradeToSeller(String token) async {
    final url = Uri.parse("${Constants.baseUrl}/seller/upgrade");
    final res = await http.post(
      url,
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      if (data is Map<String, dynamic> && data['user'] != null) {
        return User.fromJson(data['user']);
      }
      // If response doesn't include user, fetch it
      return fetchMe(token);
    }
    try {
      final err = jsonDecode(res.body);
      throw Exception(err['message'] ?? 'Upgrade failed');
    } catch (_) {
      throw Exception('Upgrade failed');
    }
  }

  static Future<bool> changeEmail({
    required String token,
    required String currentPassword,
    required String email,
  }) async {
    final url = Uri.parse("${Constants.baseUrl}/change-email");
    final res = await http.post(
      url,
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: {
        'current_password': currentPassword,
        'email': email,
      },
    );
    final data = jsonDecode(res.body);
    if (res.statusCode == 200) {
      return data['relogin_required'] == true;
    }
    throw Exception(data['message'] ?? 'Failed to change email');
  }
}

