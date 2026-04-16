class PriceModel {
  final String id;
  final DateTime date;
  final double hargaSekarang;
  final double hargaLama;
  final double selisih;
  final double persen;
  final String satuan;
  final String commodityId;
  final String commodityName;
  final String category;

  PriceModel({
    required this.id,
    required this.date,
    required this.hargaSekarang,
    required this.hargaLama,
    required this.selisih,
    required this.persen,
    required this.satuan,
    required this.commodityId,
    required this.commodityName,
    required this.category,
  });

  // Getter agar PriceChartScreen tidak perlu diubah banyak
  double get price => hargaSekarang;

  factory PriceModel.fromJson(Map<String, dynamic> json) {
    return PriceModel(
      id:             json['_id']?.toString() ?? '',
      date:           DateTime.parse(json['date'] ?? DateTime.now().toIso8601String()),
      hargaSekarang:  (json['harga_sekarang'] ?? 0).toDouble(),
      hargaLama:      (json['harga_lama'] ?? 0).toDouble(),
      selisih:        (json['selisih'] ?? 0).toDouble(),
      persen:         (json['persen'] ?? 0).toDouble(),
      satuan:         json['satuan']?.toString() ?? '',
      commodityId:    json['commodity_id']?.toString() ?? '',
      commodityName:  json['commodity_name']?.toString() ?? '',
      category:       json['category']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      '_id':            id,
      'date':           date.toIso8601String(),
      'harga_sekarang': hargaSekarang,
      'harga_lama':     hargaLama,
      'selisih':        selisih,
      'persen':         persen,
      'satuan':         satuan,
      'commodity_id':   commodityId,
      'commodity_name': commodityName,
      'category':       category,
    };
  }
}