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

  // ── Field status prediksi (dari API baru) ──────────────
  final bool   isPrediction;
  final String statusPrediksi;  // 'aktif' | 'kadaluarsa' | 'belum_mulai' | 'tidak_ada'
  final bool   dalamRange;
  final bool   sudahKadaluarsa;
  final bool   belumMulai;
  final String? tanggalMulai;
  final String? tanggalAkhir;

  const PriceLatestModel({
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
    this.isPrediction    = false,
    this.statusPrediksi  = 'tidak_ada',
    this.dalamRange      = false,
    this.sudahKadaluarsa = false,
    this.belumMulai      = false,
    this.tanggalMulai,
    this.tanggalAkhir,
  });

  // ── Getters harga ────────────────────────────────────────
  bool get isNaik   => selisih > 0;
  bool get isTurun  => selisih < 0;
  bool get isStabil => selisih == 0;

  String get persenFormatted {
    if (persen == 0) return '0%';
    final sign = persen > 0 ? '+' : '';
    return '$sign${persen.toStringAsFixed(1)}%';
  }

  // ── Getters status prediksi ──────────────────────────────
  bool get isAktif      => statusPrediksi == 'aktif';
  bool get isKadaluarsa => statusPrediksi == 'kadaluarsa';
  bool get isBelumMulai => statusPrediksi == 'belum_mulai';
  bool get tidakAdaData => statusPrediksi == 'tidak_ada';

  /// Label badge untuk ditampilkan di UI
  String get statusLabel {
    switch (statusPrediksi) {
      case 'aktif':       return 'AI Aktif';
      case 'kadaluarsa':  return 'Kadaluarsa';
      case 'belum_mulai': return 'Segera';
      default:            return '';
    }
  }

  factory PriceLatestModel.fromJson(Map<String, dynamic> json) {
    return PriceLatestModel(
      commodityId:   json['commodity_id']?.toString()    ?? '',
      commodityName: json['commodity_name']?.toString()  ?? '',
      category:      json['category']?.toString()        ?? '',
      unit:          json['unit']?.toString()             ?? 'kg',
      satuan:        json['satuan']?.toString()           ?? '',
      hargaSekarang: (json['harga_sekarang'] as num?     ?? 0).toDouble(),
      hargaLama:     (json['harga_lama']     as num?     ?? 0).toDouble(),
      selisih:       (json['selisih']        as num?     ?? 0).toDouble(),
      persen:        (json['persen']         as num?     ?? 0).toDouble(),
      date:          json['date']?.toString(),
      // ── Status prediksi ──
      isPrediction:    json['is_prediction']    as bool? ?? false,
      statusPrediksi:  json['status_prediksi']?.toString() ?? 'tidak_ada',
      dalamRange:      json['dalam_range']      as bool? ?? false,
      sudahKadaluarsa: json['sudah_kadaluarsa'] as bool? ?? false,
      belumMulai:      json['belum_mulai']      as bool? ?? false,
      tanggalMulai:    json['tanggal_mulai']?.toString(),
      tanggalAkhir:    json['tanggal_akhir']?.toString(),
    );
  }
}