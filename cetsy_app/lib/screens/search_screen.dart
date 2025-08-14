// lib/screens/search_screen.dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../config/constants.dart';
import '../models/product.dart';
import '../services/product_service.dart';
import 'product_detail_screen.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  static const Color cetsyGreen = Color(0xFF198754);

  final _queryCtl = TextEditingController();
  Future<List<Product>>? _results;
  final _fmt = NumberFormat.decimalPattern();

  @override
  void dispose() {
    _queryCtl.dispose();
    super.dispose();
  }

  void _search() {
    final q = _queryCtl.text.trim();
    if (q.isEmpty) return;
    setState(() {
      _results = ProductService.fetchProducts(keyword: q);
    });
  }

  String? _imageUrlFrom(String? file) {
    if (file == null || file.trim().isEmpty) return null;
    if (file.startsWith('http')) return Uri.encodeFull(file);
    var root = Constants.baseUrl.replaceFirst(RegExp(r'/api/?$'), '');
    if (root.endsWith('/')) root = root.substring(0, root.length - 1);
    return Uri.encodeFull('$root/storage/products/$file');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: TextField(
          controller: _queryCtl,
          decoration: const InputDecoration(
            hintText: 'Search products…',
            border: InputBorder.none,
          ),
          textInputAction: TextInputAction.search,
          onSubmitted: (_) => _search(),
        ),
        actions: [
          IconButton(
            onPressed: _search,
            icon: const Icon(Icons.search),
          ),
        ],
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFEFF7F3), Color(0xFFEAF5F0)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: _results == null
            ? const Center(child: Text('Search for products'))
            : FutureBuilder<List<Product>>(
                future: _results,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  if (snap.hasError) {
                    return Center(child: Text('Error: ${snap.error}'));
                  }
                  final items = snap.data;
                  if (items == null || items.isEmpty) {
                    return const Center(child: Text('No products found'));
                  }
                  return ListView.separated(
                    padding: const EdgeInsets.all(8),
                    itemCount: items.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 6),
                    itemBuilder: (_, i) {
                      final p = items[i];
                      final url = _imageUrlFrom(p.image);
                      final price = p.discountPrice ?? p.price;
                      return ListTile(
                        leading: url != null
                            ? Image.network(url, width: 56, height: 56, fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => const Icon(Icons.image_not_supported))
                            : const Icon(Icons.image_not_supported),
                        title: Text(p.name),
                        subtitle: Text('KES ${_fmt.format(price)}'),
                        onTap: () => Navigator.pushNamed(
                          context,
                          ProductDetailScreen.route,
                          arguments: p,
                        ),
                      );
                    },
                  );
                },
              ),
      ),
    );
  }
}
