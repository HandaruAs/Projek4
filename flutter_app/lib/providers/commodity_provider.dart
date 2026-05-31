import 'package:flutter/material.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/models/price_model.dart';
import 'package:flutter_app/services/api_service.dart';

List<String> _predictableCommodities = [];
List<String> get predictableCommodities => _predictableCommodities;

class CommodityProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<CommodityModel> _commodities = [];
  CommodityModel? _selectedCommodity;
  List<PriceModel> _priceHistory = [];
  Map<String, dynamic>? _predictionResult;
  bool _isLoading = false;
  String? _errorMessage;
  String _selectedPeriod = '7days';
  List<String> _predictableCommodities = [];
  
  List<String> get predictableCommodities => _predictableCommodities;
  List<CommodityModel> get commodities => _commodities;
  CommodityModel? get selectedCommodity => _selectedCommodity;
  List<PriceModel> get priceHistory => _priceHistory;
  Map<String, dynamic>? get predictionResult => _predictionResult;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String get selectedPeriod => _selectedPeriod;

  // ── Load semua komoditas ────────────────────────────────
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

  // ── Load detail satu komoditas ──────────────────────────
  Future<void> loadCommodityDetail(String commodityId) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.getCommodityDetail(commodityId);

      if (response['success'] == true && response['data'] != null) {
        _selectedCommodity = CommodityModel.fromJson(response['data']);
      } else {
        _errorMessage = response['message'] ?? 'Komoditas tidak ditemukan';
      }
    } catch (e) {
      _errorMessage = 'Koneksi gagal: $e';
    } finally {
      _setLoading(false);
    }
  }

  // ── Load histori harga (dengan setState) ─────────────────
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
      final response = await _apiService.getPriceHistory(commodityId, period, commodityName: _selectedCommodity?.name,);

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

  // ── FUTUREBUILDER: Load histori harga (tanpa setState, return Future) ──
  // Method ini khusus untuk digunakan dengan FutureBuilder
  // Tidak mengubah state _priceHistory, hanya mengembalikan data
  Future<List<PriceModel>> loadPriceHistoryFuture(
    String commodityId, {
    String period = '7days',
  }) async {
    try {
      final response = await _apiService.getPriceHistory(commodityId, period);

      if (response['success'] == true && response['data'] != null) {
        final List data = response['data'];
        List<PriceModel> history = data.map((e) => PriceModel.fromJson(e)).toList();
        history.sort((a, b) => a.date.compareTo(b.date));
        return history;
      } else {
        throw Exception(response['message'] ?? 'Gagal memuat histori harga');
      }
    } catch (e) {
      throw Exception('Koneksi gagal: $e');
    }
  }

  // ── Prediksi harga ──────────────────────────────────────
  Future<bool> predictPrice(String commodityName, double quantity) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.predictPrice(commodityName, quantity);

      if (response['success'] == true && response['data'] != null) {
        final data = response['data'] as Map<String, dynamic>;

        // Response dari Laravel generate():
        // {
        //   "komoditas": "Beras Merah",
        //   "harga_terakhir": 14500,
        //   "forecast": [14600, 14700, ...],
        //   "tanggal_pred": ["2025-06-01", ...],
        //   "accuracy": { "mae": ..., "rmse": ..., "mape": ... }
        // }

        final List forecast = data['forecast'] as List? ?? [];
        final hargaTerakhir =
            (data['harga_terakhir'] as num?)?.toDouble() ?? 0.0;
        // Ambil prediksi harga bulan depan = rata-rata 14 hari forecast
        final hargaPrediksi = forecast.isNotEmpty
            ? forecast
                    .take(14)
                    .map((e) => (e as num).toDouble())
                    .reduce((a, b) => a + b) /
                forecast.take(14).length
            : hargaTerakhir;

        final totalSekarang = hargaTerakhir * quantity * 4;
        final totalPrediksi = hargaPrediksi * quantity * 4;
        final selisih = totalPrediksi - totalSekarang;

        _predictionResult = {
          'current_price': hargaTerakhir,
          'predicted_price': hargaPrediksi,
          'total_cost': totalPrediksi,
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

  // ── Filter lokal ────────────────────────────────────────
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

  // ── Clear ───────────────────────────────────────────────
  void clearSelectedCommodity() {
    _selectedCommodity = null;
    _priceHistory = [];
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

  // ── Helpers ─────────────────────────────────────────────
  void _setLoading(bool value) {
    _isLoading = value;
    Future.microtask(() => notifyListeners());
  }

  void _clearError() {
    _errorMessage = null;
  }
}