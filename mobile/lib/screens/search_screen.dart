// import 'package:provider/provider.dart';
// import '../providers/currency_provider.dart';
// lib/screens/search_screen.dart
import 'package:flutter/material.dart';
// import 'package:intl/intl.dart';

import '../config/constants.dart';
import '../utils/money_utils.dart';
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
  // final _fmt = NumberFormat.decimalPattern();
  String? _selectedType; // null | 'physical' | 'service' | 'digital'

  @override
  void dispose() {
    _queryCtl.dispose();
    super.dispose();
  }

  void _search() {
    final q = _queryCtl.text.trim();
    if (q.isEmpty) return;
    setState(() {
      _results = ProductService.fetchProducts(keyword: q, type: _selectedType);
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
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(46),
          child: Container(
            alignment: Alignment.centerLeft,
            padding: const EdgeInsets.fromLTRB(12, 6, 12, 10),
            decoration: const BoxDecoration(color: Colors.white),
            child: _buildTypeChips(),
          ),
        ),
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
                      // final isSpecial = p.type != null && p.type != 'physical';
                      final typeLabel = p.type == 'service'
                          ? 'Service'
                          : (p.type == 'digital' ? 'Digital' : null);
                      return ListTile(
                        leading: url != null
                            ? Image.network(url, width: 56, height: 56, fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => const Icon(Icons.image_not_supported))
                            : const Icon(Icons.image_not_supported),
                        title: Row(
                          children: [
                            Expanded(child: Text(p.name, overflow: TextOverflow.ellipsis)),
                            if (typeLabel != null)
                              Container(
                                margin: const EdgeInsets.only(left: 8),
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: Colors.black87.withValues(alpha: .08),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  typeLabel,
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
                                ),
                              ),
                          ],
                        ),
                        subtitle: Text(context.money(price)),
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

  Widget _buildTypeChips() {
    Widget chip({required String label, String? value}) {
      final selected = _selectedType == value;
      return Padding(
        padding: const EdgeInsets.only(right: 8),
        child: ChoiceChip(
          selected: selected,
          label: Text(label),
          onSelected: (_) {
            setState(() => _selectedType = value);
            // If we already have results, re-run search with updated type
            if (_results != null) _search();
          },
          selectedColor: cetsyGreen.withValues(alpha: .15),
          labelStyle: TextStyle(
            color: selected ? cetsyGreen : Colors.black87,
            fontWeight: FontWeight.w600,
          ),
        ),
      );
    }

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          chip(label: 'All', value: null),
          chip(label: 'Products', value: 'physical'),
          chip(label: 'Services', value: 'service'),
          chip(label: 'Digital', value: 'digital'),
        ],
      ),
    );
  }
}








