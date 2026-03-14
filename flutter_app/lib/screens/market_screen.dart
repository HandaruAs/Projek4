import 'package:flutter/material.dart';
import 'package:flutter_app/providers/market_provider.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:flutter_app/widgets/market_card.dart';
import 'package:provider/provider.dart';

class MarketScreen extends StatefulWidget {
  const MarketScreen({super.key});

  @override
  State<MarketScreen> createState() => _MarketScreenState();
}

class _MarketScreenState extends State<MarketScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadMarkets();
    
    _searchController.addListener(() {
      final marketProvider = Provider.of<MarketProvider>(context, listen: false);
      marketProvider.setSearchQuery(_searchController.text);
    });
  }

  Future<void> _loadMarkets() async {
    final marketProvider = Provider.of<MarketProvider>(context, listen: false);
    await marketProvider.loadMarkets();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<MarketProvider>(
      builder: (context, marketProvider, child) {
        return RefreshIndicator(
          onRefresh: _loadMarkets,
          color: const Color(0xFF1976D2),
          child: CustomScrollView(
            slivers: [
              // Search Bar
              SliverPadding(
                padding: const EdgeInsets.all(16),
                sliver: SliverToBoxAdapter(
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'Cari pasar atau kecamatan...',
                      prefixIcon: const Icon(Icons.search),
                      suffixIcon: _searchController.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear),
                              onPressed: () {
                                _searchController.clear();
                                marketProvider.setSearchQuery('');
                              },
                            )
                          : null,
                    ),
                  ),
                ),
              ),
              
              // Markets List
              if (marketProvider.isLoading)
                const SliverFillRemaining(
                  child: Center(child: LoadingWidget()),
                )
              else if (marketProvider.filteredMarkets.isEmpty)
                SliverFillRemaining(
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.store_mall_directory,
                          size: 64,
                          color: Colors.grey.shade400,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Pasar tidak ditemukan',
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Coba kata kunci lain',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey.shade500,
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        final market = marketProvider.filteredMarkets[index];
                        return MarketCard(
                          market: market,
                          onTap: () {
                            _showMarketCommodities(context, market);
                          },
                        );
                      },
                      childCount: marketProvider.filteredMarkets.length,
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  void _showMarketCommodities(BuildContext context, dynamic market) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.9,
          minChildSize: 0.5,
          maxChildSize: 0.95,
          expand: false,
          builder: (context, scrollController) {
            return Container(
              padding: const EdgeInsets.all(20),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Handle bar
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  
                  // Market name and address
                  Text(
                    market.name,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1976D2),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    market.address,
                    style: TextStyle(
                      color: Colors.grey.shade600,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 16),
                  
                  // District info
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1976D2).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      'Kecamatan ${market.district}',
                      style: const TextStyle(
                        color: Color(0xFF1976D2),
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  
                  const SizedBox(height: 20),
                  const Divider(),
                  const SizedBox(height: 16),
                  
                  // Commodities list header
                  const Text(
                    'Daftar Komoditas',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 12),
                  
                  // Commodities list
                  Expanded(
                    child: ListView.builder(
                      controller: scrollController,
                      itemCount: 5, // This should be replaced with actual commodity count
                      itemBuilder: (context, index) {
                        return Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade50,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: ListTile(
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 8,
                            ),
                            leading: Container(
                              width: 50,
                              height: 50,
                              decoration: BoxDecoration(
                                color: const Color(0xFF1976D2).withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(
                                _getCommodityIcon(index),
                                color: const Color(0xFF1976D2),
                                size: 24,
                              ),
                            ),
                            title: Text(
                              _getCommodityName(index),
                              style: const TextStyle(
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            subtitle: const Text(
                              'Harga per kg',
                              style: TextStyle(
                                fontSize: 12,
                              ),
                            ),
                            trailing: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  _getCommodityPrice(index),
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF1976D2),
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 2,
                                  ),
                                  decoration: BoxDecoration(
                                    color: index % 2 == 0 
                                        ? Colors.green.shade50 
                                        : Colors.red.shade50,
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(
                                        index % 2 == 0 
                                            ? Icons.arrow_upward 
                                            : Icons.arrow_downward,
                                        size: 12,
                                        color: index % 2 == 0 
                                            ? Colors.green.shade600 
                                            : Colors.red.shade600,
                                      ),
                                      const SizedBox(width: 2),
                                      Text(
                                        index % 2 == 0 ? '+2.5%' : '-1.2%',
                                        style: TextStyle(
                                          color: index % 2 == 0 
                                              ? Colors.green.shade600 
                                              : Colors.red.shade600,
                                          fontSize: 11,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  // Helper methods for demo data
  IconData _getCommodityIcon(int index) {
    switch (index % 5) {
      case 0:
        return Icons.grass;
      case 1:
        return Icons.eco;
      case 2:
        return Icons.circle;
      case 3:
        return Icons.restaurant;
      default:
        return Icons.shopping_basket;
    }
  }

  String _getCommodityName(int index) {
    const commodities = [
      'Beras Premium',
      'Cabai Merah',
      'Bawang Merah',
      'Daging Sapi',
      'Minyak Goreng',
    ];
    return commodities[index % commodities.length];
  }

  String _getCommodityPrice(int index) {
    const prices = [
      'Rp 12.000',
      'Rp 35.000',
      'Rp 28.000',
      'Rp 120.000',
      'Rp 15.000',
    ];
    return prices[index % prices.length];
  }
}