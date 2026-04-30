import 'package:flutter/material.dart';
import 'package:flutter_app/models/price_latest_model.dart';
import 'package:flutter_app/services/api_service.dart';

class PriceProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<PriceLatestModel> _latestPrices  = [];
  List<PriceLatestModel> _topPrices     = [];
  List<String>           _categories    = [];
  bool                   _isLoading     = false;
  bool                   _isLoadingTop  = false;
  String?                _errorMessage;

  List<PriceLatestModel> get latestPrices  => _latestPrices;
  List<PriceLatestModel> get topPrices     => _topPrices;
  List<String>           get categories    => _categories;
  bool                   get isLoading     => _isLoading;
  bool                   get isLoadingTop  => _isLoadingTop;
  String?                get errorMessage  => _errorMessage;

  // ── Load harga terbaru semua komoditas ──────────────────
  Future<void> loadLatestPrices({
    String? category,
    String? search,
    bool forceReload = false,
  }) async {
    if (!forceReload && _latestPrices.isNotEmpty && category == null && search == null) return;

    _isLoading    = true;
    _errorMessage = null;
    Future.microtask(() => notifyListeners());

    try {
      final response = await _apiService.getLatestPrices(
        category: category,
        search: search,
      );

      if (response['success'] == true && response['data'] != null) {
        final List data = response['data'];
        _latestPrices = data.map((e) => PriceLatestModel.fromJson(e)).toList();
      } else {
        _errorMessage = response['message'] ?? 'Gagal memuat harga terbaru';
        _latestPrices = [];
      }
    } catch (e) {
      _errorMessage = 'Koneksi gagal: $e';
      _latestPrices = [];
    } finally {
      _isLoading = false;
      Future.microtask(() => notifyListeners());
    }
  }

  // ── Load top N harga tertinggi ──────────────────────────
  Future<void> loadTopPrices({int limit = 3}) async {
    _isLoadingTop = true;
    Future.microtask(() => notifyListeners());

    try {
      final response = await _apiService.getTopPrices(limit: limit);

      if (response['success'] == true && response['data'] != null) {
        final List data = response['data'];
        _topPrices = data.map((e) => PriceLatestModel.fromJson(e)).toList();
      } else {
        _topPrices = [];
      }
    } catch (e) {
      _topPrices = [];
    } finally {
      _isLoadingTop = false;
      Future.microtask(() => notifyListeners());
    }
  }

  // ── Load daftar kategori ────────────────────────────────
  Future<void> loadCategories() async {
    if (_categories.isNotEmpty) return;

    try {
      final response = await _apiService.getPriceCategories();

      if (response['success'] == true && response['data'] != null) {
        final List data = response['data'];
        _categories = ['Semua', ...data.map((e) => e.toString())];
      }
    } catch (_) {
      _categories = ['Semua'];
    }

    Future.microtask(() => notifyListeners());
  }

  // ── Filter lokal (tanpa API call ulang) ─────────────────
  List<PriceLatestModel> filterByCategory(String category) {
    if (category == 'Semua' || category.isEmpty) return _latestPrices;
    return _latestPrices
        .where((p) => p.category.toLowerCase() == category.toLowerCase())
        .toList();
  }

  List<PriceLatestModel> searchLocal(String keyword) {
    if (keyword.isEmpty) return _latestPrices;
    return _latestPrices
        .where((p) => p.commodityName.toLowerCase().contains(keyword.toLowerCase()))
        .toList();
  }

  // ── Reset ───────────────────────────────────────────────
  void clear() {
    _latestPrices = [];
    _topPrices    = [];
    _errorMessage = null;
    notifyListeners();
  }
}