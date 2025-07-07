import 'package:flutter/material.dart';
import '../models/product.dart';
import '../services/product_service.dart';
import '../config/constants.dart';
import '../utils/html_utils.dart';

class ProductListScreen extends StatefulWidget {
  const ProductListScreen({super.key});

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  late Future<List<Product>> _products;
  final _keywordController = TextEditingController();
  final _minPriceController = TextEditingController();
  final _maxPriceController = TextEditingController();
  int _currentPage = 1;

  @override
  void initState() {
    super.initState();
    _fetchFilteredProducts();
  }

  void _fetchFilteredProducts() {
    setState(() {
      _products = ProductService.fetchProducts(
        page: _currentPage,
        keyword: _keywordController.text,
        minPrice: double.tryParse(_minPriceController.text),
        maxPrice: double.tryParse(_maxPriceController.text),
      );
    });
  }

  void _nextPage() {
    setState(() => _currentPage++);
    _fetchFilteredProducts();
  }

  void _prevPage() {
    if (_currentPage > 1) {
      setState(() => _currentPage--);
      _fetchFilteredProducts();
    }
  }

  void _resetFilters() {
    _keywordController.clear();
    _minPriceController.clear();
    _maxPriceController.clear();
    _currentPage = 1;
    _fetchFilteredProducts();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('All Products')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Filter Row
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _keywordController,
                    decoration: const InputDecoration(labelText: 'Search'),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextField(
                    controller: _minPriceController,
                    decoration: const InputDecoration(labelText: 'Min Price'),
                    keyboardType: TextInputType.number,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextField(
                    controller: _maxPriceController,
                    decoration: const InputDecoration(labelText: 'Max Price'),
                    keyboardType: TextInputType.number,
                  ),
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: _fetchFilteredProducts,
                  child: const Text('Apply'),
                ),
                const SizedBox(width: 4),
                TextButton(
                  onPressed: _resetFilters,
                  child: const Text('Reset'),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Product List
            Expanded(
              child: FutureBuilder<List<Product>>(
                future: _products,
                builder: (context, snapshot) {
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  } else if (snapshot.hasError) {
                    return Center(child: Text("Error: ${snapshot.error}"));
                  } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
                    return const Center(child: Text("No products found."));
                  }

                  final products = snapshot.data!;
                  return ListView.builder(
                    itemCount: products.length,
                    itemBuilder: (context, index) {
                      final product = products[index];
                      final imageUrl = product.image != null
                          ? "${Constants.baseUrl.replaceAll('/api', '')}/storage/${product.image}"
                          : null;

                      return Card(
                        elevation: 3,
                        margin: const EdgeInsets.only(bottom: 12),
                        child: ListTile(
                          leading: imageUrl != null
                              ? Image.network(
                                  imageUrl,
                                  width: 60,
                                  height: 60,
                                  fit: BoxFit.cover,
                                  errorBuilder: (ctx, _, __) =>
                                      const Icon(Icons.broken_image),
                                )
                              : const Icon(Icons.image_not_supported),
                          title: Text(product.name),
                          subtitle: Text(
                            stripHtmlTags(product.description),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          trailing: Text("KES ${product.price.toStringAsFixed(0)}"),
                        ),
                      );
                    },
                  );
                },
              ),
            ),

            // Pagination
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                TextButton(onPressed: _prevPage, child: const Text('Previous')),
                Text('Page $_currentPage'),
                TextButton(onPressed: _nextPage, child: const Text('Next')),
              ],
            )
          ],
        ),
      ),
    );
  }
}
