// lib/screens/product_list_screen.dart
import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_staggered_grid_view/flutter_staggered_grid_view.dart';
import 'package:intl/intl.dart';
import 'package:shimmer/shimmer.dart';

import '../config/constants.dart';
import '../models/product.dart';
import '../services/product_service.dart';
import '../utils/html_utils.dart';
import 'product_detail_screen.dart';

class ProductListScreen extends StatefulWidget {
  const ProductListScreen({super.key});

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  late Future<List<Product>> _products;
  final _keywordCtl = TextEditingController();
  final _minCtl = TextEditingController();
  final _maxCtl = TextEditingController();
  final _scrollCtl = ScrollController();

  int _page = 1;
  bool _showTopFab = false;
  Timer? _debounce;
  final _priceFmt = NumberFormat.decimalPattern();

  @override
  void initState() {
    super.initState();
    _fetch();
    _scrollCtl.addListener(_onScroll);
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _scrollCtl
      ..removeListener(_onScroll)
      ..dispose();
    _keywordCtl.dispose();
    _minCtl.dispose();
    _maxCtl.dispose();
    super.dispose();
  }

  void _onScroll() {
    final show = _scrollCtl.offset > 400;
    if (show != _showTopFab) setState(() => _showTopFab = show);
  }

  void _fetch() {
    setState(() {
      _products = ProductService.fetchProducts(
        page: _page,
        keyword: _keywordCtl.text.trim(),
        minPrice: double.tryParse(_minCtl.text),
        maxPrice: double.tryParse(_maxCtl.text),
      );
    });
  }

  void _applyDebounced() {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 250), () {
      FocusScope.of(context).unfocus();
      _page = 1;
      _fetch();
    });
  }

  void _resetFilters() {
    _keywordCtl.clear();
    _minCtl.clear();
    _maxCtl.clear();
    _page = 1;
    _fetch();
  }

  String? _imageUrlFrom(String? file) {
    if (file == null || file.trim().isEmpty) return null;
    if (file.startsWith('http')) return Uri.encodeFull(file);

    // Remove trailing /api if present
    var root = Constants.baseUrl.replaceFirst(RegExp(r'/api/?$'), '');
    if (root.endsWith('/')) root = root.substring(0, root.length - 1);

    return Uri.encodeFull('$root/storage/products/$file');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      floatingActionButton: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (_showTopFab)
            FloatingActionButton.small(
              heroTag: 'toTopFab',
              onPressed: () => _scrollCtl.animateTo(
                0,
                duration: const Duration(milliseconds: 400),
                curve: Curves.easeOut,
              ),
              child: const Icon(Icons.arrow_upward),
            ),
          const SizedBox(height: 10),
          FloatingActionButton.extended(
            heroTag: 'filterFab',
            onPressed: _openFilterSheet,
            icon: const Icon(Icons.tune),
            label: const Text('Filters'),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => _fetch(),
        child: CustomScrollView(
          controller: _scrollCtl,
          slivers: [
            SliverAppBar(
              floating: true,
              snap: true,
              title: _buildSearchBar(),
              backgroundColor: Theme.of(context).scaffoldBackgroundColor,
              foregroundColor: Colors.black87,
              elevation: 2,
            ),
            _buildProductGrid(),
            SliverToBoxAdapter(child: _buildPagination()),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchBar() => TextField(
        controller: _keywordCtl,
        textInputAction: TextInputAction.search,
        onSubmitted: (_) => _applyDebounced(),
        decoration: InputDecoration(
          hintText: 'Search listings…',
          filled: true,
          fillColor: Colors.grey.shade200,
          prefixIcon: const Icon(Icons.search),
          contentPadding: EdgeInsets.zero,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(30),
            borderSide: BorderSide.none,
          ),
        ),
      );

  Widget _buildProductGrid() => FutureBuilder<List<Product>>(
        future: _products,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return _shimmerGrid();
          }
          if (snap.hasError) {
            return _sliverMessage(
              icon: Icons.error_outline,
              text: 'Something went wrong.\n${snap.error}',
            );
          }
          final products = snap.data;
          if (products == null || products.isEmpty) {
            return _sliverMessage(
              icon: Icons.inventory_2_outlined,
              text: 'No products found.',
            );
          }
          return SliverPadding(
            padding: const EdgeInsets.all(12),
            sliver: SliverMasonryGrid.count(
              crossAxisCount: _columnCount(context),
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childCount: products.length,
              itemBuilder: (_, i) => _buildCard(products[i]),
            ),
          );
        },
      );

  Widget _buildCard(Product p) {
    final url = _imageUrlFrom(p.image);
    final hasDiscount = p.discountPrice != null && p.discountPrice! < p.price;
    final displayPrice = hasDiscount ? p.discountPrice! : p.price;

    return InkWell(
      onTap: () => Navigator.pushNamed(
        context,
        ProductDetailScreen.route,
        arguments: p,
      ),
      borderRadius: BorderRadius.circular(12),
      child: MouseRegion(
        cursor: SystemMouseCursors.click,
        child: ClipRRect(
          borderRadius: BorderRadius.circular(12),
          child: Material(
            elevation: 3,
            shadowColor: Colors.black12,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                AspectRatio(
                  aspectRatio: 4 / 3,
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      if (url != null)
                        Hero(
                          tag: 'product-${p.id}',
                          child: Image.network(
                            url,
                            fit: BoxFit.cover,
                            frameBuilder: (ctx, child, frame, _) =>
                                AnimatedOpacity(
                              opacity: frame == null ? 0 : 1,
                              duration: const Duration(milliseconds: 300),
                              child: child,
                            ),
                            errorBuilder: (_, __, ___) => Image.asset(
                              'assets/images/placeholder.png',
                              fit: BoxFit.cover,
                            ),
                          ),
                        )
                      else
                        Image.asset(
                          'assets/images/placeholder.png',
                          fit: BoxFit.cover,
                        ),
                      if (hasDiscount)
                        Positioned(
                          top: 0,
                          left: 0,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 8, vertical: 4),
                            decoration: const BoxDecoration(
                              color: Colors.redAccent,
                              borderRadius: BorderRadius.only(
                                  bottomRight: Radius.circular(8)),
                            ),
                            child: Text(
                              '-${(((p.price - displayPrice) / p.price) * 100).round()}%',
                              style: const TextStyle(
                                  color: Colors.white, fontSize: 11),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
                Padding(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        p.name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                            fontWeight: FontWeight.w600, fontSize: 15),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        stripHtmlTags(p.description ?? 'No description'),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                            color: Colors.grey.shade600, fontSize: 13),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'KES ${_priceFmt.format(displayPrice)}',
                        style: const TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  SliverPadding _shimmerGrid() => SliverPadding(
        padding: const EdgeInsets.all(12),
        sliver: SliverMasonryGrid.count(
          crossAxisCount: _columnCount(context),
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childCount: 8,
          itemBuilder: (_, __) => Shimmer.fromColors(
            baseColor: Colors.grey.shade300,
            highlightColor: Colors.grey.shade100,
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        ),
      );

  SliverToBoxAdapter _sliverMessage(
          {required IconData icon, required String text}) =>
      SliverToBoxAdapter(
        child: Padding(
          padding: const EdgeInsets.only(top: 100),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 48, color: Colors.grey.shade400),
              const SizedBox(height: 12),
              Text(
                text,
                textAlign: TextAlign.center,
                style:
                    TextStyle(fontSize: 16, color: Colors.grey.shade600),
              ),
            ],
          ),
        ),
      );

  Widget _buildPagination() => Padding(
        padding: const EdgeInsets.symmetric(vertical: 24),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            OutlinedButton(
              onPressed: _page > 1
                  ? () {
                      setState(() => _page--);
                      _fetch();
                    }
                  : null,
              child: const Text('Previous'),
            ),
            const SizedBox(width: 12),
            Text('Page $_page'),
            const SizedBox(width: 12),
            OutlinedButton(
              onPressed: () {
                setState(() => _page++);
                _fetch();
              },
              child: const Text('Next'),
            ),
          ],
        ),
      );

  void _openFilterSheet() {
    showModalBottomSheet(
      context: context,
      showDragHandle: true,
      isScrollControlled: true,
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: _keywordCtl,
                decoration: const InputDecoration(
                  labelText: 'Keyword',
                  prefixIcon: Icon(Icons.search),
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _minCtl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Min Price'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextField(
                      controller: _maxCtl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Max Price'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(ctx);
                        _applyDebounced();
                      },
                      child: const Text('Apply Filters'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  TextButton(
                    onPressed: () {
                      _resetFilters();
                      Navigator.pop(ctx);
                    },
                    child: const Text('Reset'),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  int _columnCount(BuildContext context) {
    final w = MediaQuery.of(context).size.width;
    if (w >= 1100) return 4;
    if (w >= 700) return 3;
    return 2;
  }
}
