class Product {
  final int id;
  final String name;
  final String? description;
  final double price;
  final String? image;

  Product({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.image,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'] ?? '',
      description: json['description'],
      price: double.tryParse(json['price'].toString()) ?? 0.0,
      image: json['image'],
    );
  }
}
