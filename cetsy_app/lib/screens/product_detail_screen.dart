// lib/screens/product_detail_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';          // html → widgets
import 'package:photo_view/photo_view.dart';              // pinch‑zoom image
import 'package:intl/intl.dart';

import '../config/constants.dart';
import '../models/product.dart';

class ProductDetailScreen extends StatelessWidget {
  /// Named route used by the list screen.
  static const route = '/product';

  const ProductDetailScreen({super.key});

  // ──────────────────── Image helper ────────────────────
  String? _imageUrl(String? file) {
    if (file == null || file.trim().isEmpty) return null;
    if (file.startsWith('http')) return Uri.encodeFull(file);

    var root = Constants.baseUrl.replaceFirst(RegExp(r'/api/?$'), '');
    if (root.endsWith('/')) root = root.substring(0, root.length - 1);
    return Uri.encodeFull('$root/storage/products/$file');
  }

  // ──────────────────── Build ────────────────────
  @override
  Widget build(BuildContext context) {
    final product = ModalRoute.of(context)!.settings.arguments as Product;
    final imgUrl = _imageUrl(product.image);
    final priceFmt = NumberFormat.decimalPattern();

    final hasDiscount =
        product.discountPrice != null && product.discountPrice! < product.price;
    final displayPrice = hasDiscount ? product.discountPrice! : product.price;

    return Scaffold(
      appBar: AppBar(
        title: Text(product.name, overflow: TextOverflow.ellipsis),
      ),
      body: ListView(
        padding: EdgeInsets.zero,
        children: [
          // ─── Hero banner ───
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
              : Image.asset('assets/images/placeholder.png',
                  height: 220, fit: BoxFit.cover),

          // ─── Info card ───
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // name
                Text(product.name,
                    style: const TextStyle(
                        fontSize: 22, fontWeight: FontWeight.w600)),
                const SizedBox(height: 8),

                // price row
                Row(
                  children: [
                    Text(
                      'KES ${priceFmt.format(displayPrice)}',
                      style: const TextStyle(
                          fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                    if (hasDiscount) ...[
                      const SizedBox(width: 8),
                      Text(
                        'KES ${priceFmt.format(product.price)}',
                        style: const TextStyle(
                          fontSize: 16,
                          color: Colors.grey,
                          decoration: TextDecoration.lineThrough,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.redAccent,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          '-${(((product.price - displayPrice) / product.price) * 100).round()}%',
                          style: const TextStyle(
                              color: Colors.white, fontSize: 12),
                        ),
                      ),
                    ],
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

      // ─── Sticky bottom bar ───
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          child: SizedBox(
            height: 48,
            child: FilledButton(
              onPressed: () {
                // TODO ‑ integrate with your cart / checkout flow
                ScaffoldMessenger.of(context)
                    .showSnackBar(const SnackBar(content: Text('Added to cart')));
              },
              child: const Text('Add to Cart'),
            ),
          ),
        ),
      ),
    );
  }

  // ──────────────────── Description helper ────────────────────
  Widget _descriptionSection(BuildContext ctx, Product p) {
    final hasDesc =
        p.description != null && p.description!.trim().isNotEmpty;
    if (!hasDesc) {
      return const Text('No description provided.',
          style: TextStyle(color: Colors.grey));
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Description',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
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

  // ──────────────────── Photo viewer ────────────────────
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
                  loadingBuilder: (_, __) =>
                      const CircularProgressIndicator(color: Colors.white),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ──────────────────── Link handler ────────────────────
  void _openLink(BuildContext ctx, String url) {
    ScaffoldMessenger.of(ctx)
        .showSnackBar(SnackBar(content: Text('Open link: $url')));
    // For production use url_launcher
  }
}
