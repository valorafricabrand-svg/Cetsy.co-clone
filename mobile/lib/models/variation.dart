class VariationOption {
  final int id;
  final String value;
  const VariationOption({required this.id, required this.value});

  factory VariationOption.fromJson(Map<String, dynamic> json) =>
      VariationOption(id: json['id'], value: json['value'] ?? '');

  Map<String, dynamic> toJson() => {'id': id, 'value': value};
}

class VariationType {
  final int id;
  final String name;
  final List<VariationOption> options;
  const VariationType({required this.id, required this.name, this.options = const []});

  factory VariationType.fromJson(Map<String, dynamic> json) => VariationType(
        id: json['id'],
        name: json['name'] ?? '',
        options: (json['options'] as List?)
                ?.map((e) => VariationOption.fromJson(e))
                .toList() ??
            const [],
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'options': options.map((e) => e.toJson()).toList(),
      };
}

class Variant {
  final int id;
  final double? price;
  final int stock;
  final List<int> optionIds; // matches VariationOption IDs
  final String label; // "Red / M"

  const Variant({
    required this.id,
    this.price,
    required this.stock,
    this.optionIds = const [],
    this.label = '',
  });

  factory Variant.fromJson(Map<String, dynamic> json) => Variant(
        id: json['id'],
        price: json['price'] == null ? null : double.tryParse(json['price'].toString()),
        stock: json['stock'] is int ? json['stock'] : int.tryParse('${json['stock']}') ?? 0,
        optionIds: (json['option_ids'] as List?)?.map((e) => e as int).toList() ?? const [],
        label: json['label'] ?? '',
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'price': price,
        'stock': stock,
        'option_ids': optionIds,
        'label': label,
      };
}

