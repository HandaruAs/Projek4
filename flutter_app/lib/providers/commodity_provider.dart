import 'package:flutter/material.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/models/price_model.dart';
import 'package:flutter_app/services/api_service.dart';

class CommodityProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  
  List<CommodityModel> _commodities = [];
  CommodityModel? _selectedCommodity;
  List<PriceModel> _priceHistory = [];
  Map<String, dynamic>? _predictionResult;
  
  bool _isLoading = false;
  String? _errorMessage;
  String _selectedPeriod = '7days';

  List<CommodityModel> get commodities => _commodities;
  CommodityModel? get selectedCommodity => _selectedCommodity;
  List<PriceModel> get priceHistory => _priceHistory;
  Map<String, dynamic>? get predictionResult => _predictionResult;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String get selectedPeriod => _selectedPeriod;

  Future<void> loadCommodities({String? date}) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await _apiService.getCommodities(date: date);
      
      if (response['status'] == 'success' && response['data'] != null) {
        List<dynamic> commoditiesData = response['data']['commodities'] ?? [];
        _commodities = commoditiesData
            .map((item) => CommodityModel.fromJson(item))
            .toList();
      } else {
        _commodities = [];
      }
    } catch (e) {
      _errorMessage = e.toString();
      _commodities = [];
    } finally {
      _setLoading(false);
    }
  }

  Future<void> loadCommodityDetail(String commodityId) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await _apiService.getCommodityDetail(commodityId);
      
      if (response['status'] == 'success' && response['data'] != null) {
        _selectedCommodity =
            CommodityModel.fromJson(response['data']['commodity']);
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _setLoading(false);
    }
  }

  Future<void> loadPriceHistory(String commodityId, {String period = '7days'}) async {
    _setLoading(true);
    _clearError();
    _selectedPeriod = period;
    
    try {
      final response = await _apiService.getPriceHistory(commodityId, period);
      
      if (response['status'] == 'success' && response['data'] != null) {
        List<dynamic> historyData = response['data']['history'] ?? [];
        _priceHistory = historyData
            .map((item) => PriceModel.fromJson(item))
            .toList();
      } else {
        _priceHistory = [];
      }
    } catch (e) {
      _errorMessage = e.toString();
      _priceHistory = [];
    } finally {
      _setLoading(false);
    }
  }

  Future<bool> predictPrice(String commodityId, double quantity) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await _apiService.predictPrice(commodityId, quantity);
      
      if (response['status'] == 'success' && response['data'] != null) {
        _predictionResult = response['data'];
        _setLoading(false);
        return true;
      }
      return false;
    } catch (e) {
      _errorMessage = e.toString();
      _setLoading(false);
      return false;
    }
  }

  void clearPrediction() {
    _predictionResult = null;
  }

  void setSelectedPeriod(String period) {
    _selectedPeriod = period;
    notifyListeners();
  }

  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void _clearError() {
    _errorMessage = null;
  }
}