// lib/models/product.dart
import 'shipping_profile.dart';

class Product {
  final int id;
  final String name;
  final String? description;
  final double price;
  final String? image;         // featured image path or full URL
  final double? discountPrice; // nullable → easier null-checks
  final List<ShippingProfile> shippingProfiles;

  // ─────────────────────────  ctor  ─────────────────────────
  const Product({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.image,
    this.discountPrice,
    this.shippingProfiles = const [],
  });

  // ─────────────────────────  Getters  ─────────────────────────
  /// `true` when `discountPrice` is present AND lower than `price`
  bool get hasDiscount =>
      discountPrice != null && discountPrice! > 0 && discountPrice! < price;

  /// The price you should show to the user (discount if available)
  double get finalPrice => hasDiscount ? discountPrice! : price;

  /// Handy helper if your back-end sometimes returns a *full* URL and other
  /// times just a relative path. (Leave resolution logic outside if you prefer.)
  bool get imageIsFullUrl =>
      image != null && (image!.startsWith('http://') || image!.startsWith('https://'));

  // ─────────────────────────  JSON  ─────────────────────────
  factory Product.fromJson(Map<String, dynamic> json) => Product(
        id: json['id'],
        name: json['name'] ?? '',
        description: json['description'],
        price: double.tryParse(json['price'].toString()) ?? 0.0,
        // Try both common keys: featured_image or just image
        image: json['featured_image'] ?? json['image'],
        discountPrice: json['discount_price'] == null
            ? null
            : double.tryParse(json['discount_price'].toString()),
        shippingProfiles: (json['shipping_profiles'] as List?)
                ?.map((e) => ShippingProfile.fromJson(e))
                .toList() ??
            const [],
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'description': description,
        'price': price,
        'image': image,
        'discount_price': discountPrice,
        'shipping_profiles':
            shippingProfiles.map((e) => e.toJson()).toList(),
      };

  /// Converts a list of dynamic maps to a strongly-typed `List<Product>`.
  static List<Product> fromJsonList(List<dynamic> list) =>
      list.map((e) => Product.fromJson(e as Map<String, dynamic>)).toList();

  // ─────────────────────────  copyWith  ─────────────────────────
  Product copyWith({
    int? id,
    String? name,
    String? description,
    double? price,
    String? image,
    double? discountPrice,
    List<ShippingProfile>? shippingProfiles,
  }) =>
      Product(
        id: id ?? this.id,
        name: name ?? this.name,
        description: description ?? this.description,
        price: price ?? this.price,
        image: image ?? this.image,
        discountPrice: discountPrice ?? this.discountPrice,
        shippingProfiles: shippingProfiles ?? this.shippingProfiles,
      );

  // For easier debugging
  @override
  String toString() =>
      'Product(id: $id, name: $name, price: $price, discount: $discountPrice, shippingProfiles: ${shippingProfiles.length})';
}
