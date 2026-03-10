import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/models/market_model.dart';
import 'package:flutter_app/models/price_model.dart';
import 'package:flutter_app/models/user_model.dart';

class MockData {
  // Mock User
  static UserModel get mockUser {
    return UserModel(
      id: '1',
      name: 'OTW EPIM',
      email: 'otwepim@example.com',
      token: 'testi123',
    );
  }

  // Mock Commodities
  static List<CommodityModel> get mockCommodities {
    return [
      CommodityModel(
        id: '1',
        name: 'Beras Premium',
        category: 'Beras',
        unit: 'kg',
        currentPrice: 12000,
        previousPrice: 11800,
        priceChange: 200,
        changePercentage: '1.7%',
        isIncreasing: true,
        imageUrl: null,
      ),
      CommodityModel(
        id: '2',
        name: 'Cabai Merah',
        category: 'Sayuran',
        unit: 'kg',
        currentPrice: 35000,
        previousPrice: 36000,
        priceChange: 1000,
        changePercentage: '-2.8%',
        isIncreasing: false,
        imageUrl: null,
      ),
      CommodityModel(
        id: '3',
        name: 'Bawang Merah',
        category: 'Bumbu',
        unit: 'kg',
        currentPrice: 28000,
        previousPrice: 27500,
        priceChange: 500,
        changePercentage: '1.8%',
        isIncreasing: true,
        imageUrl: null,
      ),
      CommodityModel(
        id: '4',
        name: 'Daging Sapi',
        category: 'Daging',
        unit: 'kg',
        currentPrice: 120000,
        previousPrice: 118000,
        priceChange: 2000,
        changePercentage: '1.7%',
        isIncreasing: true,
        imageUrl: null,
      ),
      CommodityModel(
        id: '5',
        name: 'Minyak Goreng',
        category: 'Minyak',
        unit: 'liter',
        currentPrice: 15000,
        previousPrice: 15200,
        priceChange: 200,
        changePercentage: '-1.3%',
        isIncreasing: false,
        imageUrl: null,
      ),
      CommodityModel(
        id: '6',
        name: 'Telur Ayam',
        category: 'Telur',
        unit: 'kg',
        currentPrice: 26000,
        previousPrice: 25500,
        priceChange: 500,
        changePercentage: '2.0%',
        isIncreasing: true,
        imageUrl: null,
      ),
      CommodityModel(
        id: '7',
        name: 'Gula Pasir',
        category: 'Gula',
        unit: 'kg',
        currentPrice: 14000,
        previousPrice: 14200,
        priceChange: 200,
        changePercentage: '-1.4%',
        isIncreasing: false,
        imageUrl: null,
      ),
    ];
  }

  // Mock Markets
  static List<MarketModel> get mockMarkets {
    return [
      MarketModel(
        id: '1',
        name: 'Pasar Tanjung',
        district: 'Kaliwates',
        address: 'Jl. Tanjung No. 123, Kaliwates',
        latitude: -8.1845,
        longitude: 113.6682,
        commodityCount: 45,
        phone: '0331-123456',
        openHours: '05:00 - 17:00',
        imageUrl: null,
        isActive: true,
      ),
      MarketModel(
        id: '2',
        name: 'Pasar Mangli',
        district: 'Kaliwates',
        address: 'Jl. Mangli No. 45, Kaliwates',
        latitude: -8.1723,
        longitude: 113.6751,
        commodityCount: 38,
        phone: '0331-123457',
        openHours: '05:00 - 16:00',
        imageUrl: null,
        isActive: true,
      ),
      MarketModel(
        id: '3',
        name: 'Pasar Balung',
        district: 'Balung',
        address: 'Jl. Raya Balung No. 78, Balung',
        latitude: -8.2876,
        longitude: 113.5489,
        commodityCount: 42,
        phone: '0336-123458',
        openHours: '05:30 - 16:30',
        imageUrl: null,
        isActive: true,
      ),
      MarketModel(
        id: '4',
        name: 'Pasar Arjasa',
        district: 'Arjasa',
        address: 'Jl. Arjasa No. 12, Arjasa',
        latitude: -8.1123,
        longitude: 113.7234,
        commodityCount: 35,
        phone: '0331-123459',
        openHours: '05:00 - 15:00',
        imageUrl: null,
        isActive: true,
      ),
      MarketModel(
        id: '5',
        name: 'Pasar Kalisat',
        district: 'Kalisat',
        address: 'Jl. Raya Kalisat No. 56, Kalisat',
        latitude: -8.1234,
        longitude: 113.8123,
        commodityCount: 40,
        phone: '0331-123460',
        openHours: '05:00 - 16:00',
        imageUrl: null,
        isActive: true,
      ),
      MarketModel(
        id: '6',
        name: 'Pasar Mayang',
        district: 'Mayang',
        address: 'Jl. Mayang No. 34, Mayang',
        latitude: -8.1987,
        longitude: 113.7987,
        commodityCount: 32,
        phone: '0331-123461',
        openHours: '05:30 - 15:30',
        imageUrl: null,
        isActive: true,
      ),
    ];
  }

  // Mock Price History for chart
  static List<PriceModel> getMockPriceHistory(String commodityId, String period) {
    final now = DateTime.now();
    List<PriceModel> history = [];
    
    int days = 7;
    if (period == '30days') days = 30;
    if (period == '3months') days = 90;
    
    double basePrice = 12000;
    if (commodityId == '1') basePrice = 12000;
    else if (commodityId == '2') basePrice = 35000;
    else if (commodityId == '3') basePrice = 28000;
    else if (commodityId == '4') basePrice = 120000;
    else if (commodityId == '5') basePrice = 15000;
    else if (commodityId == '6') basePrice = 26000;
    else if (commodityId == '7') basePrice = 14000;
    
    String commodityName = 'Komoditas';
    if (commodityId == '1') commodityName = 'Beras Premium';
    else if (commodityId == '2') commodityName = 'Cabai Merah';
    else if (commodityId == '3') commodityName = 'Bawang Merah';
    else if (commodityId == '4') commodityName = 'Daging Sapi';
    else if (commodityId == '5') commodityName = 'Minyak Goreng';
    else if (commodityId == '6') commodityName = 'Telur Ayam';
    else if (commodityId == '7') commodityName = 'Gula Pasir';
    
    for (int i = days; i >= 0; i--) {
      final date = now.subtract(Duration(days: i));
      // Create some fluctuation in prices
      final variation = (i % 5 - 2) * 500.0;
      final price = basePrice + variation + (i * 10.0);
      
      history.add(PriceModel(
        date: date,
        price: price,
        commodityId: commodityId,
        commodityName: commodityName,
      ));
    }
    
    return history;
  }

  // Mock Prediction Result
  static Map<String, dynamic> getMockPredictionResult(String commodityId, double quantity) {
    double predictedPrice = 13000;
    String commodityName = 'Komoditas';
    
    if (commodityId == '1') {
      predictedPrice = 12500;
      commodityName = 'Beras Premium';
    } else if (commodityId == '2') {
      predictedPrice = 36000;
      commodityName = 'Cabai Merah';
    } else if (commodityId == '3') {
      predictedPrice = 29000;
      commodityName = 'Bawang Merah';
    } else if (commodityId == '4') {
      predictedPrice = 125000;
      commodityName = 'Daging Sapi';
    } else if (commodityId == '5') {
      predictedPrice = 15500;
      commodityName = 'Minyak Goreng';
    } else if (commodityId == '6') {
      predictedPrice = 27000;
      commodityName = 'Telur Ayam';
    } else if (commodityId == '7') {
      predictedPrice = 14500;
      commodityName = 'Gula Pasir';
    }
    
    return {
      'predicted_price': predictedPrice,
      'period': 'Minggu depan',
      'total_cost': predictedPrice * quantity,
      'confidence': 0.85,
      'commodity_id': commodityId,
      'commodity_name': commodityName,
      'quantity': quantity,
      'prediction_date': DateTime.now().add(const Duration(days: 7)).toIso8601String(),
    };
  }
}