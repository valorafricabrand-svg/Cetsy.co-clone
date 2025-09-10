// lib/models/shipping_profile.dart
class ShippingProfile {
  final int id;
  final String name;
  final double baseRate;

  const ShippingProfile({
    required this.id,
    required this.name,
    required this.baseRate,
  });

  factory ShippingProfile.fromJson(Map<String, dynamic> json) => ShippingProfile(
        id: json['id'],
        name: json['name'] ?? '',
        baseRate: double.tryParse(json['base_rate'].toString()) ?? 0.0,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'base_rate': baseRate,
      };
}
