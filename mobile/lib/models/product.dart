// lib/models/product.dart
import 'shipping_profile.dart';
import 'variation.dart';

class Product {
  final int id;
  final String name;
  final String? description;
  final double price;
  final String? image;         // featured image path or full URL
  final double? discountPrice; // nullable → easier null-checks
  final String? type;
  final int? shopId;
  final int? shopUserId;
  final List<String> media; // relative paths or full URLs
  final List<int> mediaIds; // IDs matching media list order
  final List<ShippingProfile> shippingProfiles;
  final List<VariationType> variationTypes;
  final List<Variant> variants;

  // ─────────────────────────  ctor  ─────────────────────────
  const Product({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.image,
    this.discountPrice,
    this.type,
    this.shopId,
    this.shopUserId,
    this.media = const [],
    this.mediaIds = const [],
    this.shippingProfiles = const [],
    this.variationTypes = const [],
    this.variants = const [],
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
        // Prefer server-computed discounted_price (mirrors web), fallback to discount_price column
        discountPrice: (() {
          final dp = json['discounted_price'] ?? json['discount_price'];
          if (dp == null) return null;
          return double.tryParse(dp.toString());
        })(),
        type: json['type'],
        shopId: (json['shop'] is Map && (json['shop']['id'] != null)) ? json['shop']['id'] as int : null,
        shopUserId: (json['shop'] is Map && (json['shop']['user_id'] != null)) ? json['shop']['user_id'] as int : null,
        media: (json['media'] as List?)
                ?.map((e) {
                  final m = e as Map<String, dynamic>;
                  final u = (m['url'] ?? m['image'])?.toString();
                  return u ?? '';
                })
                .where((e) => e.isNotEmpty)
                .toList() ??
            const [],
        mediaIds: (json['media'] as List?)
                ?.map((e) => (e as Map<String,dynamic>)['id'] as int)
                .toList() ??
            const [],
        shippingProfiles: (json['shipping_profiles'] as List?)
                ?.map((e) => ShippingProfile.fromJson(e))
                .toList() ??
            const [],
        variationTypes: (json['variation_types'] as List?)
                ?.map((e) => VariationType.fromJson(e))
                .toList() ??
            const [],
        variants: (json['variants'] as List?)
                ?.map((e) => Variant.fromJson(e))
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
        'type': type,
        'shop_id': shopId,
        'shop_user_id': shopUserId,
        'media': media,
        'media_ids': mediaIds,
        'shipping_profiles':
            shippingProfiles.map((e) => e.toJson()).toList(),
        'variation_types': variationTypes.map((e) => e.toJson()).toList(),
        'variants': variants.map((e) => e.toJson()).toList(),
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
    String? type,
    int? shopId,
    int? shopUserId,
    List<String>? media,
    List<ShippingProfile>? shippingProfiles,
    List<VariationType>? variationTypes,
    List<Variant>? variants,
  }) =>
      Product(
        id: id ?? this.id,
        name: name ?? this.name,
        description: description ?? this.description,
        price: price ?? this.price,
        image: image ?? this.image,
        discountPrice: discountPrice ?? this.discountPrice,
        type: type ?? this.type,
        shopId: shopId ?? this.shopId,
        shopUserId: shopUserId ?? this.shopUserId,
        media: media ?? this.media,
        mediaIds: mediaIds ?? this.mediaIds,
        shippingProfiles: shippingProfiles ?? this.shippingProfiles,
        variationTypes: variationTypes ?? this.variationTypes,
        variants: variants ?? this.variants,
      );

  // For easier debugging
  @override
  String toString() =>
      'Product(id: $id, name: $name, price: $price, discount: $discountPrice, shippingProfiles: ${shippingProfiles.length}, variants: ${variants.length})';
}
