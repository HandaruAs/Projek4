class PriceModel {
  final DateTime date;
  final double price;
  final String commodityId;
  final String commodityName;

  PriceModel({
    required this.date,
    required this.price,
    required this.commodityId,
    required this.commodityName,
  });

  factory PriceModel.fromJson(Map<String, dynamic> json) {
    return PriceModel(
      date: DateTime.parse(json['date'] ?? DateTime.now().toIso8601String()),
      price: (json['price'] ?? 0).toDouble(),
      commodityId: json['commodity_id']?.toString() ?? '',
      commodityName: json['commodity_name'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'date': date.toIso8601String(),
      'price': price,
      'commodity_id': commodityId,
      'commodity_name': commodityName,
    };
  }
}