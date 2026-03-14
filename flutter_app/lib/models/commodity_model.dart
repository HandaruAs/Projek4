class CommodityModel {
  final String id;
  final String name;
  final String category;
  final String unit;
  final double currentPrice;
  final double previousPrice;
  final double priceChange;
  final String changePercentage;
  final bool isIncreasing;
  final String? imageUrl;

  CommodityModel({
    required this.id,
    required this.name,
    required this.category,
    required this.unit,
    required this.currentPrice,
    required this.previousPrice,
    required this.priceChange,
    required this.changePercentage,
    required this.isIncreasing,
    this.imageUrl,
  });

  factory CommodityModel.fromJson(Map<String, dynamic> json) {
    double current = (json['current_price'] ?? 0).toDouble();
    double previous = (json['previous_price'] ?? 0).toDouble();
    double change = current - previous;
    bool isIncreasing = change > 0;
    
    return CommodityModel(
      id: json['id']?.toString() ?? '',
      name: json['name'] ?? '',
      category: json['category'] ?? '',
      unit: json['unit'] ?? 'kg',
      currentPrice: current,
      previousPrice: previous,
      priceChange: change.abs(),
      changePercentage: previous != 0 ? ((change / previous) * 100).toStringAsFixed(1) + '%' : '0%',
      isIncreasing: isIncreasing,
      imageUrl: json['image_url'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'category': category,
      'unit': unit,
      'current_price': currentPrice,
      'previous_price': previousPrice,
    };
  }
}