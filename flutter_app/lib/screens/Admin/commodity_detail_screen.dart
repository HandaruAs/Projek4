import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class CommodityDetailScreen extends StatefulWidget {
  final String commodityId;

  const CommodityDetailScreen({
    super.key,
    required this.commodityId,
  });

  @override
  State<CommodityDetailScreen> createState() => _CommodityDetailScreenState();
}

class _CommodityDetailScreenState extends State<CommodityDetailScreen> {
  final NumberFormat currencyFormat = NumberFormat.currency(
    locale: 'id',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final commodityProvider =
        Provider.of<CommodityProvider>(context, listen: false);
    await commodityProvider.loadCommodityDetail(widget.commodityId);
    await commodityProvider.loadPriceHistory(widget.commodityId);
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<CommodityProvider>(
      builder: (context, commodityProvider, child) {
        final commodity = commodityProvider.selectedCommodity;

        if (commodityProvider.isLoading || commodity == null) {
          return const Scaffold(
            body: Center(child: LoadingWidget()),
          );
        }

        return Scaffold(
          appBar: AppBar(
            title: Text(commodity.name),
          ),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Price Card
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Harga Hari Ini',
                                  style: TextStyle(
                                    color: Colors.grey.shade600,
                                    fontSize: 14,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  currencyFormat.format(commodity.currentPrice),
                                  style: const TextStyle(
                                    fontSize: 28,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF1976D2),
                                  ),
                                ),
                              ],
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 6,
                              ),
                              decoration: BoxDecoration(
                                color: commodity.isIncreasing
                                    ? Colors.green.shade50
                                    : Colors.red.shade50,
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    commodity.isIncreasing
                                        ? Icons.arrow_upward
                                        : Icons.arrow_downward,
                                    size: 16,
                                    color: commodity.isIncreasing
                                        ? Colors.green.shade600
                                        : Colors.red.shade600,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    commodity.changePercentage,
                                    style: TextStyle(
                                      color: commodity.isIncreasing
                                          ? Colors.green.shade600
                                          : Colors.red.shade600,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        const Divider(),
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildInfoItem(
                              'Harga Kemarin',
                              currencyFormat.format(commodity.previousPrice),
                            ),
                            _buildInfoItem(
                              'Rata-rata Mingguan',
                              currencyFormat.format(
                                commodity.currentPrice * 0.95 +
                                    500, // Example calculation
                              ),
                            ),
                            _buildInfoItem(
                              'Satuan',
                              commodity.unit,
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 20),

                // Price Chart
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Grafik Harga Historis',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Period Selector
                        Row(
                          children: [
                            _buildPeriodChip(
                                '7 Hari', '7days', commodityProvider),
                            const SizedBox(width: 8),
                            _buildPeriodChip(
                                '30 Hari', '30days', commodityProvider),
                            const SizedBox(width: 8),
                            _buildPeriodChip(
                                '3 Bulan', '3months', commodityProvider),
                          ],
                        ),
                        const SizedBox(height: 20),

                        // Chart
                        SizedBox(
                          height: 200,
                          child: commodityProvider.priceHistory.isEmpty
                              ? Center(
                                  child: Text(
                                    'Tidak ada data grafik',
                                    style:
                                        TextStyle(color: Colors.grey.shade600),
                                  ),
                                )
                              : LineChart(
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
                                          reservedSize: 40,
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
                                          reservedSize: 22,
                                          getTitlesWidget: (value, meta) {
                                            if (value.toInt() >= 0 &&
                                                value.toInt() <
                                                    commodityProvider
                                                        .priceHistory.length) {
                                              final date = commodityProvider
                                                  .priceHistory[value.toInt()]
                                                  .date;
                                              return Text(
                                                DateFormat('dd/MM')
                                                    .format(date),
                                                style: TextStyle(
                                                  color: Colors.grey.shade600,
                                                  fontSize: 10,
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
                                    borderData: FlBorderData(show: false),
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
                                        dotData: const FlDotData(show: false),
                                        belowBarData: BarAreaData(
                                          show: true,
                                          color: const Color(0xFF1976D2)
                                              .withOpacity(0.1),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 20),

                // Additional Info
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Informasi Tambahan',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 12),
                        _buildInfoRow('Kategori', commodity.category),
                        _buildInfoRow('Tersedia di', '15 Pasar'),
                        _buildInfoRow('Update Terakhir',
                            DateFormat('dd MMM yyyy').format(DateTime.now())),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildInfoItem(String label, String value) {
    return Column(
      children: [
        Text(
          label,
          style: TextStyle(
            color: Colors.grey.shade600,
            fontSize: 12,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 14,
          ),
        ),
      ],
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              color: Colors.grey.shade600,
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPeriodChip(
      String label, String value, CommodityProvider provider) {
    final isSelected = provider.selectedPeriod == value;

    return InkWell(
      onTap: () {
        provider.setSelectedPeriod(value);
        provider.loadPriceHistory(widget.commodityId, period: value);
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF1976D2) : Colors.grey.shade100,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : Colors.grey.shade700,
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
          ),
        ),
      ),
    );
  }
}
