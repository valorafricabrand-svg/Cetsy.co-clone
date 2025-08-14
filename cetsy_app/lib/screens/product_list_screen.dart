// lib/screens/product_list_screen.dart
import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_staggered_grid_view/flutter_staggered_grid_view.dart';
import 'package:intl/intl.dart';
import 'package:shimmer/shimmer.dart';
import 'package:provider/provider.dart';

import '../config/constants.dart';
import '../models/product.dart';
import '../services/product_service.dart';
import '../utils/html_utils.dart';
import '../providers/cart_provider.dart';
import 'product_detail_screen.dart';

class ProductListScreen extends StatefulWidget {
  const ProductListScreen({super.key});

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  // Brand color
  static const Color cetsyGreen = Color(0xFF198754);

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

  // Unified, modern input decoration used across sheets/fields
  InputDecoration _pillInput({
    String? hint,
    String? label,
    IconData? prefixIcon,
  }) {
    return InputDecoration(
      hintText: hint,
      labelText: label,
      prefixIcon: prefixIcon != null ? Icon(prefixIcon) : null,
      filled: true,
      fillColor: Colors.grey.shade100,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(30),
        borderSide: BorderSide.none,
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(30),
        borderSide: const BorderSide(color: cetsyGreen, width: 1.5),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final bgGradient = const LinearGradient(
      colors: [Color(0xFFEFF7F3), Color(0xFFEAF5F0)],
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
    );

    return Scaffold(
      floatingActionButton: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (_showTopFab)
            FloatingActionButton.small(
              heroTag: 'toTopFab',
              backgroundColor: Colors.white,
              foregroundColor: cetsyGreen,
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
            backgroundColor: cetsyGreen,
            foregroundColor: Colors.white,
            onPressed: _openFilterSheet,
            icon: const Icon(Icons.tune),
            label: const Text('Filters'),
          ),
        ],
      ),
      body: Container(
        decoration: BoxDecoration(gradient: bgGradient),
        child: RefreshIndicator(
          color: cetsyGreen,
          onRefresh: () async => _fetch(),
          child: CustomScrollView(
            controller: _scrollCtl,
            slivers: [
              SliverAppBar(
                floating: true,
                snap: true,
                backgroundColor: Colors.white,
                foregroundColor: Colors.black87,
                elevation: 1,
                titleSpacing: 12,
                title: _buildSearchBar(),
                actions: [
                  IconButton(
                    tooltip: 'Open filters',
                    onPressed: _openFilterSheet,
                    icon: const Icon(Icons.filter_alt_outlined),
                    color: cetsyGreen,
                  ),
                  const SizedBox(width: 6),
                ],
              ),
              // Active filter chips (keyword/min/max)
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(12, 8, 12, 0),
                  child: Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _activeFilterChips(),
                  ),
                ),
              ),
              _buildProductGrid(),
              SliverToBoxAdapter(child: _buildPagination()),
              const SliverToBoxAdapter(child: SizedBox(height: 40)),
            ],
          ),
        ),
      ),
    );
  }

  // Search pill with Cetsy styling
  Widget _buildSearchBar() => TextField(
        controller: _keywordCtl,
        textInputAction: TextInputAction.search,
        onSubmitted: (_) => _applyDebounced(),
        decoration: _pillInput(
          hint: 'Search listings…',
          prefixIcon: Icons.search,
        ).copyWith(
          suffixIcon: _keywordCtl.text.isNotEmpty
              ? IconButton(
                  tooltip: 'Clear',
                  onPressed: () {
                    _keywordCtl.clear();
                    _applyDebounced();
                  },
                  icon: const Icon(Icons.close),
                )
              : null,
        ),
      );

  // Build chips for any active filters
  List<Widget> _activeFilterChips() {
    final chips = <Widget>[];
    if (_keywordCtl.text.trim().isNotEmpty) {
      chips.add(_filterChip('Keyword: ${_keywordCtl.text.trim()}', () {
        _keywordCtl.clear();
        _applyDebounced();
      }));
    }
    if (_minCtl.text.trim().isNotEmpty) {
      chips.add(_filterChip('Min: ${_minCtl.text.trim()}', () {
        _minCtl.clear();
        _applyDebounced();
      }));
    }
    if (_maxCtl.text.trim().isNotEmpty) {
      chips.add(_filterChip('Max: ${_maxCtl.text.trim()}', () {
        _maxCtl.clear();
        _applyDebounced();
      }));
    }
    if (chips.isEmpty) {
      chips.add(
        Chip(
          label: const Text('Tip: Tap Filters to refine results'),
          backgroundColor: Colors.grey.shade200,
          labelStyle: TextStyle(color: Colors.grey.shade700),
          avatar: const Icon(Icons.info_outline, size: 18),
        ),
      );
    }
    return chips;
  }

  Widget _filterChip(String text, VoidCallback onDeleted) {
    return Chip(
      label: Text(text),
      labelStyle: const TextStyle(fontWeight: FontWeight.w500),
      backgroundColor: const Color(0xFFEFF7F3),
      side: const BorderSide(color: cetsyGreen),
      deleteIcon: const Icon(Icons.close, size: 18),
      onDeleted: onDeleted,
      avatar: const Icon(Icons.tune, color: cetsyGreen, size: 18),
    );
  }

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
      borderRadius: BorderRadius.circular(14),
      child: MouseRegion(
        cursor: SystemMouseCursors.click,
        child: ClipRRect(
          borderRadius: BorderRadius.circular(14),
          child: Material(
            color: Colors.white,
            elevation: 2.5,
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

                      // Subtle gradient overlay bottom for text readability (reserved)
                      Align(
                        alignment: Alignment.bottomCenter,
                        child: Container(
                          height: 0,
                          decoration: const BoxDecoration(
                            gradient: LinearGradient(
                              begin: Alignment.bottomCenter,
                              end: Alignment.topCenter,
                              colors: [Colors.black26, Colors.transparent],
                            ),
                          ),
                        ),
                      ),

                      if (hasDiscount)
                        Positioned(
                          top: 0,
                          left: 0,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 10, vertical: 6),
                            decoration: const BoxDecoration(
                              color: Colors.redAccent,
                              borderRadius: BorderRadius.only(
                                bottomRight: Radius.circular(10),
                              ),
                            ),
                            child: Text(
                              '-${(((p.price - displayPrice) / p.price) * 100).round()}%',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ),

                      // 👉 Quick action: Add to cart
                      Positioned(
                        top: 8,
                        right: 8,
                        child: Container(
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.95),
                            shape: BoxShape.circle,
                            boxShadow: const [
                              BoxShadow(
                                blurRadius: 10,
                                color: Color(0x14000000),
                              )
                            ],
                          ),
                          child: IconButton(
                            tooltip: 'Add to cart',
                            onPressed: () {
                              context.read<CartProvider>().add(p);
                              ScaffoldMessenger.of(context).hideCurrentSnackBar();
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('Added "${p.name}" to cart'),
                                  duration: const Duration(seconds: 1),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            },
                            icon: const Icon(Icons.add_shopping_cart),
                            splashRadius: 20,
                            color: Colors.grey.shade800,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                Padding(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Title
                      Text(
                        p.name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 15.5,
                        ),
                      ),
                      const SizedBox(height: 4),

                      // Description
                      Text(
                        stripHtmlTags(p.description ?? 'No description'),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: Colors.grey.shade600,
                          fontSize: 13,
                          height: 1.35,
                        ),
                      ),
                      const SizedBox(height: 8),

                      // Price row
                      Row(
                        children: [
                          Text(
                            'KES ${_priceFmt.format(displayPrice)}',
                            style: const TextStyle(
                              fontWeight: FontWeight.w800,
                              fontSize: 16,
                              color: cetsyGreen,
                            ),
                          ),
                          if (hasDiscount) ...[
                            const SizedBox(width: 8),
                            Text(
                              'KES ${_priceFmt.format(p.price)}',
                              style: TextStyle(
                                fontSize: 13,
                                color: Colors.grey.shade600,
                                decoration: TextDecoration.lineThrough,
                              ),
                            ),
                          ],
                        ],
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
                borderRadius: BorderRadius.circular(14),
              ),
            ),
          ),
        ),
      );

  SliverToBoxAdapter _sliverMessage({
    required IconData icon,
    required String text,
  }) =>
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
                style: TextStyle(fontSize: 16, color: Colors.grey.shade600),
              ),
            ],
          ),
        ),
      );

  Widget _buildPagination() => Padding(
        padding: const EdgeInsets.symmetric(vertical: 24),
        child: Wrap(
          crossAxisAlignment: WrapCrossAlignment.center,
          spacing: 12,
          children: [
            OutlinedButton.icon(
              onPressed: _page > 1
                  ? () {
                      setState(() => _page--);
                      _fetch();
                    }
                  : null,
              icon: const Icon(Icons.chevron_left),
              label: const Text('Previous'),
              style: OutlinedButton.styleFrom(
                foregroundColor: cetsyGreen,
                side: const BorderSide(color: cetsyGreen),
              ),
            ),
            Text('Page $_page',
                style: const TextStyle(
                    fontWeight: FontWeight.w600, color: Colors.black87)),
            OutlinedButton.icon(
              onPressed: () {
                setState(() => _page++);
                _fetch();
              },
              icon: const Icon(Icons.chevron_right),
              label: const Text('Next'),
              style: OutlinedButton.styleFrom(
                foregroundColor: cetsyGreen,
                side: const BorderSide(color: cetsyGreen),
              ),
            ),
          ],
        ),
      );

  void _openFilterSheet() {
    showModalBottomSheet(
      context: context,
      showDragHandle: true,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(18)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Header row
              Row(
                children: [
                  const Icon(Icons.tune, color: cetsyGreen),
                  const SizedBox(width: 8),
                  const Expanded(
                    child: Text(
                      'Refine Results',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                    ),
                  ),
                  TextButton(
                    onPressed: () {
                      _resetFilters();
                      Navigator.pop(ctx);
                    },
                    child: const Text('Reset'),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _keywordCtl,
                decoration: _pillInput(
                  label: 'Keyword',
                  prefixIcon: Icons.search,
                ),
                onSubmitted: (_) {
                  Navigator.pop(ctx);
                  _applyDebounced();
                },
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _minCtl,
                      keyboardType: TextInputType.number,
                      decoration: _pillInput(label: 'Min Price').copyWith(
                        prefixIcon: const Icon(Icons.price_change_outlined),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextField(
                      controller: _maxCtl,
                      keyboardType: TextInputType.number,
                      decoration: _pillInput(label: 'Max Price').copyWith(
                        prefixIcon: const Icon(Icons.price_check_outlined),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      icon: const Icon(Icons.check_circle_outline),
                      onPressed: () {
                        Navigator.pop(ctx);
                        _applyDebounced();
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: cetsyGreen,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                      label: const Text(
                        'Apply Filters',
                        style: TextStyle(fontWeight: FontWeight.w700),
                      ),
                    ),
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
