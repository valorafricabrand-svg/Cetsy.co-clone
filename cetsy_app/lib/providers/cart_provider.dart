// lib/providers/cart_provider.dart
import 'package:flutter/foundation.dart';
import '../models/product.dart';
import '../models/shipping_profile.dart';

class CartItem {
  final Product product;
  int qty;
  ShippingProfile? shippingProfile;
  CartItem({required this.product, this.qty = 1, this.shippingProfile});

  double get shippingCost =>
      (shippingProfile?.baseRate ?? 0) * qty;
}

class CartProvider extends ChangeNotifier {
  /// key = product.id
  final Map<int, CartItem> _items = {};

  Map<int, CartItem> get items => _items;

  int get itemCount => _items.values.fold(0, (sum, i) => sum + i.qty);

  double get total =>
      _items.values.fold(0.0,
          (sum, i) => sum + (i.product.discountPrice ?? i.product.price) * i.qty);

  double get shippingTotal =>
      _items.values.fold(0.0, (sum, i) => sum + i.shippingCost);

  double get grandTotal => total + shippingTotal;

  void add(Product p, {int qty = 1, ShippingProfile? profile}) {
    if (_items.containsKey(p.id)) {
      _items[p.id]!.qty += qty;
    } else {
      _items[p.id] = CartItem(
        product: p,
        qty: qty,
        shippingProfile: profile ??
            (p.shippingProfiles.isNotEmpty ? p.shippingProfiles.first : null),
      );
    }
    notifyListeners();
  }

  void remove(int productId) {
    if (_items.containsKey(productId)) {
      _items.remove(productId);
      notifyListeners();
    }
  }

  void setQty(int productId, int qty) {
    if (_items.containsKey(productId)) {
      if (qty <= 0) {
        _items.remove(productId);
      } else {
        _items[productId]!.qty = qty;
      }
      notifyListeners();
    }
  }

  void setShippingProfile(int productId, ShippingProfile profile) {
    if (_items.containsKey(productId)) {
      _items[productId]!.shippingProfile = profile;
      notifyListeners();
    }
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }
}
