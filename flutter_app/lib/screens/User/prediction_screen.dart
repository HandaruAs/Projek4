import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class UserPredictionScreen extends StatefulWidget {
  const UserPredictionScreen({super.key});

  @override
  State<UserPredictionScreen> createState() => _UserPredictionScreenState();
}

class _UserPredictionScreenState extends State<UserPredictionScreen> {
  String? _selectedCommodityId;
  String  _selectedPeriod = '7days';
  bool    _hasLoaded      = false;

  final NumberFormat _fmt = NumberFormat.currency(
    locale: 'id', symbol: 'Rp ', decimalDigits: 0,
  );

  final List<Map<String, String>> _periods = const [
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
    }
  }

  Future<void> _loadChart() async {
    if (_selectedCommodityId == null) return;
    final provider = Provider.of<CommodityProvider>(context, listen: false);
    await provider.loadPriceHistory(_selectedCommodityId!, period: _selectedPeriod);
    setState(() => _hasLoaded = true);
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark      = Theme.of(context).brightness == Brightness.dark;
    final cardBg      = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    final textPrimary = isDark ? Colors.white : const Color(0xFF1A1A2E);
    final textSub     = isDark ? Colors.grey[400]! : Colors.grey[600]!;

    return Consumer<CommodityProvider>(
      builder: (context, provider, _) {
        final history = provider.priceHistory;

        // Statistik dari histori
        double? minPrice, maxPrice, avgPrice;
        if (history.isNotEmpty) {
          final prices = history.map((e) => e.price).toList();
          minPrice = prices.reduce((a, b) => a < b ? a : b);
          maxPrice = prices.reduce((a, b) => a > b ? a : b);
          avgPrice = prices.reduce((a, b) => a + b) / prices.length;
        }

        return Scaffold(
          body: Stack(
            children: [
              SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [

                    // ── HEADER ──────────────────────────
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF1565C0), Color(0xFF42A5F5)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.2),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.show_chart, color: Colors.white, size: 20),
                              ),
                              const SizedBox(width: 12),
                              const Text(
                                'Tren & Prediksi Harga',
                                style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'Lihat tren harga historis dan proyeksi ke depan untuk perencanaan yang lebih baik.',
                            style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.4),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),

                    // ── FILTER CARD ──────────────────────
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: cardBg,
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(isDark ? 0.3 : 0.06),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [

                          // Pilih Komoditas
                          Text('Pilih Komoditas',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: textPrimary)),
                          const SizedBox(height: 8),
                          DropdownButtonFormField<String>(
                            isExpanded: true,
                            value: _selectedCommodityId,
                            dropdownColor: isDark ? const Color(0xFF2C2C2C) : Colors.white,
                            style: TextStyle(color: textPrimary, fontSize: 14),
                            items: provider.commodities.map((c) {
                              return DropdownMenuItem(value: c.id, child: Text(c.name));
                            }).toList(),
                            onChanged: (val) => setState(() => _selectedCommodityId = val),
                            decoration: InputDecoration(
                              filled: true,
                              fillColor: isDark ? const Color(0xFF2C2C2C) : Colors.grey.shade50,
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10),
                                borderSide: BorderSide(
                                  color: isDark ? Colors.grey.shade700 : Colors.grey.shade300,
                                ),
                              ),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                            ),
                          ),

                          const SizedBox(height: 16),

                          // Pilih Periode
                          Text('Pilih Periode',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: textPrimary)),
                          const SizedBox(height: 10),
                          Row(
                            children: _periods.map((p) {
                              final isActive = _selectedPeriod == p['value'];
                              return Expanded(
                                child: Padding(
                                  padding: const EdgeInsets.only(right: 8),
                                  child: GestureDetector(
                                    onTap: () => setState(() => _selectedPeriod = p['value']!),
                                    child: AnimatedContainer(
                                      duration: const Duration(milliseconds: 200),
                                      padding: const EdgeInsets.symmetric(vertical: 10),
                                      decoration: BoxDecoration(
                                        color: isActive
                                            ? const Color(0xFF1976D2)
                                            : (isDark ? const Color(0xFF2C2C2C) : Colors.grey.shade100),
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(
                                          color: isActive
                                              ? const Color(0xFF1976D2)
                                              : (isDark ? Colors.grey.shade700 : Colors.grey.shade300),
                                        ),
                                      ),
                                      child: Text(
                                        p['label']!,
                                        textAlign: TextAlign.center,
                                        style: TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.w600,
                                          color: isActive ? Colors.white : textSub,
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              );
                            }).toList(),
                          ),

                          const SizedBox(height: 16),

                          // Tombol Lihat Tren
                          SizedBox(
                            width: double.infinity,
                            height: 48,
                            child: ElevatedButton.icon(
                              onPressed: provider.isLoading ? null : _loadChart,
                              icon: const Icon(Icons.bar_chart, size: 18),
                              label: const Text('Lihat Tren Harga', style: TextStyle(fontSize: 15)),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF1976D2),
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),

                    // ── HASIL ───────────────────────────
                    if (_hasLoaded) ...[

                      // Statistik ringkasan
                      if (history.isNotEmpty) ...[
                        Row(
                          children: [
                            Expanded(child: _statCard(
                              label: 'Harga Min',
                              value: _fmt.format(minPrice),
                              icon: Icons.arrow_downward,
                              color: Colors.green,
                              isDark: isDark,
                            )),
                            const SizedBox(width: 10),
                            Expanded(child: _statCard(
                              label: 'Rata-rata',
                              value: _fmt.format(avgPrice),
                              icon: Icons.remove,
                              color: const Color(0xFF1976D2),
                              isDark: isDark,
                            )),
                            const SizedBox(width: 10),
                            Expanded(child: _statCard(
                              label: 'Harga Max',
                              value: _fmt.format(maxPrice),
                              icon: Icons.arrow_upward,
                              color: Colors.red,
                              isDark: isDark,
                            )),
                          ],
                        ),

                        const SizedBox(height: 16),
                      ],

                      // Chart
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: cardBg,
                          borderRadius: BorderRadius.circular(14),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(isDark ? 0.3 : 0.06),
                              blurRadius: 8,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Grafik Tren Harga',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w700,
                                color: textPrimary,
                              ),
                            ),
                            Text(
                              '${history.length} data point',
                              style: TextStyle(fontSize: 11, color: textSub),
                            ),
                            const SizedBox(height: 16),

                            if (history.isEmpty)
                              Center(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 32),
                                  child: Text('Tidak ada data untuk periode ini',
                                      style: TextStyle(color: textSub)),
                                ),
                              )
                            else
                              SizedBox(
                                height: 200,
                                child: LineChart(
                                  LineChartData(
                                    gridData: FlGridData(
                                      show: true,
                                      getDrawingHorizontalLine: (value) => FlLine(
                                        color: isDark ? Colors.grey.shade800 : Colors.grey.shade200,
                                        strokeWidth: 1,
                                      ),
                                      getDrawingVerticalLine: (value) => FlLine(
                                        color: isDark ? Colors.grey.shade800 : Colors.grey.shade200,
                                        strokeWidth: 1,
                                      ),
                                    ),
                                    borderData: FlBorderData(show: false),
                                    titlesData: FlTitlesData(
                                      leftTitles: AxisTitles(
                                        sideTitles: SideTitles(
                                          showTitles: true,
                                          reservedSize: 60,
                                          getTitlesWidget: (val, meta) => Text(
                                            '${(val / 1000).toStringAsFixed(0)}k',
                                            style: TextStyle(fontSize: 10, color: textSub),
                                          ),
                                        ),
                                      ),
                                      bottomTitles: AxisTitles(
                                        sideTitles: SideTitles(
                                          showTitles: true,
                                          reservedSize: 24,
                                          interval: (history.length / 4).ceilToDouble(),
                                          getTitlesWidget: (val, meta) {
                                            final idx = val.toInt();
                                            if (idx < 0 || idx >= history.length) return const SizedBox();
                                            final date = history[idx].date;
                                            return Text(
                                              DateFormat('d/M').format(date),
                                              style: TextStyle(fontSize: 9, color: textSub),
                                            );
                                          },
                                        ),
                                      ),
                                      topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                                      rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                                    ),
                                    lineBarsData: [
                                      LineChartBarData(
                                        spots: history.asMap().entries.map((e) =>
                                            FlSpot(e.key.toDouble(), e.value.price)).toList(),
                                        isCurved: true,
                                        color: const Color(0xFF1976D2),
                                        barWidth: 2.5,
                                        dotData: const FlDotData(show: false),
                                        belowBarData: BarAreaData(
                                          show: true,
                                          color: const Color(0xFF1976D2).withOpacity(0.1),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Info note
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF1565C0).withOpacity(0.2)
                              : const Color(0xFFE3F2FD),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: isDark
                                ? const Color(0xFF1976D2).withOpacity(0.4)
                                : const Color(0xFFBBDEFB),
                          ),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(Icons.lightbulb_outline,
                                color: isDark ? const Color(0xFF64B5F6) : const Color(0xFF1565C0),
                                size: 18),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                'Tren harga diambil dari data pasar resmi Kabupaten Jember. '
                                'Gunakan data ini sebagai referensi perencanaan belanja.',
                                style: TextStyle(
                                  fontSize: 12,
                                  height: 1.5,
                                  color: isDark ? const Color(0xFF64B5F6) : const Color(0xFF1565C0),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),

              if (provider.isLoading) const LoadingWidget(),
            ],
          ),
        );
      },
    );
  }

  Widget _statCard({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
    required bool isDark,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(isDark ? 0.15 : 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 16),
          const SizedBox(height: 6),
          Text(label, style: TextStyle(fontSize: 10, color: Colors.grey[500])),
          const SizedBox(height: 2),
          Text(value,
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: color),
              maxLines: 1,
              overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }
}