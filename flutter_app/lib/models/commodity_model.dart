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
    double current  = (json['current_price']  as num? ?? 0).toDouble();
    double previous = (json['previous_price'] as num? ?? 0).toDouble();
    double change   = current - previous;
    bool increasing = change >= 0;

    String categoryName = '';
    if (json['category'] is Map) {
      categoryName = json['category']['name'] ?? '';
    } else {
      categoryName = json['category']?.toString() ?? '';
    }

    return CommodityModel(
      id:               json['_id']?.toString() ?? json['id']?.toString() ?? '',
      name:             json['name']?.toString() ?? '',
      category:         categoryName,
      unit:             json['unit']?.toString() ?? 'kg',
      currentPrice:     current,
      previousPrice:    previous,
      priceChange:      change.abs(),
      changePercentage: previous != 0
          ? '${((change / previous) * 100).toStringAsFixed(1)}%'
          : '0%',
      isIncreasing: increasing,
      imageUrl:     json['image_url']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id':             id,
    'name':           name,
    'category':       category,
    'unit':           unit,
    'current_price':  currentPrice,
    'previous_price': previousPrice,
  };
}

// ─────────────────────────────────────────────────────────────────────────────
// Model untuk response GET /api/commodities/{id}/forecast
// ─────────────────────────────────────────────────────────────────────────────
class CommodityForecastModel {
  final bool hasForecast;
  final String commodityName;
  final String satuan;

  // Harga dinamis hari ini (dari forecast)
  final double hargaHariIni;
  // Harga aktual terakhir (dari data real)
  final double hargaAktual;

  // Status prediksi
  final String statusPrediksi; // 'aktif' | 'kadaluarsa' | 'belum_mulai'
  final bool dalamRange;
  final bool sudahKadaluarsa;
  final bool belumMulai;
  final String? tanggalMulai;
  final String? tanggalAkhir;

  // Periode yang tersedia sesuai berapa hari admin generate
  final int totalForecastDays;
  final List<int> availablePeriods; // [30] / [30, 60] / [30, 60, 90]

  // Data forecast per hari
  final List<ForecastPoint> forecast;
  // Data historis 30 hari ke belakang
  final List<ForecastPoint> history;

  // Metadata
  final Map<String, dynamic>? accuracy;
  final String? generatedAt;

  const CommodityForecastModel({
    required this.hasForecast,
    required this.commodityName,
    required this.satuan,
    required this.hargaHariIni,
    required this.hargaAktual,
    required this.statusPrediksi,
    required this.dalamRange,
    required this.sudahKadaluarsa,
    required this.belumMulai,
    required this.tanggalMulai,
    required this.tanggalAkhir,
    required this.totalForecastDays,
    required this.availablePeriods,
    required this.forecast,
    required this.history,
    this.accuracy,
    this.generatedAt,
  });

  bool get isAktif      => statusPrediksi == 'aktif';
  bool get isKadaluarsa => statusPrediksi == 'kadaluarsa';
  bool get isBelumMulai => statusPrediksi == 'belum_mulai';

  /// Ambil slice forecast sesuai period (30/60/90)
  List<ForecastPoint> forecastForPeriod(int days) =>
      forecast.take(days).toList();

  factory CommodityForecastModel.fromJson(Map<String, dynamic> json) {
    final forecastList = (json['forecast'] as List? ?? [])
        .map((e) => ForecastPoint.fromJson(Map<String, dynamic>.from(e)))
        .toList();

    final historyList = (json['history'] as List? ?? [])
        .map((e) => ForecastPoint.fromJson(Map<String, dynamic>.from(e)))
        .toList();

    final periods = (json['available_periods'] as List? ?? [])
        .map((e) => (e as num).toInt())
        .toList();

    return CommodityForecastModel(
      hasForecast:       json['has_forecast'] as bool? ?? false,
      commodityName:     json['commodity_name']?.toString() ?? '',
      satuan:            json['satuan']?.toString() ?? 'kg',
      hargaHariIni:      (json['harga_hari_ini'] as num? ?? 0).toDouble(),
      hargaAktual:       (json['harga_aktual']   as num? ?? 0).toDouble(),
      statusPrediksi:    json['status_prediksi']?.toString() ?? '',
      dalamRange:        json['dalam_range']      as bool? ?? false,
      sudahKadaluarsa:   json['sudah_kadaluarsa'] as bool? ?? false,
      belumMulai:        json['belum_mulai']      as bool? ?? false,
      tanggalMulai:      json['tanggal_mulai']?.toString(),
      tanggalAkhir:      json['tanggal_akhir']?.toString(),
      totalForecastDays: (json['total_forecast_days'] as num? ?? 0).toInt(),
      availablePeriods:  periods,
      forecast:          forecastList,
      history:           historyList,
      accuracy:          json['accuracy'] != null
          ? Map<String, dynamic>.from(json['accuracy'])
          : null,
      generatedAt:       json['generated_at']?.toString(),
    );
  }

  /// Empty model untuk state awal / komoditas tanpa prediksi
  static CommodityForecastModel empty() => const CommodityForecastModel(
    hasForecast:       false,
    commodityName:     '',
    satuan:            'kg',
    hargaHariIni:      0,
    hargaAktual:       0,
    statusPrediksi:    '',
    dalamRange:        false,
    sudahKadaluarsa:   false,
    belumMulai:        false,
    tanggalMulai:      null,
    tanggalAkhir:      null,
    totalForecastDays: 0,
    availablePeriods:  [],
    forecast:          [],
    history:           [],
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// Satu titik data: bisa historis atau forecast
// ─────────────────────────────────────────────────────────────────────────────
class ForecastPoint {
  final DateTime date;
  final double harga;
  final bool isForecast;

  const ForecastPoint({
    required this.date,
    required this.harga,
    required this.isForecast,
  });

  factory ForecastPoint.fromJson(Map<String, dynamic> json) {
    return ForecastPoint(
      date:       DateTime.parse(json['date'] as String),
      harga:      (json['harga'] as num).toDouble(),
      isForecast: json['is_forecast'] as bool? ?? false,
    );
  }
}