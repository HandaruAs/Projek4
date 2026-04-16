class MarketModel {
  final String id;
  final String name;
  final String district;
  final String address;
  final double latitude;
  final double longitude;
  final int commodityCount;
  final String? phone;
  final String? openHours;
  final String? imageUrl;
  final bool isActive;

  MarketModel({
    required this.id,
    required this.name,
    required this.district,
    required this.address,
    required this.latitude,
    required this.longitude,
    required this.commodityCount,
    this.phone,
    this.openHours,
    this.imageUrl,
    this.isActive = true,
  });

  factory MarketModel.fromJson(Map<String, dynamic> json) {
    return MarketModel(
      id: json['id']?.toString() ?? '',
      name: json['name'] ?? '',
      district: json['district'] ?? '',
      address: json['address'] ?? '',
      latitude: (json['latitude'] ?? 0).toDouble(),
      longitude: (json['longitude'] ?? 0).toDouble(),
      commodityCount: json['commodity_count'] ?? 0,
      phone: json['phone'],
      openHours: json['open_hours'],
      imageUrl: json['image_url'],
      isActive: json['is_active'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'district': district,
      'address': address,
      'latitude': latitude,
      'longitude': longitude,
      'commodity_count': commodityCount,
      'phone': phone,
      'open_hours': openHours,
      'image_url': imageUrl,
      'is_active': isActive,
    };
  }
}