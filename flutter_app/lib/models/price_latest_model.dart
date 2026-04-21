class PriceLatestModel {
  final String commodityId;
  final String commodityName;
  final String category;
  final String unit;
  final String satuan;
  final double hargaSekarang;
  final double hargaLama;
  final double selisih;
  final double persen;
  final String? date;

  PriceLatestModel({
    required this.commodityId,
    required this.commodityName,
    required this.category,
    required this.unit,
    required this.satuan,
    required this.hargaSekarang,
    required this.hargaLama,
    required this.selisih,
    required this.persen,
    this.date,
  });

  bool get isNaik   => selisih > 0;
  bool get isTurun  => selisih < 0;
  bool get isStabil => selisih == 0;

  // Format persen: "+2.1%" / "-1.3%" / "0%"
  String get persenFormatted {
    if (persen == 0) return '0%';
    final sign = persen > 0 ? '+' : '';
    return '$sign${persen.toStringAsFixed(1)}%';
  }

  factory PriceLatestModel.fromJson(Map<String, dynamic> json) {
    return PriceLatestModel(
      commodityId:   json['commodity_id']?.toString() ?? '',
      commodityName: json['commodity_name']?.toString() ?? '',
      category:      json['category']?.toString() ?? '',
      unit:          json['unit']?.toString() ?? 'kg',
      satuan:        json['satuan']?.toString() ?? '',
      hargaSekarang: (json['harga_sekarang'] ?? 0).toDouble(),
      hargaLama:     (json['harga_lama'] ?? 0).toDouble(),
      selisih:       (json['selisih'] ?? 0).toDouble(),
      persen:        (json['persen'] ?? 0).toDouble(),
      date:          json['date']?.toString(),
    );
  }
}