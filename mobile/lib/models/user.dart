class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String userType;
  final bool isActive;
  final int? countryId;
  final String? photo;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.userType,
    required this.isActive,
    this.phone,
    this.countryId,
    this.photo,
  });

  /// Factory method to convert JSON to User object
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'],
      userType: json['user_type'] ?? 'buyer',
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      countryId: json['country_id'],
      photo: json['photo'],
    );
  }

  /// Convert User object to JSON (useful for caching)
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'user_type': userType,
      'is_active': isActive,
      'country_id': countryId,
      'photo': photo,
    };
  }
}
