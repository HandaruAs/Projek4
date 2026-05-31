import 'dart:async';
import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/models/price_model.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/screens/User/prediction_screen.dart';
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

class _CommodityDetailScreenState extends State<CommodityDetailScreen>
    with SingleTickerProviderStateMixin {
  // ── Format ──────────────────────────────────────────────────────────────────
  final NumberFormat _rupiahFmt = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  // ── State ───────────────────────────────────────────────────────────────────
  bool _isLoadingDetail = true;
  bool _isLoadingHistory = true;
  String? _detailError;
  String? _historyError;
  String _selectedPeriod = '7days';

  // ── Animation (pulse dot dihapus, tapi controller tetap untuk potensi pakai) ─
  late AnimationController _pulseController;

  // ── Real-time polling ────────────────────────────────────────────────────────
  Timer? _pollingTimer;
  static const Duration _pollInterval = Duration(seconds: 30);

  @override
  void initState() {
    super.initState();

    // Controller sudah tidak dipakai untuk live badge, tapi dispose tetap aman
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    );

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadDetail();
      _loadHistory(_selectedPeriod);
      _startPolling();
    });
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _pulseController.dispose();
    super.dispose();
  }

  // ── Loaders ─────────────────────────────────────────────────────────────────

  Future<void> _loadDetail() async {
    if (!mounted) return;
    setState(() {
      _isLoadingDetail = true;
      _detailError = null;
    });

    final provider = context.read<CommodityProvider>();
    await provider.loadCommodityDetail(widget.commodityId);

    if (!mounted) return;
    setState(() {
      _isLoadingDetail = false;
      _detailError = provider.errorMessage;
    });
  }

  Future<void> _loadHistory(String period) async {
    if (!mounted) return;
    setState(() {
      _isLoadingHistory = true;
      _historyError = null;
      _selectedPeriod = period;
    });

    final provider = context.read<CommodityProvider>();
    await provider.loadPriceHistory(widget.commodityId, period: period);

    if (!mounted) return;
    setState(() {
      _isLoadingHistory = false;
      _historyError = provider.errorMessage;
    });
  }

  void _startPolling() {
    _pollingTimer = Timer.periodic(_pollInterval, (_) async {
      if (!mounted) return;
      final provider = context.read<CommodityProvider>();
      await provider.loadCommodityDetail(widget.commodityId);
      // Tidak ada setState untuk _lastUpdated karena live badge sudah dihapus
    });
  }

  Future<void> _onRefresh() async {
    await Future.wait([
      _loadDetail(),
      _loadHistory(_selectedPeriod),
    ]);
  }

  // ── Navigate ke tab Prediksi di bottom navbar ────────────────────────────────
  // Pop dengan result supaya UserMainScreen bisa switch tab tanpa full-screen push.
  void _navigateToPrediction(CommodityModel commodity) {
    Navigator.pop(context, {
      'action': 'navigate_tab',
      'tabIndex': 1,
      'initialCommodity': commodity.name,
    });
  }

  // ── Helpers ──────────────────────────────────────────────────────────────────

  Color _categoryColor(String category) {
    const map = <String, Color>{
      'sayur mayur': Color(0xFF4CAF50),
      'bawang': Color(0xFF9C27B0),
      'ikan segar': Color(0xFF2196F3),
      'buah': Color(0xFFFF9800),
      'beras': Color(0xFF795548),
      'daging': Color(0xFFF44336),
      'minyak': Color(0xFFFFAB00),
      'bumbu': Color(0xFFFF5722),
      'telur ayam': Color(0xFFFFB300),
      'gula': Color(0xFF8D6E63),
    };
    final k = category.toLowerCase();
    for (final e in map.entries) {
      if (k.contains(e.key)) return e.value;
    }
    return const Color(0xFF1976D2);
  }

  IconData _categoryIcon(String category) {
    const map = <String, IconData>{
      'sayur mayur': Icons.eco,
      'bawang': Icons.bubble_chart,
      'ikan segar': Icons.set_meal,
      'buah': Icons.apple,
      'beras': Icons.grain,
      'daging': Icons.kebab_dining,
      'minyak': Icons.opacity,
      'bumbu': Icons.spa,
      'telur ayam': Icons.egg,
      'gula': Icons.cake,
    };
    final k = category.toLowerCase();
    for (final e in map.entries) {
      if (k.contains(e.key)) return e.value;
    }
    return Icons.inventory_2;
  }

  // ── Build ────────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Consumer<CommodityProvider>(
      builder: (context, provider, _) {
        final commodity = provider.selectedCommodity;

        // ── Loading awal ───────────────────────────────────────────────────────
        if (_isLoadingDetail && commodity == null) {
          return Scaffold(
            backgroundColor:
                isDark ? const Color(0xFF121212) : const Color(0xFFF5F7FA),
            appBar: AppBar(
              backgroundColor: const Color(0xFF1565C0),
              foregroundColor: Colors.white,
              elevation: 0,
              title: const Text('Detail Komoditas'),
            ),
            body: const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: Color(0xFF1976D2)),
                  SizedBox(height: 16),
                  Text(
                    'Memuat data komoditas...',
                    style: TextStyle(color: Colors.grey),
                  ),
                ],
              ),
            ),
          );
        }

        // ── Error awal ─────────────────────────────────────────────────────────
        if (_detailError != null && commodity == null) {
          return Scaffold(
            backgroundColor:
                isDark ? const Color(0xFF121212) : const Color(0xFFF5F7FA),
            appBar: AppBar(
              backgroundColor: const Color(0xFF1565C0),
              foregroundColor: Colors.white,
              elevation: 0,
              title: const Text('Detail Komoditas'),
            ),
            body: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.cloud_off_rounded,
                        size: 56, color: Colors.grey),
                    const SizedBox(height: 16),
                    Text(
                      _detailError!,
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.grey),
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton.icon(
                      onPressed: _loadDetail,
                      icon: const Icon(Icons.refresh),
                      label: const Text('Coba Lagi'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF1976D2),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        }

        final color = _categoryColor(commodity?.category ?? '');
        final icon = _categoryIcon(commodity?.category ?? '');

        return Scaffold(
          backgroundColor:
              isDark ? const Color(0xFF121212) : const Color(0xFFF5F7FA),
          body: RefreshIndicator(
            onRefresh: _onRefresh,
            color: const Color(0xFF1976D2),
            child: CustomScrollView(
              slivers: [
                _buildSliverAppBar(commodity!, color, icon, isDark),

                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                  sliver: SliverList(
                    delegate: SliverChildListDelegate([
                      // ── REVISI 1: Live badge & jam dihapus ──────────────────
                      _buildPriceCard(commodity, color, isDark),
                      const SizedBox(height: 14),
                      // ── REVISI 3: Tombol prediksi → navigate ────────────────
                      _buildPredictionBanner(commodity, isDark),
                      const SizedBox(height: 14),
                      _buildChartCard(provider, isDark),
                      // ── REVISI 2: Info card dihapus ─────────────────────────
                    ]),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  // ── Sliver App Bar ───────────────────────────────────────────────────────────

  Widget _buildSliverAppBar(
    CommodityModel commodity,
    Color color,
    IconData icon,
    bool isDark,
  ) {
    return SliverAppBar(
      expandedHeight: 160,
      pinned: true,
      backgroundColor: const Color(0xFF1565C0),
      foregroundColor: Colors.white,
      elevation: 0,
      flexibleSpace: FlexibleSpaceBar(
        background: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                const Color(0xFF1565C0),
                color.withValues(alpha: 0.85),
              ],
            ),
          ),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 48, 20, 16),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Icon(icon, color: Colors.white, size: 28),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.end,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          commodity.name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            commodity.category,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ── Price Card ───────────────────────────────────────────────────────────────

  Widget _buildPriceCard(CommodityModel commodity, Color color, bool isDark) {
    final isNaik = commodity.isIncreasing;
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    final selisih = commodity.currentPrice - commodity.previousPrice;

    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: color.withValues(alpha: 0.2),
          width: 0.8,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.06),
            blurRadius: 12,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(
                  'Harga Aktual',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[500],
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const Spacer(),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: isNaik
                        ? const Color(0xFF4CAF50).withValues(alpha: 0.1)
                        : const Color(0xFFF44336).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isNaik
                            ? Icons.arrow_upward_rounded
                            : Icons.arrow_downward_rounded,
                        size: 12,
                        color: isNaik
                            ? const Color(0xFF4CAF50)
                            : const Color(0xFFF44336),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        commodity.changePercentage,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: isNaik
                              ? const Color(0xFF4CAF50)
                              : const Color(0xFFF44336),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              _rupiahFmt.format(commodity.currentPrice),
              style: TextStyle(
                fontSize: 30,
                fontWeight: FontWeight.w800,
                color: color,
                letterSpacing: -0.5,
              ),
            ),
            if (commodity.unit.isNotEmpty)
              Text(
                '/ ${commodity.unit}',
                style: TextStyle(fontSize: 12, color: Colors.grey[400]),
              ),
            const SizedBox(height: 16),
            const Divider(height: 1),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: _buildStatItem(
                    label: 'Harga Sebelumnya',
                    value: _rupiahFmt.format(commodity.previousPrice),
                    icon: Icons.history,
                    color: Colors.grey[600]!,
                    isDark: isDark,
                  ),
                ),
                Container(
                  width: 1,
                  height: 40,
                  color: isDark
                      ? Colors.grey.withValues(alpha: 0.2)
                      : Colors.grey.withValues(alpha: 0.15),
                ),
                Expanded(
                  child: _buildStatItem(
                    label: 'Selisih Harga',
                    value: (selisih >= 0 ? '+' : '') +
                        _rupiahFmt.format(selisih),
                    icon: Icons.swap_vert,
                    color: selisih > 0
                        ? const Color(0xFF4CAF50)
                        : selisih < 0
                            ? const Color(0xFFF44336)
                            : Colors.grey[600]!,
                    isDark: isDark,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatItem({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
    required bool isDark,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8),
      child: Column(
        children: [
          Icon(icon, size: 16, color: color),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: color,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: TextStyle(fontSize: 10, color: Colors.grey[500]),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  // ── REVISI 3: Prediction Banner → hanya navigate ke halaman prediksi ─────────

  Widget _buildPredictionBanner(CommodityModel commodity, bool isDark) {
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;

    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: const Color(0xFF7C3AED).withValues(alpha: 0.25),
          width: 0.8,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.06),
            blurRadius: 12,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => _navigateToPrediction(commodity),
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Row(
              children: [
                // ── Icon kiri ────────────────────────────────────────────────
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF7C3AED).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(
                    Icons.auto_graph_rounded,
                    size: 20,
                    color: Color(0xFF7C3AED),
                  ),
                ),
                const SizedBox(width: 14),

                // ── Teks tengah ──────────────────────────────────────────────
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Prediksi Harga AI',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF7C3AED),
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'Lihat perkiraan harga bulan depan',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey[500],
                        ),
                      ),
                    ],
                  ),
                ),

                // ── Arrow kanan ──────────────────────────────────────────────
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: const Color(0xFF7C3AED).withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.arrow_forward_ios_rounded,
                    size: 14,
                    color: Color(0xFF7C3AED),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ── Chart Card ───────────────────────────────────────────────────────────────

  Widget _buildChartCard(CommodityProvider provider, bool isDark) {
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;

    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.06),
            blurRadius: 12,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(
                  'Grafik Harga Historis',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white : const Color(0xFF1A1A2E),
                  ),
                ),
                const Spacer(),
                if (_isLoadingHistory)
                  const SizedBox(
                    width: 14,
                    height: 14,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Color(0xFF1976D2),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 12),

            // Period selector
            Row(
              children: [
                _buildPeriodChip('7 Hari', '7days', isDark),
                const SizedBox(width: 8),
                _buildPeriodChip('30 Hari', '30days', isDark),
                const SizedBox(width: 8),
                _buildPeriodChip('3 Bulan', '3months', isDark),
              ],
            ),
            const SizedBox(height: 20),

            Padding(
              padding: const EdgeInsets.only(left: 4),
              child: SizedBox(
                height: 220,
                child: _isLoadingHistory
                    ? const Center(
                        child: CircularProgressIndicator(
                          color: Color(0xFF1976D2),
                          strokeWidth: 2,
                        ),
                      )
                    : _historyError != null
                        ? Center(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.bar_chart_outlined,
                                    color: Colors.grey[400], size: 36),
                                const SizedBox(height: 8),
                                Text(
                                  'Gagal memuat grafik',
                                  style: TextStyle(color: Colors.grey[500]),
                                ),
                                TextButton(
                                  onPressed: () =>
                                      _loadHistory(_selectedPeriod),
                                  child: const Text('Coba Lagi'),
                                ),
                              ],
                            ),
                          )
                        : provider.priceHistory.isEmpty
                            ? Center(
                                child: Text(
                                  'Belum ada data historis',
                                  style: TextStyle(color: Colors.grey[500]),
                                ),
                              )
                            : _buildLineChart(provider.priceHistory, isDark),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLineChart(List<PriceModel> history, bool isDark) {
    final spots = history.asMap().entries.map((e) {
      return FlSpot(e.key.toDouble(), e.value.hargaSekarang);
    }).toList();

    final prices = history.map((e) => e.hargaSekarang).toList();
    final minVal = prices.reduce((a, b) => a < b ? a : b);
    final maxVal = prices.reduce((a, b) => a > b ? a : b);
    final padding = (maxVal - minVal) == 0
        ? minVal * 0.1
        : (maxVal - minVal) * 0.25;
    final minY = minVal - padding;
    final maxY = maxVal + padding;

    final yInterval = (maxY - minY) / 3;
    final xInterval =
        ((history.length / 4).ceilToDouble()).clamp(2.0, double.infinity);

    return LineChart(
      LineChartData(
        minY: minY,
        maxY: maxY,
        gridData: FlGridData(
          show: true,
          drawVerticalLine: false,
          getDrawingHorizontalLine: (_) => FlLine(
            color: isDark
                ? Colors.white.withValues(alpha: 0.05)
                : Colors.grey.withValues(alpha: 0.1),
            strokeWidth: 1,
          ),
        ),
        titlesData: FlTitlesData(
          leftTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 78,
              interval: yInterval,
              getTitlesWidget: (value, meta) {
                String label;
                if (value >= 1000000) {
                  label = '${(value / 1000000).toStringAsFixed(1)}jt';
                } else if (value >= 1000) {
                  final rb = value / 1000;
                  label = rb == rb.roundToDouble()
                      ? '${rb.toInt()}rb'
                      : '${rb.toStringAsFixed(1)}rb';
                } else {
                  label = value.toInt().toString();
                }
                return SideTitleWidget(
                  axisSide: meta.axisSide,
                  space: 8,
                  child: Text(
                    label,
                    style: TextStyle(color: Colors.grey[500], fontSize: 9),
                  ),
                );
              },
            ),
          ),
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 32,
              interval: xInterval,
              getTitlesWidget: (value, meta) {
                final idx = value.toInt();
                if (idx < 0 || idx >= history.length) {
                  return const Text('');
                }
                return Padding(
                  padding: const EdgeInsets.only(top: 6),
                  child: Text(
                    DateFormat('dd/MM').format(history[idx].date),
                    style: TextStyle(color: Colors.grey[500], fontSize: 9),
                  ),
                );
              },
            ),
          ),
          rightTitles:
              const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          topTitles:
              const AxisTitles(sideTitles: SideTitles(showTitles: false)),
        ),
        borderData: FlBorderData(show: false),
        lineTouchData: LineTouchData(
          touchTooltipData: LineTouchTooltipData(
            // FIX: tambah getTooltipColor agar tidak warning di fl_chart terbaru
            getTooltipColor: (_) => const Color(0xFF1565C0),
            getTooltipItems: (touchedSpots) => touchedSpots.map((s) {
              final idx = s.x.toInt();
              final date = idx < history.length
                  ? DateFormat('dd MMM').format(history[idx].date)
                  : '';
              return LineTooltipItem(
                '$date\n${_rupiahFmt.format(s.y)}',
                const TextStyle(
                  color: Colors.white,
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              );
            }).toList(),
          ),
        ),
        lineBarsData: [
          LineChartBarData(
            spots: spots,
            isCurved: true,
            curveSmoothness: 0.3,
            color: const Color(0xFF1976D2),
            barWidth: 2.5,
            isStrokeCapRound: true,
            dotData: FlDotData(
              show: true,
              getDotPainter: (spot, percent, bar, index) => FlDotCirclePainter(
                radius: 2.5,
                color: const Color(0xFF1976D2),
                strokeWidth: 1.5,
                strokeColor: Colors.white,
              ),
            ),
            belowBarData: BarAreaData(
              show: true,
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  const Color(0xFF1976D2).withValues(alpha: 0.2),
                  const Color(0xFF1976D2).withValues(alpha: 0.0),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPeriodChip(String label, String value, bool isDark) {
    final isActive = _selectedPeriod == value;
    return GestureDetector(
      onTap: () => _loadHistory(value),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: isActive
              ? const Color(0xFF1976D2)
              : (isDark
                  ? Colors.white.withValues(alpha: 0.08)
                  : Colors.grey[100]),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
            color: isActive
                ? Colors.white
                : (isDark ? Colors.grey[300] : Colors.grey[700]),
          ),
        ),
      ),
    );
  }
}