import 'package:flutter/material.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/models/price_model.dart';
import 'package:flutter_app/services/api_service.dart';

class CommodityProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<CommodityModel>    _commodities     = [];
  CommodityModel?         _selectedCommodity;
  List<PriceModel>        _priceHistory    = [];
  Map<String, dynamic>?   _predictionResult;
  bool                    _isLoading       = false;
  String?                 _errorMessage;
  String                  _selectedPeriod  = '7days';

  List<CommodityModel>   get commodities      => _commodities;
  CommodityModel?        get selectedCommodity => _selectedCommodity;
  List<PriceModel>       get priceHistory     => _priceHistory;
  Map<String, dynamic>?  get predictionResult => _predictionResult;
  bool                   get isLoading        => _isLoading;
  String?                get errorMessage     => _errorMessage;
  String                 get selectedPeriod   => _selectedPeriod;

  // ── Load semua komoditas ────────────────────────────────
  Future<void> loadCommodities({bool forceReload = false}) async {
  // Skip jika sudah ada data
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
        _commodities  = [];
      }
    } catch (e) {
      _errorMessage = 'Koneksi gagal: $e';
      _commodities  = [];
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

  // ── Load histori harga ──────────────────────────────────
  Future<void> loadPriceHistory(
    String commodityId, {
    String period = '7days',
  }) async {
    _setLoading(true);
    _clearError();
    _selectedPeriod = period;
    _priceHistory   = [];
    notifyListeners();

    try {
      final response = await _apiService.getPriceHistory(commodityId, period);

      if (response['success'] == true && response['data'] != null) {
        final List data = response['data'];
        _priceHistory = data
            .map((e) => PriceModel.fromJson(e))
            .toList()
          // Wajib ascending untuk chart time series
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

  // ── Prediksi harga ──────────────────────────────────────
  Future<bool> predictPrice(String commodityId, double quantity) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.predictPrice(commodityId, quantity);

      if (response['success'] == true && response['data'] != null) {
        _predictionResult = response['data'];
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
    _priceHistory      = [];
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