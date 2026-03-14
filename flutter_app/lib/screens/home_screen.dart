import 'package:flutter/material.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/screens/commodity_detail_screen.dart';
import 'package:flutter_app/widgets/commodity_card.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  DateTime _selectedDate = DateTime.now();
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadCommodities();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadCommodities() async {
    if (!mounted) return;
    final commodityProvider =
        Provider.of<CommodityProvider>(context, listen: false);
    await commodityProvider.loadCommodities();
  }

  Future<void> _selectDate(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: Color(0xFF1976D2),
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });

      if (!mounted) return;
      final commodityProvider =
          Provider.of<CommodityProvider>(context, listen: false);
      await commodityProvider.loadCommodities(
        date: DateFormat('yyyy-MM-dd').format(_selectedDate),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<CommodityProvider>(
      builder: (context, commodityProvider, child) {
        return RefreshIndicator(
          onRefresh: () => commodityProvider.loadCommodities(),
          color: const Color(0xFF1976D2),
          child: CustomScrollView(
            slivers: [
              SliverPadding(
                padding: const EdgeInsets.all(16),
                sliver: SliverToBoxAdapter(
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: InkWell(
                              onTap: () => _selectDate(context),
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 8,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(12),
                                  border:
                                      Border.all(color: Colors.grey.shade200),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(
                                      Icons.calendar_today,
                                      size: 16,
                                      color: Color(0xFF1976D2),
                                    ),
                                    const SizedBox(width: 8),
                                    Text(
                                      DateFormat('dd MMM yyyy')
                                          .format(_selectedDate),
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.grey.shade50,
                                borderRadius: BorderRadius.circular(12),
                                border:
                                    Border.all(color: Colors.grey.shade200),
                              ),
                              child: const Row(
                                children: [
                                  Icon(
                                    Icons.location_on,
                                    size: 16,
                                    color: Color(0xFF1976D2),
                                  ),
                                  SizedBox(width: 8),
                                  Text(
                                    'Jember',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),

                      /// SEARCH FIELD
                      TextField(
                        controller: _searchController,
                        decoration: const InputDecoration(
                          hintText: 'Cari komoditas...',
                          prefixIcon: Icon(Icons.search),
                          suffixIcon: Icon(Icons.tune),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              if (commodityProvider.isLoading)
                const SliverFillRemaining(
                  child: Center(child: LoadingWidget()),
                )
              else if (commodityProvider.commodities.isEmpty)
                SliverFillRemaining(
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.inbox,
                          size: 64,
                          color: Colors.grey.shade400,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Tidak ada data komoditas',
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey.shade600,
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
                        final commodity =
                            commodityProvider.commodities[index];
                        return GestureDetector(
                          onTap: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => CommodityDetailScreen(
                                  commodityId: commodity.id,
                                ),
                              ),
                            );
                          },
                          child: CommodityCard(commodity: commodity),
                        );
                      },
                      childCount: commodityProvider.commodities.length,
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }
}