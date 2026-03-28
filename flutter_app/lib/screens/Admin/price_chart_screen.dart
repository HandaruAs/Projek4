import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class PriceChartScreen extends StatefulWidget {
  const PriceChartScreen({super.key});

  @override
  State<PriceChartScreen> createState() => _PriceChartScreenState();
}

class _PriceChartScreenState extends State<PriceChartScreen> {
  String? _selectedCommodityId;
  String _selectedPeriod = '7days';
  final NumberFormat currencyFormat = NumberFormat.currency(
    locale: 'id',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  final List<Map<String, String>> _periodOptions = const [
    {'value': '7days', 'label': '7 Hari'},
    {'value': '30days', 'label': '30 Hari'},
    {'value': '3months', 'label': '3 Bulan'},
  ];

  @override
  void initState() {
    super.initState();
    _loadCommodities();
  }

  Future<void> _loadCommodities() async {
    final commodityProvider =
        Provider.of<CommodityProvider>(context, listen: false);
    await commodityProvider.loadCommodities();

    if (commodityProvider.commodities.isNotEmpty &&
        _selectedCommodityId == null) {
      setState(() {
        _selectedCommodityId = commodityProvider.commodities.first.id;
      });
      _loadPriceHistory(commodityProvider.commodities.first.id);
    }
  }

  Future<void> _loadPriceHistory(String commodityId) async {
    if (!mounted) return;
    final commodityProvider =
        Provider.of<CommodityProvider>(context, listen: false);
    await commodityProvider.loadPriceHistory(
      commodityId,
      period: _selectedPeriod,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<CommodityProvider>(
      builder: (context, commodityProvider, child) {
        return Scaffold(
          body: Column(
            children: [
              // Filter Section
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.grey.withValues(alpha: 0.1),
                      blurRadius: 10,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Commodity Selection
                    const Text(
                      'Pilih Komoditas',
                      style: TextStyle(
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade300),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value: _selectedCommodityId,
                          isExpanded: true,
                          hint: const Text('Pilih komoditas'),
                          items: commodityProvider.commodities.map((commodity) {
                            return DropdownMenuItem(
                              value: commodity.id,
                              child: Text(
                                commodity.name,
                                style: const TextStyle(
                                  fontSize: 14,
                                ),
                              ),
                            );
                          }).toList(),
                          onChanged: (value) {
                            setState(() {
                              _selectedCommodityId = value;
                            });
                            if (value != null) {
                              _loadPriceHistory(value);
                            }
                          },
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Period Selection
                    const Text(
                      'Rentang Waktu',
                      style: TextStyle(
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 8),
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: _periodOptions.map((period) {
                          final isSelected = _selectedPeriod == period['value'];
                          return Padding(
                            padding: const EdgeInsets.only(right: 8),
                            child: InkWell(
                              onTap: () {
                                setState(() {
                                  _selectedPeriod = period['value']!;
                                });
                                if (_selectedCommodityId != null) {
                                  _loadPriceHistory(_selectedCommodityId!);
                                }
                              },
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 8,
                                ),
                                decoration: BoxDecoration(
                                  color: isSelected
                                      ? const Color(0xFF1976D2)
                                      : Colors.grey.shade100,
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  period['label']!,
                                  style: TextStyle(
                                    color: isSelected
                                        ? Colors.white
                                        : Colors.grey.shade700,
                                    fontWeight: isSelected
                                        ? FontWeight.w600
                                        : FontWeight.normal,
                                    fontSize: 13,
                                  ),
                                ),
                              ),
                            ),
                          );
                        }).toList(),
                      ),
                    ),
                  ],
                ),
              ),

              // Chart Section
              Expanded(
                child: commodityProvider.isLoading
                    ? const Center(child: LoadingWidget())
                    : commodityProvider.priceHistory.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Container(
                                  width: 120,
                                  height: 120,
                                  decoration: BoxDecoration(
                                    color: Colors.grey.shade100,
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    Icons.show_chart,
                                    size: 48,
                                    color: Colors.grey.shade400,
                                  ),
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  'Tidak ada data grafik',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w500,
                                    color: Colors.grey.shade600,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  'Pilih komoditas dan rentang waktu',
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: Colors.grey.shade500,
                                  ),
                                ),
                              ],
                            ),
                          )
                        : Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              children: [
                                // Summary Card
                                Card(
                                  child: Padding(
                                    padding: const EdgeInsets.all(16),
                                    child: Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceAround,
                                      children: [
                                        _buildSummaryItem(
                                          'Tertinggi',
                                          currencyFormat.format(
                                            commodityProvider.priceHistory
                                                .map((e) => e.price)
                                                .reduce(
                                                    (a, b) => a > b ? a : b),
                                          ),
                                          Icons.trending_up,
                                          Colors.green,
                                        ),
                                        Container(
                                          height: 30,
                                          width: 1,
                                          color: Colors.grey.shade300,
                                        ),
                                        _buildSummaryItem(
                                          'Terendah',
                                          currencyFormat.format(
                                            commodityProvider.priceHistory
                                                .map((e) => e.price)
                                                .reduce(
                                                    (a, b) => a < b ? a : b),
                                          ),
                                          Icons.trending_down,
                                          Colors.red,
                                        ),
                                        Container(
                                          height: 30,
                                          width: 1,
                                          color: Colors.grey.shade300,
                                        ),
                                        _buildSummaryItem(
                                          'Rata-rata',
                                          currencyFormat.format(
                                            commodityProvider.priceHistory
                                                    .map((e) => e.price)
                                                    .reduce((a, b) => a + b) /
                                                commodityProvider
                                                    .priceHistory.length,
                                          ),
                                          Icons.calculate,
                                          const Color(0xFF1976D2),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 20),

                                // Line Chart
                                Expanded(
                                  child: LineChart(
                                    LineChartData(
                                      gridData: FlGridData(
                                        show: true,
                                        drawVerticalLine: false,
                                        getDrawingHorizontalLine: (value) {
                                          return FlLine(
                                            color: Colors.grey.shade200,
                                            strokeWidth: 1,
                                          );
                                        },
                                      ),
                                      titlesData: FlTitlesData(
                                        leftTitles: AxisTitles(
                                          sideTitles: SideTitles(
                                            showTitles: true,
                                            reservedSize: 50,
                                            getTitlesWidget: (value, meta) {
                                              return Text(
                                                currencyFormat
                                                    .format(value)
                                                    .replaceAll('Rp ', ''),
                                                style: TextStyle(
                                                  color: Colors.grey.shade600,
                                                  fontSize: 10,
                                                ),
                                              );
                                            },
                                          ),
                                        ),
                                        bottomTitles: AxisTitles(
                                          sideTitles: SideTitles(
                                            showTitles: true,
                                            reservedSize: 30,
                                            interval: _getIntervalForPeriod(
                                                _selectedPeriod),
                                            getTitlesWidget: (value, meta) {
                                              if (value.toInt() >= 0 &&
                                                  value.toInt() <
                                                      commodityProvider
                                                          .priceHistory
                                                          .length) {
                                                final date = commodityProvider
                                                    .priceHistory[value.toInt()]
                                                    .date;
                                                return Padding(
                                                  padding:
                                                      const EdgeInsets.only(
                                                          top: 8),
                                                  child: Text(
                                                    _formatDateForPeriod(
                                                        date, _selectedPeriod),
                                                    style: TextStyle(
                                                      color:
                                                          Colors.grey.shade600,
                                                      fontSize: 10,
                                                    ),
                                                  ),
                                                );
                                              }
                                              return const Text('');
                                            },
                                          ),
                                        ),
                                        rightTitles: const AxisTitles(
                                          sideTitles:
                                              SideTitles(showTitles: false),
                                        ),
                                        topTitles: const AxisTitles(
                                          sideTitles:
                                              SideTitles(showTitles: false),
                                        ),
                                      ),
                                      borderData: FlBorderData(
                                        show: true,
                                        border: Border.all(
                                          color: Colors.grey.shade300,
                                          width: 1,
                                        ),
                                      ),
                                      minY: _getMinY(
                                          commodityProvider.priceHistory),
                                      maxY: _getMaxY(
                                          commodityProvider.priceHistory),
                                      lineBarsData: [
                                        LineChartBarData(
                                          spots: commodityProvider.priceHistory
                                              .asMap()
                                              .entries
                                              .map((entry) {
                                            return FlSpot(
                                              entry.key.toDouble(),
                                              entry.value.price,
                                            );
                                          }).toList(),
                                          isCurved: true,
                                          color: const Color(0xFF1976D2),
                                          barWidth: 3,
                                          isStrokeCapRound: true,
                                          dotData: FlDotData(
                                            show: true,
                                            getDotPainter: (spot, percent,
                                                barData, index) {
                                              return FlDotCirclePainter(
                                                radius: 4,
                                                color: Colors.white,
                                                strokeWidth: 2,
                                                strokeColor:
                                                    const Color(0xFF1976D2),
                                              );
                                            },
                                          ),
                                          belowBarData: BarAreaData(
                                            show: true,
                                            color: const Color(0xFF1976D2)
                                                .withValues(alpha: 0.1),
                                          ),
                                        ),
                                      ],
                                      lineTouchData: LineTouchData(
                                        touchTooltipData: LineTouchTooltipData(
                                          getTooltipItems:
                                              (List<LineBarSpot> touchedSpots) {
                                            return touchedSpots.map((spot) {
                                              final date = commodityProvider
                                                  .priceHistory[spot.x.toInt()]
                                                  .date;
                                              return LineTooltipItem(
                                                '${DateFormat('dd MMM yyyy').format(date)}\n',
                                                const TextStyle(
                                                  color: Colors.white,
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 12,
                                                ),
                                                children: [
                                                  TextSpan(
                                                    text: currencyFormat
                                                        .format(spot.y),
                                                    style: const TextStyle(
                                                      color: Colors.white,
                                                      fontWeight:
                                                          FontWeight.normal,
                                                      fontSize: 12,
                                                    ),
                                                  ),
                                                ],
                                              );
                                            }).toList();
                                          },
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSummaryItem(
      String label, String value, IconData icon, Color color) {
    return Column(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(height: 4),
        Text(
          label,
          style: TextStyle(
            fontSize: 11,
            color: Colors.grey.shade600,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.bold,
          ),
        ),
      ],
    );
  }

  double _getMinY(List<dynamic> priceHistory) {
    if (priceHistory.isEmpty) return 0;
    final minPrice =
        priceHistory.map((e) => e.price).reduce((a, b) => a < b ? a : b);
    return minPrice * 0.95;
  }

  double _getMaxY(List<dynamic> priceHistory) {
    if (priceHistory.isEmpty) return 10000;
    final maxPrice =
        priceHistory.map((e) => e.price).reduce((a, b) => a > b ? a : b);
    return maxPrice * 1.05;
  }

  double _getIntervalForPeriod(String period) {
    switch (period) {
      case '7days':
        return 1;
      case '30days':
        return 3;
      case '3months':
        return 7;
      default:
        return 1;
    }
  }

  String _formatDateForPeriod(DateTime date, String period) {
    switch (period) {
      case '7days':
      case '30days':
        return DateFormat('dd/MM').format(date);
      case '3months':
        return DateFormat('dd MMM').format(date);
      default:
        return DateFormat('dd/MM').format(date);
    }
  }
}
