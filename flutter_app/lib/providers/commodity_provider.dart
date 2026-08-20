import 'package:flutter/foundation.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/models/price_model.dart';
import 'package:flutter_app/services/api_service.dart';

// ── Top-level functions untuk compute() isolate ─────────────
CommodityForecastModel _parseForecast(Map<String, dynamic> json) {
  return CommodityForecastModel.fromJson(json);
}

CommodityModel _parseCommodityDetail(Map<String, dynamic> json) {
  return CommodityModel.fromJson(json);
}

class CommodityProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<CommodityModel> _commodities = [];
  CommodityModel? _selectedCommodity;
  List<PriceModel> _priceHistory = [];
  CommodityForecastModel _forecast = CommodityForecastModel.empty();
  Map<String, dynamic>? _predictionResult;

  bool _isLoading = false;
  bool _isLoadingForecast = false;
  String? _errorMessage;
  String? _forecastError;
  String _selectedPeriod = '7days';

  List<String> _predictableCommodities = [];

  // ── Getters ──────────────────────────────────────────────
  List<CommodityModel> get commodities => _commodities;
  CommodityModel? get selectedCommodity => _selectedCommodity;
  List<PriceModel> get priceHistory => _priceHistory;
  CommodityForecastModel get forecast => _forecast;
  Map<String, dynamic>? get predictionResult => _predictionResult;
  bool get isLoading => _isLoading;
  bool get isLoadingForecast => _isLoadingForecast;
  String? get errorMessage => _errorMessage;
  String? get forecastError => _forecastError;
  String get selectedPeriod => _selectedPeriod;
  List<String> get predictableCommodities => _predictableCommodities;

  // ── Load semua komoditas ─────────────────────────────────
  Future<void> loadCommodities({bool forceReload = false}) async {
    if (!forceReload && _commodities.isNotEmpty) return;
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.getCommodities();
      if (response['success'] == true && response['data'] != null) {
        final List data = response['data'];
        _commodities = data.map((e) => CommodityModel.fromJson(e)).toList();
      } else {
        _errorMessage = response['message'] ?? 'Gagal memuat data komoditas';
        _commodities = [];
      }
    } catch (e) {
      _errorMessage = 'Koneksi gagal: $e';
      _commodities = [];
    } finally {
      _setLoading(false);
    }
  }

  // ── Load detail + history secara parallel ────────────────
  // ✅ FIX: forecast TIDAK ikut Future.wait — jalan sendiri di background
  //         sehingga UI langsung muncul tanpa nunggu forecast selesai
  Future<void> loadDetailAndHistory(
    String commodityId,
    String period,
  ) async {
    // Reset state
    _isLoadingDetail = true;
    _isLoadingHistory = true;
    _detailError = null;
    _historyError = null;
    Future.microtask(() => notifyListeners());

    // Detail + history jalan parallel, forecast jalan sendiri
    await Future.wait([
      _loadDetailInternal(commodityId),
      _loadHistoryInternal(commodityId, period),
    ]);

    // Forecast dijalankan SETELAH detail & history selesai
    // Tidak di-await → UI tidak freeze
    loadForecast(commodityId);
  }

  // ── State untuk detail & history ────────────────────────
  bool _isLoadingDetail = false;
  bool _isLoadingHistory = false;
  String? _detailError;
  String? _historyError;

  bool get isLoadingDetail => _isLoadingDetail;
  bool get isLoadingHistory => _isLoadingHistory;
  String? get detailError => _detailError;
  String? get historyError => _historyError;

  Future<void> _loadDetailInternal(String commodityId) async {
    try {
      final response = await _apiService.getCommodityDetail(commodityId);
      if (response['success'] == true && response['data'] != null) {
        _selectedCommodity = await compute(
          _parseCommodityDetail,
          Map<String, dynamic>.from(response['data']),
        );
      } else {
        _detailError = response['message'] ?? 'Komoditas tidak ditemukan';
      }
    } catch (e) {
      _detailError = 'Koneksi gagal: $e';
    } finally {
      _isLoadingDetail = false;
      Future.microtask(() => notifyListeners());
    }
  }

  Future<void> _loadHistoryInternal(String commodityId, String period) async {
    _selectedPeriod = period;
    _priceHistory = [];

    try {
      final response = await _apiService.getPriceHistory(
        commodityId,
        period,
        commodityName: _selectedCommodity?.name,
      );
      if (response['success'] == true && response['data'] != null) {
        final List data = response['data'];
        _priceHistory = data.map((e) => PriceModel.fromJson(e)).toList()
          ..sort((a, b) => a.date.compareTo(b.date));
      } else {
        _historyError = response['message'] ?? 'Gagal memuat histori harga';
        _priceHistory = [];
      }
    } catch (e) {
      _historyError = 'Koneksi gagal: $e';
      _priceHistory = [];
    } finally {
      _isLoadingHistory = false;
      Future.microtask(() => notifyListeners());
    }
  }

  // ── Load detail komoditas (standalone) ──────────────────
  Future<void> loadCommodityDetail(String commodityId) async {
    _setLoading(true);
    _clearError();
    try {
      final response = await _apiService.getCommodityDetail(commodityId);
      if (response['success'] == true && response['data'] != null) {
        _selectedCommodity = await compute(
          _parseCommodityDetail,
          Map<String, dynamic>.from(response['data']),
        );
      } else {
        _errorMessage = response['message'] ?? 'Komoditas tidak ditemukan';
      }
    } catch (e) {
      _errorMessage = 'Koneksi gagal: $e';
    } finally {
      _setLoading(false);
    }
  }

  // ── Load forecast ─────────────────────────────────────────
  // ✅ FIX: berjalan sendiri di background, tidak blocking UI
  Future<void> loadForecast(String commodityId) async {
    if (_isLoadingForecast) return;
    _isLoadingForecast = true;
    _forecastError = null;
    Future.microtask(() => notifyListeners());

    try {
      final response = await _apiService.getCommodityForecast(commodityId);

      if (response['success'] == true) {
        _forecast = await compute(
          _parseForecast,
          Map<String, dynamic>.from(response),
        );
      } else {
        _forecastError = response['message'] ?? 'Gagal memuat forecast';
        _forecast = CommodityForecastModel.empty();
      }
    } catch (e) {
      _forecastError = 'Koneksi gagal: $e';
      _forecast = CommodityForecastModel.empty();
    } finally {
      _isLoadingForecast = false;
      Future.microtask(() => notifyListeners());
    }
  }

  // ── Load histori harga (standalone, untuk ganti period) ──
  Future<void> loadPriceHistory(
    String commodityId, {
    String period = '7days',
  }) async {
    _setLoading(true);
    _clearError();
    _selectedPeriod = period;
    _priceHistory = [];
    notifyListeners();

    try {
      final response = await _apiService.getPriceHistory(
        commodityId,
        period,
        commodityName: _selectedCommodity?.name,
      );
      if (response['success'] == true && response['data'] != null) {
        final List data = response['data'];
        _priceHistory = data.map((e) => PriceModel.fromJson(e)).toList()
          ..sort((a, b) => a.date.compareTo(b.date));
      } else {
        _errorMessage = response['message'] ?? 'Gagal memuat histori harga';
        _priceHistory = [];
      }
    } catch (e) {
      _errorMessage = 'Koneksi gagal: $e';
      _priceHistory = [];
    } finally {
      _setLoading(false);
    }
  }

  // ── Prediksi harga (untuk prediction screen) ─────────────
  Future<bool> predictPrice(String commodityName, double quantity) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.predictPrice(commodityName, quantity);
      if (response['success'] == true && response['data'] != null) {
        final data = response['data'] as Map<String, dynamic>;
        final List forecast = data['forecast'] as List? ?? [];
        final hargaTerakhir =
            (data['harga_terakhir'] as num?)?.toDouble() ?? 0.0;

        final hargaPrediksi = forecast.isNotEmpty
            ? forecast
                    .take(14)
                    .map((e) => (e as num).toDouble())
                    .reduce((a, b) => a + b) /
                forecast.take(14).length
            : hargaTerakhir;

        final selisih = (hargaPrediksi - hargaTerakhir) * quantity * 4;

        _predictionResult = {
          'current_price': hargaTerakhir,
          'predicted_price': hargaPrediksi,
          'total_cost': hargaPrediksi * quantity * 4,
          'selisih': selisih,
          'period': 'Bulan depan',
          'insight': 'AI memprediksi harga ${data['komoditas']} sekitar '
              'Rp ${hargaPrediksi.toStringAsFixed(0)} per kg bulan depan '
              'berdasarkan tren historis terkini.',
        };
        return true;
      } else {
        _errorMessage = response['message'] ?? 'Prediksi gagal';
        return false;
      }
    } catch (e) {
      _errorMessage = 'Koneksi gagal: $e';
      return false;
    } finally {
      _setLoading(false);
    }
  }

  Future<void> loadPredictableCommodities() async {
    _setLoading(true);
    _clearError();
    try {
      _predictableCommodities = await _apiService.getPredictableCommodities();
    } catch (e) {
      _errorMessage = 'Gagal memuat daftar komoditas: $e';
      _predictableCommodities = [];
    } finally {
      _setLoading(false);
    }
  }

  // ── Filter lokal ─────────────────────────────────────────
  List<CommodityModel> searchCommodities(String keyword) {
    if (keyword.isEmpty) return _commodities;
    return _commodities
        .where((c) => c.name.toLowerCase().contains(keyword.toLowerCase()))
        .toList();
  }

  List<CommodityModel> filterByCategory(String category) {
    if (category.isEmpty) return _commodities;
    return _commodities
        .where((c) => c.category.toLowerCase() == category.toLowerCase())
        .toList();
  }

  // ── Clear ────────────────────────────────────────────────
  void clearSelectedCommodity() {
    _selectedCommodity = null;
    _priceHistory = [];
    _forecast = CommodityForecastModel.empty();
    _detailError = null;
    _historyError = null;
    notifyListeners();
  }

  void clearPrediction() {
    _predictionResult = null;
    notifyListeners();
  }

  void setSelectedPeriod(String period) {
    _selectedPeriod = period;
    notifyListeners();
  }

  // ── Helpers ──────────────────────────────────────────────
  void _setLoading(bool value) {
    _isLoading = value;
    Future.microtask(() => notifyListeners());
  }

  void _clearError() {
    _errorMessage = null;
  }
}
