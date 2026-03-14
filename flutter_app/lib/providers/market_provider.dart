import 'package:flutter/material.dart';
import 'package:flutter_app/models/market_model.dart';
import 'package:flutter_app/services/api_service.dart';

class MarketProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  
  List<MarketModel> _markets = [];
  MarketModel? _selectedMarket;
  bool _isLoading = false;
  String? _errorMessage;
  String _searchQuery = '';

  List<MarketModel> get markets => _markets;
  MarketModel? get selectedMarket => _selectedMarket;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String get searchQuery => _searchQuery;

  List<MarketModel> get filteredMarkets {
    if (_searchQuery.isEmpty) return _markets;
    
    return _markets.where((market) {
      return market.name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
             market.district.toLowerCase().contains(_searchQuery.toLowerCase());
    }).toList();
  }

  Future<void> loadMarkets({String? search}) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await _apiService.getMarkets(search: search);
      
      if (response['status'] == 'success' && response['data'] != null) {
        List<dynamic> marketsData = response['data']['markets'] ?? [];
        _markets = marketsData
            .map((item) => MarketModel.fromJson(item))
            .toList();
      } else {
        _markets = [];
      }
    } catch (e) {
      _errorMessage = e.toString();
      _markets = [];
    } finally {
      _setLoading(false);
    }
  }

  Future<void> loadMarketDetail(String marketId) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await _apiService.getMarketDetail(marketId);
      
      if (response['status'] == 'success' && response['data'] != null) {
        _selectedMarket = MarketModel.fromJson(response['data']['market']);
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _setLoading(false);
    }
  }

  void setSearchQuery(String query) {
    _searchQuery = query;
    notifyListeners();
  }

  void selectMarket(MarketModel market) {
    _selectedMarket = market;
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