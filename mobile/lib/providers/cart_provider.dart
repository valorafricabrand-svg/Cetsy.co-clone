// lib/providers/cart_provider.dart
import 'dart:collection';

import 'package:flutter/foundation.dart';

import '../models/product.dart';
import '../models/shipping_profile.dart';
import '../models/variation.dart';

class CartItem {
  final Product product;
  final int? variantId; // null when no variant selected
  final String? variationLabel; // e.g., "Red / M"
  int qty;
  ShippingProfile? shippingProfile;
  CartItem({
    required this.product,
    this.variantId,
    this.variationLabel,
    this.qty = 1,
    this.shippingProfile,
  });

  double get unitPrice {
    if (variantId != null) {
      final v = product.variants.firstWhere(
        (e) => e.id == variantId,
        orElse: () => const Variant(id: -1, stock: 0),
      );
      if (v.price != null && v.price! > 0) return v.price!;
    }
    return product.discountPrice ?? product.price;
  }

  double get lineTotal => unitPrice * qty;

  double get shippingCost => (shippingProfile?.baseRate ?? 0) * qty;
}

class CartProvider extends ChangeNotifier {
  /// key = composite "productId:variantId" (variantId may be 0)
  final Map<String, CartItem> _items = {};

  UnmodifiableMapView<String, CartItem> get items => UnmodifiableMapView(_items);

  int get itemCount => _items.values.fold(0, (sum, i) => sum + i.qty);

  double get total => _items.values.fold(0.0, (sum, i) => sum + i.lineTotal);

  double get shippingTotal =>
      _items.values.fold(0.0, (sum, i) => sum + i.shippingCost);

  double get grandTotal => total + shippingTotal;

  void add(Product p, {int qty = 1, int? variantId, String? variationLabel, ShippingProfile? profile}) {
    final key = '${p.id}:${variantId ?? 0}';
    if (_items.containsKey(key)) {
      _items[key]!.qty += qty;
    } else {
      _items[key] = CartItem(
        product: p,
        variantId: variantId,
        variationLabel: variationLabel,
        qty: qty,
        shippingProfile: profile ?? (p.shippingProfiles.isNotEmpty ? p.shippingProfiles.first : null),
      );
    }
    notifyListeners();
  }

  void removeByKey(String key) {
    if (_items.containsKey(key)) {
      _items.remove(key);
      notifyListeners();
    }
  }

  void setQtyByKey(String key, int qty) {
    if (_items.containsKey(key)) {
      if (qty <= 0) {
        _items.remove(key);
      } else {
        _items[key]!.qty = qty;
      }
      notifyListeners();
    }
  }

  void setShippingProfileByKey(String key, ShippingProfile profile) {
    if (_items.containsKey(key)) {
      _items[key]!.shippingProfile = profile;
      notifyListeners();
    }
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }
}
