import 'package:provider/provider.dart';
import '../utils/money_utils.dart';
// import '../providers/currency_provider.dart';
// lib/screens/product_detail_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:photo_view/photo_view.dart';
// import 'package:intl/intl.dart';

import '../config/constants.dart';
import '../models/product.dart';
import '../models/variation.dart';
import '../providers/cart_provider.dart';
import '../providers/auth_provider.dart';
import 'manage_listing_screen.dart';
import '../services/product_service.dart';
import 'checkout_screen.dart';

class ProductDetailScreen extends StatefulWidget {
  static const route = '/product';
  const ProductDetailScreen({super.key});

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  final Map<int, int> _selectedOptionByType = {}; // typeId -> optionId
  int _qty = 1;
  Product? _data;
  bool _loading = false;

  String? _imageUrl(String? file) {
    if (file == null || file.trim().isEmpty) return null;
    if (file.startsWith('http')) return Uri.encodeFull(file);
    var root = Constants.baseUrl.replaceFirst(RegExp(r'/api/?$'), '');
    if (root.endsWith('/')) root = root.substring(0, root.length - 1);
    return Uri.encodeFull('$root/storage/products/$file');
  }

  Variant? _matchVariant(Product p) {
    if (p.variants.isEmpty || p.variationTypes.isEmpty) return null;
    final selected = _selectedOptionByType.values.toList()..sort();
    if (selected.length != p.variationTypes.length) return null;
    for (final v in p.variants) {
      final ids = [...v.optionIds]..sort();
      if (ids.length == selected.length && ids.every((id) => selected.contains(id))) {
        return v;
      }
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final routeProduct = ModalRoute.of(context)!.settings.arguments as Product;
    final product = _data ?? routeProduct;
    final imgUrl = _imageUrl(product.image);
    // final priceFmt = NumberFormat.decimalPattern();

    final variant = _matchVariant(product);
    final variantPrice = variant?.price;
    final displayPrice = (variantPrice != null && variantPrice > 0)
        ? variantPrice
        : (product.discountPrice ?? product.price);

    final auth = context.watch<AuthProvider>();
    final canManage = auth.user != null && auth.user!.userType == 'seller' && product.shopUserId != null && auth.user!.id == product.shopUserId;

    return Scaffold(
      appBar: AppBar(
        title: Text(product.name, overflow: TextOverflow.ellipsis),
        actions: [
          if (canManage)
            IconButton(
              tooltip: 'Manage Listing',
              icon: const Icon(Icons.settings),
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => ManageListingScreen(productId: product.id)),
              ),
            ),
        ],
      ),
      body: ListView(
        padding: EdgeInsets.zero,
        children: [
          // image
          imgUrl != null
              ? GestureDetector(
                  onTap: () => _openPhoto(context, imgUrl, product.id),
                  child: Hero(
                    tag: 'product-${product.id}',
                    child: AspectRatio(
                      aspectRatio: 4 / 3,
                      child: FadeInImage.assetNetwork(
                        placeholder: 'assets/images/placeholder.png',
                        image: imgUrl,
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                )
              : Image.asset('assets/images/placeholder.png', height: 220, fit: BoxFit.cover),

          // info card
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (_loading) const LinearProgressIndicator(),
                Text(product.name, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w600)),
                const SizedBox(height: 8),

                Row(
                  children: [
                    Text(
                      context.money(displayPrice),
                      style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                    if (variant == null && (product.discountPrice != null && product.discountPrice! < product.price)) ...[
                      const SizedBox(width: 8),
                      Text(
                        context.money(product.price),
                        style: const TextStyle(
                          fontSize: 16,
                          color: Colors.grey,
                          decoration: TextDecoration.lineThrough,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(color: Colors.redAccent, borderRadius: BorderRadius.circular(4)),
                        child: Text(
                          '-${(((product.price - displayPrice) / product.price) * 100).round()}%',
                          style: const TextStyle(color: Colors.white, fontSize: 12),
                        ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 16),

                // Variations (if any)
                if (product.variationTypes.isNotEmpty) ...[
                  const Text('Options', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 8),
                  ...product.variationTypes.map((t) {
                    final value = _selectedOptionByType[t.id];
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: DropdownButtonFormField<int>(
                        initialValue: value,
                        decoration: InputDecoration(labelText: t.name),
                        items: t.options.map((o) => DropdownMenuItem(value: o.id, child: Text(o.value))).toList(),
                        onChanged: (id) {
                          if (id == null) return;
                          setState(() => _selectedOptionByType[t.id] = id);
                        },
                      ),
                    );
                  }),
                  if (variant != null && variant.label.isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: Text('Selected: ${variant.label}', style: const TextStyle(color: Colors.black54)),
                    ),
                ],

                // Quantity
                Row(
                  children: [
                    const Text('Qty:'),
                    const SizedBox(width: 8),
                    IconButton(
                      onPressed: () => setState(() => _qty = (_qty > 1) ? _qty - 1 : 1),
                      icon: const Icon(Icons.remove_circle_outline),
                    ),
                    Text('$_qty', style: const TextStyle(fontWeight: FontWeight.w600)),
                    IconButton(
                      onPressed: () => setState(() => _qty += 1),
                      icon: const Icon(Icons.add_circle_outline),
                    ),
                  ],
                ),
                const SizedBox(height: 20),

                // description
                _descriptionSection(context, product),
              ],
            ),
          ),
        ],
      ),

      // bottom actions
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          child: Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () {
                    if (product.variationTypes.isNotEmpty && _matchVariant(product) == null) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Please select all options')),
                      );
                      return;
                    }
                    final v = _matchVariant(product);
                    context.read<CartProvider>().add(
                          product,
                          qty: _qty,
                          variantId: v?.id,
                          variationLabel: v?.label,
                        );
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Added to cart')),
                    );
                  },
                  child: const Text('Add to Cart'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: FilledButton(
                  onPressed: () {
                    if (product.variationTypes.isNotEmpty && _matchVariant(product) == null) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Please select all options')),
                      );
                      return;
                    }
                    final v = _matchVariant(product);
                    context.read<CartProvider>().add(
                          product,
                          qty: _qty,
                          variantId: v?.id,
                          variationLabel: v?.label,
                        );
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                    );
                  },
                  child: const Text('Buy Now'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  void initState() {
    super.initState();
    // Fetch enriched product details after first frame
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final base = ModalRoute.of(context)!.settings.arguments as Product;
      setState(() => _loading = true);
      try {
        final full = await ProductService.getProduct(base.id);
        if (mounted) setState(() => _data = full);
      } catch (_) {
        // keep base data
      } finally {
        if (mounted) setState(() => _loading = false);
      }
    });
  }

  Widget _descriptionSection(BuildContext ctx, Product p) {
    final hasDesc = p.description != null && p.description!.trim().isNotEmpty;
    if (!hasDesc) {
      return const Text('No description provided.', style: TextStyle(color: Colors.grey));
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Description', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        Html(
          data: p.description!,
          onLinkTap: (url, _, __) {
            if (url != null) _openLink(ctx, url);
          },
        ),
      ],
    );
  }

  void _openPhoto(BuildContext ctx, String url, int id) {
    Navigator.of(ctx).push(
      PageRouteBuilder(
        opaque: false,
        barrierDismissible: true,
        pageBuilder: (_, __, ___) => Scaffold(
          backgroundColor: Colors.black87,
          body: GestureDetector(
            onTap: () => Navigator.pop(ctx),
            child: Center(
              child: Hero(
                tag: 'product-$id',
                child: PhotoView(
                  imageProvider: NetworkImage(url),
                  loadingBuilder: (_, __) => const CircularProgressIndicator(color: Colors.white),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _openLink(BuildContext ctx, String url) {
    ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text('Open link: $url')));
  }
}







