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
  String  _selectedPeriod = '7days';

  final NumberFormat currencyFormat = NumberFormat.currency(
    locale: 'id', symbol: 'Rp ', decimalDigits: 0,
  );

  final List<Map<String, String>> _periodOptions = const [
    {'value': '7days',   'label': '7 Hari'},
    {'value': '30days',  'label': '30 Hari'},
    {'value': '3months', 'label': '3 Bulan'},
  ];

  @override
  void initState() {
    super.initState();
    _loadCommodities();
  }

  Future<void> _loadCommodities() async {
    final provider = Provider.of<CommodityProvider>(context, listen: false);
    await provider.loadCommodities();

    if (provider.commodities.isNotEmpty && _selectedCommodityId == null) {
      setState(() => _selectedCommodityId = provider.commodities.first.id);
      _loadPriceHistory(provider.commodities.first.id);
    }
  }

  Future<void> _loadPriceHistory(String commodityId) async {
    if (!mounted) return;
    final provider = Provider.of<CommodityProvider>(context, listen: false);
    await provider.loadPriceHistory(commodityId, period: _selectedPeriod);
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<CommodityProvider>(
      builder: (context, provider, child) {
        return Scaffold(
          body: Column(
            children: [
              // ── Filter Section ───────────────────────────
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
                    const Text('Pilih Komoditas',
                        style: TextStyle(fontWeight: FontWeight.w500)),
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
                          items: provider.commodities.map((commodity) {
                            return DropdownMenuItem(
                              value: commodity.id,
                              child: Text(commodity.name,
                                  style: const TextStyle(fontSize: 14)),
                            );
                          }).toList(),
                          onChanged: (value) {
                            setState(() => _selectedCommodityId = value);
                            if (value != null) _loadPriceHistory(value);
                          },
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text('Rentang Waktu',
                        style: TextStyle(fontWeight: FontWeight.w500)),
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
                                setState(() => _selectedPeriod = period['value']!);
                                if (_selectedCommodityId != null) {
                                  _loadPriceHistory(_selectedCommodityId!);
                                }
                              },
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 16, vertical: 8),
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

              // ── Chart Section ────────────────────────────
              Expanded(
                child: provider.isLoading
                    ? const Center(child: LoadingWidget())
                    : provider.priceHistory.isEmpty
                        ? _buildEmptyState()
                        : Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              children: [
                                _buildSummaryCard(provider),
                                const SizedBox(height: 20),
                                Expanded(child: _buildLineChart(provider)),
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

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 120, height: 120,
            decoration: BoxDecoration(
              color: Colors.grey.shade100, shape: BoxShape.circle,
            ),
            child: Icon(Icons.show_chart, size: 48, color: Colors.grey.shade400),
          ),
          const SizedBox(height: 16),
          Text('Tidak ada data grafik',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w500,
                  color: Colors.grey.shade600)),
          const SizedBox(height: 8),
          Text('Pilih komoditas dan rentang waktu',
              style: TextStyle(fontSize: 14, color: Colors.grey.shade500)),
        ],
      ),
    );
  }

  Widget _buildSummaryCard(CommodityProvider provider) {
    final prices  = provider.priceHistory.map((e) => e.price).toList();
    final highest = prices.reduce((a, b) => a > b ? a : b);
    final lowest  = prices.reduce((a, b) => a < b ? a : b);
    final average = prices.reduce((a, b) => a + b) / prices.length;

    // Hitung perubahan keseluruhan (first vs last)
    final first  = provider.priceHistory.first;
    final last   = provider.priceHistory.last;
    final change = last.persen;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildSummaryItem('Tertinggi', currencyFormat.format(highest),
                    Icons.trending_up, Colors.green),
                Container(height: 30, width: 1, color: Colors.grey.shade300),
                _buildSummaryItem('Terendah', currencyFormat.format(lowest),
                    Icons.trending_down, Colors.red),
                Container(height: 30, width: 1, color: Colors.grey.shade300),
                _buildSummaryItem('Rata-rata', currencyFormat.format(average),
                    Icons.calculate, const Color(0xFF1976D2)),
              ],
            ),
            if (provider.priceHistory.length > 1) ...[
              const SizedBox(height: 12),
              const Divider(height: 1),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text('Perubahan terakhir: ',
                      style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                  Icon(
                    change > 0 ? Icons.arrow_upward
                        : change < 0 ? Icons.arrow_downward : Icons.remove,
                    size: 14,
                    color: change > 0 ? Colors.green
                        : change < 0 ? Colors.red : Colors.grey,
                  ),
                  Text(
                    '${change.abs().toStringAsFixed(2)}%',
                    style: TextStyle(
                      fontSize: 12, fontWeight: FontWeight.bold,
                      color: change > 0 ? Colors.green
                          : change < 0 ? Colors.red : Colors.grey,
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildLineChart(CommodityProvider provider) {
    return LineChart(
      LineChartData(
        gridData: FlGridData(
          show: true,
          drawVerticalLine: false,
          getDrawingHorizontalLine: (value) =>
              FlLine(color: Colors.grey.shade200, strokeWidth: 1),
        ),
        titlesData: FlTitlesData(
          leftTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 60,
              getTitlesWidget: (value, meta) => Text(
                NumberFormat.compact(locale: 'id').format(value),
                style: TextStyle(color: Colors.grey.shade600, fontSize: 10),
              ),
            ),
          ),
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 30,
              interval: _getIntervalForPeriod(_selectedPeriod),
              getTitlesWidget: (value, meta) {
                final idx = value.toInt();
                if (idx >= 0 && idx < provider.priceHistory.length) {
                  return Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text(
                      _formatDateForPeriod(
                          provider.priceHistory[idx].date, _selectedPeriod),
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 10),
                    ),
                  );
                }
                return const Text('');
              },
            ),
          ),
          rightTitles: const AxisTitles(
              sideTitles: SideTitles(showTitles: false)),
          topTitles: const AxisTitles(
              sideTitles: SideTitles(showTitles: false)),
        ),
        borderData: FlBorderData(
          show: true,
          border: Border.all(color: Colors.grey.shade300, width: 1),
        ),
        minY: _getMinY(provider.priceHistory),
        maxY: _getMaxY(provider.priceHistory),
        lineBarsData: [
          LineChartBarData(
            spots: provider.priceHistory.asMap().entries.map((entry) =>
                FlSpot(entry.key.toDouble(), entry.value.price)).toList(),
            isCurved: true,
            color: const Color(0xFF1976D2),
            barWidth: 3,
            isStrokeCapRound: true,
            dotData: FlDotData(
              show: true,
              getDotPainter: (spot, percent, barData, index) =>
                  FlDotCirclePainter(
                    radius: 4,
                    color: Colors.white,
                    strokeWidth: 2,
                    strokeColor: const Color(0xFF1976D2),
                  ),
            ),
            belowBarData: BarAreaData(
              show: true,
              color: const Color(0xFF1976D2).withValues(alpha: 0.1),
            ),
          ),
        ],
        lineTouchData: LineTouchData(
          touchTooltipData: LineTouchTooltipData(
            getTooltipItems: (List<LineBarSpot> touchedSpots) {
              return touchedSpots.map((spot) {
                final item    = provider.priceHistory[spot.x.toInt()];
                final persen  = item.persen;
                final persenStr = persen > 0
                    ? '▲ ${persen.toStringAsFixed(2)}%'
                    : persen < 0
                        ? '▼ ${persen.abs().toStringAsFixed(2)}%'
                        : '— 0%';

                return LineTooltipItem(
                  '${DateFormat('dd MMM yyyy').format(item.date)}\n',
                  const TextStyle(color: Colors.white,
                      fontWeight: FontWeight.bold, fontSize: 12),
                  children: [
                    TextSpan(
                      text: '${currencyFormat.format(spot.y)}\n',
                      style: const TextStyle(color: Colors.white,
                          fontWeight: FontWeight.normal, fontSize: 12),
                    ),
                    TextSpan(
                      text: persenStr,
                      style: TextStyle(
                        fontSize: 11,
                        color: persen > 0 ? Colors.greenAccent
                            : persen < 0 ? Colors.redAccent : Colors.white70,
                      ),
                    ),
                  ],
                );
              }).toList();
            },
          ),
        ),
      ),
    );
  }

  Widget _buildSummaryItem(
      String label, String value, IconData icon, Color color) {
    return Column(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(height: 4),
        Text(label,
            style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
        const SizedBox(height: 2),
        Text(value,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
      ],
    );
  }

  double _getMinY(List priceHistory) {
    if (priceHistory.isEmpty) return 0;
    return priceHistory.map((e) => e.price).reduce((a, b) => a < b ? a : b) * 0.95;
  }

  double _getMaxY(List priceHistory) {
    if (priceHistory.isEmpty) return 10000;
    return priceHistory.map((e) => e.price).reduce((a, b) => a > b ? a : b) * 1.05;
  }

  double _getIntervalForPeriod(String period) {
    switch (period) {
      case '7days':   return 1;
      case '30days':  return 5;
      case '3months': return 10;
      default:        return 1;
    }
  }

  String _formatDateForPeriod(DateTime date, String period) {
    switch (period) {
      case '7days':
      case '30days':  return DateFormat('dd/MM').format(date);
      case '3months': return DateFormat('dd MMM').format(date);
      default:        return DateFormat('dd/MM').format(date);
    }
  }
}