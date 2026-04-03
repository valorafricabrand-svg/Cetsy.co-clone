import 'shipping_profile.dart';
import 'variation.dart';

class Product {
  final int id;
  final String name;
  final String? description;
  final double price;
  final String? image;
  final String? thumbnailUrl;
  final String? previewThumbnailUrl;
  final String? previewImageUrl;
  final double? discountPrice;
  final String? type;
  final int? shopId;
  final int? shopUserId;
  final List<String> media;
  final List<int> mediaIds;
  final List<ShippingProfile> shippingProfiles;
  final List<VariationType> variationTypes;
  final List<Variant> variants;

  const Product({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.image,
    this.thumbnailUrl,
    this.previewThumbnailUrl,
    this.previewImageUrl,
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

  bool get hasDiscount =>
      discountPrice != null && discountPrice! > 0 && discountPrice! < price;

  double get finalPrice => hasDiscount ? discountPrice! : price;

  bool get imageIsFullUrl =>
      image != null &&
      (image!.startsWith('http://') || image!.startsWith('https://'));

  bool get isDigital => type == 'digital';

  factory Product.fromJson(Map<String, dynamic> json) => Product(
    id: json['id'],
    name: json['name'] ?? '',
    description: json['description'],
    price: double.tryParse(json['price'].toString()) ?? 0.0,
    image: (json['featured_image'] ?? json['image'])?.toString(),
    thumbnailUrl: json['thumbnail_url']?.toString(),
    previewThumbnailUrl: json['preview_thumbnail_url']?.toString(),
    previewImageUrl: json['preview_image_url']?.toString(),
    discountPrice: (() {
      final dp = json['discounted_price'] ?? json['discount_price'];
      if (dp == null) return null;
      return double.tryParse(dp.toString());
    })(),
    type: (json['effective_type'] ?? json['type'])?.toString(),
    shopId: (json['shop'] is Map && (json['shop']['id'] != null))
        ? json['shop']['id'] as int
        : null,
    shopUserId: (json['shop'] is Map && (json['shop']['user_id'] != null))
        ? json['shop']['user_id'] as int
        : null,
    media:
        (json['media'] as List?)
            ?.map((e) {
              final m = e as Map<String, dynamic>;
              final u = (m['preview_url'] ?? m['url'] ?? m['image'])
                  ?.toString();
              return u ?? '';
            })
            .where((e) => e.isNotEmpty)
            .toList() ??
        const [],
    mediaIds:
        (json['media'] as List?)
            ?.map((e) => (e as Map<String, dynamic>)['id'] as int)
            .toList() ??
        const [],
    shippingProfiles:
        (json['shipping_profiles'] as List?)
            ?.map((e) => ShippingProfile.fromJson(e))
            .toList() ??
        const [],
    variationTypes:
        (json['variation_types'] as List?)
            ?.map((e) => VariationType.fromJson(e))
            .toList() ??
        const [],
    variants:
        (json['variants'] as List?)?.map((e) => Variant.fromJson(e)).toList() ??
        const [],
  );

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'description': description,
    'price': price,
    'image': image,
    'thumbnail_url': thumbnailUrl,
    'preview_thumbnail_url': previewThumbnailUrl,
    'preview_image_url': previewImageUrl,
    'discount_price': discountPrice,
    'type': type,
    'shop_id': shopId,
    'shop_user_id': shopUserId,
    'media': media,
    'media_ids': mediaIds,
    'shipping_profiles': shippingProfiles.map((e) => e.toJson()).toList(),
    'variation_types': variationTypes.map((e) => e.toJson()).toList(),
    'variants': variants.map((e) => e.toJson()).toList(),
  };

  static List<Product> fromJsonList(List<dynamic> list) =>
      list.map((e) => Product.fromJson(e as Map<String, dynamic>)).toList();

  Product copyWith({
    int? id,
    String? name,
    String? description,
    double? price,
    String? image,
    String? thumbnailUrl,
    String? previewThumbnailUrl,
    String? previewImageUrl,
    double? discountPrice,
    String? type,
    int? shopId,
    int? shopUserId,
    List<String>? media,
    List<int>? mediaIds,
    List<ShippingProfile>? shippingProfiles,
    List<VariationType>? variationTypes,
    List<Variant>? variants,
  }) => Product(
    id: id ?? this.id,
    name: name ?? this.name,
    description: description ?? this.description,
    price: price ?? this.price,
    image: image ?? this.image,
    thumbnailUrl: thumbnailUrl ?? this.thumbnailUrl,
    previewThumbnailUrl: previewThumbnailUrl ?? this.previewThumbnailUrl,
    previewImageUrl: previewImageUrl ?? this.previewImageUrl,
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

  @override
  String toString() =>
      'Product(id: $id, name: $name, price: $price, discount: $discountPrice, shippingProfiles: ${shippingProfiles.length}, variants: ${variants.length})';
}
