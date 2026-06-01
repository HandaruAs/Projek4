import 'dart:async';
import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/models/price_model.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
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

  final NumberFormat _rupiahFmt = NumberFormat.currency(
    locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0,
  );

  // ── State ────────────────────────────────────────────────
  bool    _isLoadingDetail  = true;
  bool    _isLoadingHistory = true;
  String? _detailError;
  String? _historyError;
  String  _selectedPeriod   = '7days';

  // ── State forecast ───────────────────────────────────────
  int _selectedForecastDays = 30;
  int _touchedForecastIndex = -1;

  // ── Midnight refresh ────────────────────────────────────
  Timer? _midnightTimer;
  DateTime? _lastForecastDate;

  late AnimationController _pulseController;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this, duration: const Duration(seconds: 2),
    );

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadAllParallel();
      _startMidnightRefresh();
    });
  }

  @override
  void dispose() {
    _midnightTimer?.cancel();
    _pulseController.dispose();
    super.dispose();
  }

  // ── Load semua data secara parallel ─────────────────────
  Future<void> _loadAllParallel() async {
    if (!mounted) return;

    await Future.wait([
      _loadDetail(),
      _loadHistory(_selectedPeriod),
      _loadForecast(),
    ]);
  }

  Future<void> _loadDetail() async {
    if (!mounted) return;
    setState(() { _isLoadingDetail = true; _detailError = null; });

    final provider = context.read<CommodityProvider>();
    await provider.loadCommodityDetail(widget.commodityId);

    if (!mounted) return;
    setState(() {
      _isLoadingDetail = false;
      _detailError     = provider.errorMessage;
    });
  }

  Future<void> _loadHistory(String period) async {
    if (!mounted) return;
    setState(() {
      _isLoadingHistory = true;
      _historyError     = null;
      _selectedPeriod   = period;
    });

    final provider = context.read<CommodityProvider>();
    await provider.loadPriceHistory(widget.commodityId, period: period);

    if (!mounted) return;
    setState(() {
      _isLoadingHistory = false;
      _historyError     = provider.errorMessage;
    });
  }

  // ── Load forecast dari endpoint baru ────────────────────
  Future<void> _loadForecast() async {
    if (!mounted) return;
    final provider = context.read<CommodityProvider>();
    await provider.loadForecast(widget.commodityId);

    if (!mounted) return;
    final forecast = provider.forecast;

    // Set default period ke period terkecil yang tersedia
    if (forecast.hasForecast && forecast.availablePeriods.isNotEmpty) {
      setState(() {
        _selectedForecastDays = forecast.availablePeriods.first;
        _lastForecastDate     = DateTime.now();
      });
    }
  }

  // ── Refresh otomatis saat ganti hari ────────────────────
  void _startMidnightRefresh() {
    _midnightTimer = Timer.periodic(const Duration(minutes: 1), (_) async {
      if (!mounted) return;
      final now = DateTime.now();
      if (_lastForecastDate != null && now.day != _lastForecastDate!.day) {
        await _loadForecast();
      }
    });
  }

  Future<void> _onRefresh() async {
    // Reset forecast state supaya rebuild
    final provider = context.read<CommodityProvider>();
    provider.clearSelectedCommodity();

    await Future.wait([
      _loadDetail(),
      _loadHistory(_selectedPeriod),
      _loadForecast(),
    ]);
  }

  void _navigateToPrediction(CommodityModel commodity) {
    Navigator.pop(context, {
      'action':            'navigate_tab',
      'tabIndex':          1,
      'initialCommodity':  commodity.name,
    });
  }

  // ── Helpers ──────────────────────────────────────────────
  Color _categoryColor(String category) {
    const map = <String, Color>{
      'sayur mayur': Color(0xFF4CAF50),
      'bawang':      Color(0xFF9C27B0),
      'ikan segar':  Color(0xFF2196F3),
      'buah':        Color(0xFFFF9800),
      'beras':       Color(0xFF795548),
      'daging':      Color(0xFFF44336),
      'minyak':      Color(0xFFFFAB00),
      'bumbu':       Color(0xFFFF5722),
      'telur ayam':  Color(0xFFFFB300),
      'gula':        Color(0xFF8D6E63),
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
      'bawang':      Icons.bubble_chart,
      'ikan segar':  Icons.set_meal,
      'buah':        Icons.apple,
      'beras':       Icons.grain,
      'daging':      Icons.kebab_dining,
      'minyak':      Icons.opacity,
      'bumbu':       Icons.spa,
      'telur ayam':  Icons.egg,
      'gula':        Icons.cake,
    };
    final k = category.toLowerCase();
    for (final e in map.entries) {
      if (k.contains(e.key)) return e.value;
    }
    return Icons.inventory_2;
  }

  String _fmtShort(double val) {
    if (val >= 1000000) return '${(val / 1000000).toStringAsFixed(1)}jt';
    if (val >= 1000)    return '${(val / 1000).toStringAsFixed(0)}rb';
    return val.toStringAsFixed(0);
  }

  // ── Build ─────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Consumer<CommodityProvider>(
      builder: (context, provider, _) {
        final commodity = provider.selectedCommodity;

        // ── Loading awal ───────────────────────────────────
        if (_isLoadingDetail && commodity == null) {
          return Scaffold(
            backgroundColor: isDark
                ? const Color(0xFF121212) : const Color(0xFFF5F7FA),
            appBar: AppBar(
              backgroundColor: const Color(0xFF1565C0),
              foregroundColor: Colors.white,
              elevation: 0,
              title: const Text('Detail Komoditas'),
            ),
            body: const Center(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                CircularProgressIndicator(color: Color(0xFF1976D2)),
                SizedBox(height: 16),
                Text('Memuat data komoditas...',
                    style: TextStyle(color: Colors.grey)),
              ]),
            ),
          );
        }

        // ── Error awal ─────────────────────────────────────
        if (_detailError != null && commodity == null) {
          return Scaffold(
            backgroundColor: isDark
                ? const Color(0xFF121212) : const Color(0xFFF5F7FA),
            appBar: AppBar(
              backgroundColor: const Color(0xFF1565C0),
              foregroundColor: Colors.white,
              elevation: 0,
              title: const Text('Detail Komoditas'),
            ),
            body: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(Icons.cloud_off_rounded,
                      size: 56, color: Colors.grey),
                  const SizedBox(height: 16),
                  Text(_detailError!, textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.grey)),
                  const SizedBox(height: 24),
                  ElevatedButton.icon(
                    onPressed: _loadDetail,
                    icon: const Icon(Icons.refresh),
                    label: const Text('Coba Lagi'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1976D2),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ]),
              ),
            ),
          );
        }

        final color = _categoryColor(commodity?.category ?? '');
        final icon  = _categoryIcon(commodity?.category ?? '');

        return Scaffold(
          backgroundColor: isDark
              ? const Color(0xFF121212) : const Color(0xFFF5F7FA),
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
                      _buildPriceCard(commodity, color, isDark),
                      const SizedBox(height: 14),
                      _buildPredictionBanner(commodity, isDark),
                      const SizedBox(height: 14),
                      _buildForecastCard(provider, isDark),
                      const SizedBox(height: 14),
                      _buildChartCard(provider, isDark),
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

  // ── Sliver App Bar ────────────────────────────────────────

  Widget _buildSliverAppBar(
      CommodityModel commodity, Color color, IconData icon, bool isDark) {
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
                        Text(commodity.name,
                            style: const TextStyle(color: Colors.white,
                                fontSize: 20, fontWeight: FontWeight.w700)),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(commodity.category,
                              style: const TextStyle(
                                  color: Colors.white, fontSize: 11)),
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

  // ── Price Card ────────────────────────────────────────────

  Widget _buildPriceCard(
      CommodityModel commodity, Color color, bool isDark) {
    final isNaik  = commodity.isIncreasing;
    final cardBg  = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    final selisih = commodity.currentPrice - commodity.previousPrice;

    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
            color: color.withValues(alpha: 0.2), width: 0.8),
        boxShadow: [BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.06),
            blurRadius: 12, offset: const Offset(0, 3))],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Text('Harga Aktual',
                style: TextStyle(fontSize: 12, color: Colors.grey[500],
                    fontWeight: FontWeight.w500)),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(
                  horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: isNaik
                    ? const Color(0xFF4CAF50).withValues(alpha: 0.1)
                    : const Color(0xFFF44336).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Row(mainAxisSize: MainAxisSize.min, children: [
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
                Text(commodity.changePercentage,
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600,
                        color: isNaik
                            ? const Color(0xFF4CAF50)
                            : const Color(0xFFF44336))),
              ]),
            ),
          ]),
          const SizedBox(height: 6),
          Text(_rupiahFmt.format(commodity.currentPrice),
              style: TextStyle(fontSize: 30, fontWeight: FontWeight.w800,
                  color: color, letterSpacing: -0.5)),
          if (commodity.unit.isNotEmpty)
            Text('/ ${commodity.unit}',
                style: TextStyle(fontSize: 12, color: Colors.grey[400])),
          const SizedBox(height: 16),
          const Divider(height: 1),
          const SizedBox(height: 14),
          Row(children: [
            Expanded(child: _buildStatItem(
                label: 'Harga Sebelumnya',
                value: _rupiahFmt.format(commodity.previousPrice),
                icon: Icons.history,
                color: Colors.grey[600]!, isDark: isDark)),
            Container(width: 1, height: 40,
                color: isDark
                    ? Colors.grey.withValues(alpha: 0.2)
                    : Colors.grey.withValues(alpha: 0.15)),
            Expanded(child: _buildStatItem(
                label: 'Selisih Harga',
                value: (selisih >= 0 ? '+' : '') +
                    _rupiahFmt.format(selisih),
                icon: Icons.swap_vert,
                color: selisih > 0
                    ? const Color(0xFF4CAF50)
                    : selisih < 0
                        ? const Color(0xFFF44336)
                        : Colors.grey[600]!,
                isDark: isDark)),
          ]),
        ]),
      ),
    );
  }

  Widget _buildStatItem({
    required String label, required String value,
    required IconData icon, required Color color, required bool isDark,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8),
      child: Column(children: [
        Icon(icon, size: 16, color: color),
        const SizedBox(height: 4),
        Text(value, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700,
            color: color), textAlign: TextAlign.center),
        const SizedBox(height: 2),
        Text(label, style: TextStyle(fontSize: 10, color: Colors.grey[500]),
            textAlign: TextAlign.center),
      ]),
    );
  }

  // ── Prediction Banner (ke tab prediksi) ──────────────────

  Widget _buildPredictionBanner(CommodityModel commodity, bool isDark) {
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
            color: const Color(0xFF7C3AED).withValues(alpha: 0.25),
            width: 0.8),
        boxShadow: [BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.06),
            blurRadius: 12, offset: const Offset(0, 3))],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => _navigateToPrediction(commodity),
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Row(children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                    color: const Color(0xFF7C3AED).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10)),
                child: const Icon(Icons.auto_graph_rounded,
                    size: 20, color: Color(0xFF7C3AED)),
              ),
              const SizedBox(width: 14),
              Expanded(child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Prediksi Harga AI',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700,
                          color: Color(0xFF7C3AED))),
                  const SizedBox(height: 3),
                  Text('Lihat perkiraan harga lengkap',
                      style: TextStyle(fontSize: 11, color: Colors.grey[500])),
                ],
              )),
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                    color: const Color(0xFF7C3AED).withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(8)),
                child: const Icon(Icons.arrow_forward_ios_rounded,
                    size: 14, color: Color(0xFF7C3AED)),
              ),
            ]),
          ),
        ),
      ),
    );
  }

  // ── Forecast Card ─────────────────────────────────────────
  // Pakai CommodityForecastModel dari provider

  Widget _buildForecastCard(CommodityProvider provider, bool isDark) {
    final cardBg  = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    final forecast = provider.forecast;
    final isLoading = provider.isLoadingForecast;
    final error     = provider.forecastError;

    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.06),
            blurRadius: 12, offset: const Offset(0, 3))],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

          // ── Header ─────────────────────────────────────────
          Row(children: [
            Container(
              padding: const EdgeInsets.all(7),
              decoration: BoxDecoration(
                  color: const Color(0xFF7C3AED).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(9)),
              child: const Icon(Icons.query_stats_rounded,
                  size: 16, color: Color(0xFF7C3AED)),
            ),
            const SizedBox(width: 10),
            Text('Prediksi Harga ke Depan',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white : const Color(0xFF1A1A2E))),
            const Spacer(),
            if (isLoading)
              const SizedBox(width: 14, height: 14,
                  child: CircularProgressIndicator(
                      strokeWidth: 2, color: Color(0xFF7C3AED))),
            // Badge status prediksi
            if (!isLoading && forecast.hasForecast)
              _buildStatusBadge(forecast),
          ]),
          const SizedBox(height: 14),

          // ── Content ────────────────────────────────────────
          if (isLoading)
            const SizedBox(height: 120,
                child: Center(child: CircularProgressIndicator(
                    color: Color(0xFF7C3AED), strokeWidth: 2)))
          else if (error != null)
            _buildForecastError(error, isDark)
          else if (!forecast.hasForecast)
            _buildForecastEmpty(isDark)
          else ...[
            // Period tabs — dinamis sesuai available_periods dari API
            _buildForecastPeriodTabs(forecast, isDark),
            const SizedBox(height: 16),
            _buildForecastSummaryRow(forecast, isDark),
            const SizedBox(height: 16),
            _buildForecastChart(forecast, isDark),
            const SizedBox(height: 14),
            _buildForecastTable(forecast, isDark),
            if (forecast.accuracy != null)
              _buildAccuracyRow(forecast, isDark),
          ],
        ]),
      ),
    );
  }

  Widget _buildStatusBadge(CommodityForecastModel forecast) {
    final Color bgColor;
    final Color textColor;
    final String label;
    final IconData icon;

    switch (forecast.statusPrediksi) {
      case 'aktif':
        bgColor   = const Color(0xFFDCFCE7);
        textColor = const Color(0xFF166534);
        label     = 'Aktif';
        icon      = Icons.check_circle_rounded;
        break;
      case 'kadaluarsa':
        bgColor   = const Color(0xFFFEF3C7);
        textColor = const Color(0xFF92400E);
        label     = 'Kadaluarsa';
        icon      = Icons.access_time_rounded;
        break;
      case 'belum_mulai':
        bgColor   = const Color(0xFFEFF6FF);
        textColor = const Color(0xFF1D4ED8);
        label     = 'Segera';
        icon      = Icons.calendar_month_rounded;
        break;
      default:
        return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
          color: bgColor, borderRadius: BorderRadius.circular(20)),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 10, color: textColor),
        const SizedBox(width: 4),
        Text(label, style: TextStyle(fontSize: 10,
            fontWeight: FontWeight.w700, color: textColor)),
      ]),
    );
  }

  // ── Period tabs — sesuai available_periods dari API ───────

  Widget _buildForecastPeriodTabs(
      CommodityForecastModel forecast, bool isDark) {
    final periods = forecast.availablePeriods;
    if (periods.isEmpty) return const SizedBox.shrink();

    return Wrap(
      spacing: 8,
      children: periods.map((days) {
        final isActive = _selectedForecastDays == days;
        final label    = days < 30
            ? '$days Hari'
            : days == 30 ? '1 Bulan'
            : days == 60 ? '2 Bulan'
            : days == 90 ? '3 Bulan'
            : '$days Hari';

        return GestureDetector(
          onTap: () => setState(() {
            _selectedForecastDays = days;
            _touchedForecastIndex = -1;
          }),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 7),
            decoration: BoxDecoration(
              color: isActive
                  ? const Color(0xFF7C3AED)
                  : (isDark
                      ? Colors.white.withValues(alpha: 0.08)
                      : Colors.grey[100]),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(label, style: TextStyle(
              fontSize: 12,
              fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
              color: isActive
                  ? Colors.white
                  : (isDark ? Colors.grey[300] : Colors.grey[700]),
            )),
          ),
        );
      }).toList(),
    );
  }

  // ── Summary row ───────────────────────────────────────────

  Widget _buildForecastSummaryRow(
      CommodityForecastModel forecast, bool isDark) {
    final points   = forecast.forecastForPeriod(_selectedForecastDays);
    if (points.isEmpty) return const SizedBox.shrink();

    final hargaAktual = forecast.hargaAktual;
    final estimasi    = points.last.harga;
    final tren        = hargaAktual > 0
        ? double.parse(
            ((estimasi - hargaAktual) / hargaAktual * 100)
                .toStringAsFixed(1))
        : 0.0;
    final isNaik = tren >= 0;

    final periodLabel = _selectedForecastDays < 30
        ? '$_selectedForecastDays HARI'
        : _selectedForecastDays == 30 ? '1 BULAN'
        : _selectedForecastDays == 60 ? '2 BULAN'
        : '3 BULAN';

    return Row(children: [
      Expanded(child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [
              const Color(0xFF7C3AED),
              const Color(0xFF7C3AED).withValues(alpha: 0.75),
            ],
            begin: Alignment.topLeft, end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('ESTIMASI $periodLabel',
              style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700,
                  letterSpacing: 0.6,
                  color: Colors.white.withValues(alpha: 0.75))),
          const SizedBox(height: 6),
          Text(_rupiahFmt.format(estimasi),
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800,
                  color: Colors.white, letterSpacing: -0.5)),
          const SizedBox(height: 4),
          Text('Saat ini: ${_rupiahFmt.format(forecast.hargaHariIni)}',
              style: TextStyle(fontSize: 10,
                  color: Colors.white.withValues(alpha: 0.75))),
        ]),
      )),
      const SizedBox(width: 10),
      Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: (isNaik
              ? const Color(0xFFF44336)
              : const Color(0xFF10B981))
              .withValues(alpha: isDark ? 0.15 : 0.08),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: (isNaik
                ? const Color(0xFFF44336)
                : const Color(0xFF10B981))
                .withValues(alpha: 0.3),
          ),
        ),
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Icon(
            isNaik ? Icons.trending_up_rounded : Icons.trending_down_rounded,
            color: isNaik
                ? const Color(0xFFF44336) : const Color(0xFF10B981),
            size: 26,
          ),
          const SizedBox(height: 6),
          Text('${isNaik ? '+' : ''}$tren%',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800,
                  color: isNaik
                      ? const Color(0xFFF44336) : const Color(0xFF10B981))),
          Text('tren',
              style: TextStyle(fontSize: 10, color: Colors.grey[500])),
        ]),
      ),
    ]);
  }

  // ── Forecast Line Chart ───────────────────────────────────

  Widget _buildForecastChart(
      CommodityForecastModel forecast, bool isDark) {
    final points = forecast.forecastForPeriod(_selectedForecastDays);
    if (points.isEmpty) return const SizedBox.shrink();

    final prices    = points.map((p) => p.harga).toList();
    final minVal    = prices.reduce((a, b) => a < b ? a : b);
    final maxVal    = prices.reduce((a, b) => a > b ? a : b);
    final range     = maxVal - minVal;
    final pad       = range == 0 ? minVal * 0.05 : range * 0.2;
    final minY      = (minVal - pad).clamp(0, double.infinity).toDouble();
    final maxY      = maxVal + pad;

    final hargaAktual = forecast.hargaAktual;
    final estimasi    = points.last.harga;
    final isNaik      = estimasi >= hargaAktual;
    final lineColor   = isNaik
        ? const Color(0xFFF44336) : const Color(0xFF10B981);

    final spots = points.asMap().entries
        .map((e) => FlSpot(e.key.toDouble(), e.value.harga))
        .toList();

    final xInterval =
        ((points.length / 4).ceilToDouble()).clamp(2.0, double.infinity);

    return RepaintBoundary(
      child: SizedBox(
        height: 200,
        child: LineChart(
          LineChartData(
        minY: minY, maxY: maxY,
        gridData: FlGridData(
          show: true, drawVerticalLine: false,
          getDrawingHorizontalLine: (_) => FlLine(
            color: isDark
                ? Colors.white.withValues(alpha: 0.05)
                : Colors.grey.withValues(alpha: 0.1),
            strokeWidth: 1,
          ),
        ),
        titlesData: FlTitlesData(
          leftTitles: AxisTitles(sideTitles: SideTitles(
            showTitles: true, reservedSize: 72,
            interval: range > 0 ? range / 3 : 1,
            getTitlesWidget: (value, meta) => SideTitleWidget(
              axisSide: meta.axisSide, space: 8,
              child: Text(_fmtShort(value),
                  style: TextStyle(color: Colors.grey[500], fontSize: 9)),
            ),
          )),
          bottomTitles: AxisTitles(sideTitles: SideTitles(
            showTitles: true, reservedSize: 32, interval: xInterval,
            getTitlesWidget: (value, meta) {
              final idx = value.toInt();
              if (idx < 0 || idx >= points.length) return const Text('');
              return Padding(
                padding: const EdgeInsets.only(top: 6),
                child: Text(
                  DateFormat('dd/MM').format(points[idx].date),
                  style: TextStyle(color: Colors.grey[500], fontSize: 9),
                ),
              );
            },
          )),
          rightTitles: const AxisTitles(
              sideTitles: SideTitles(showTitles: false)),
          topTitles: const AxisTitles(
              sideTitles: SideTitles(showTitles: false)),
        ),
        borderData: FlBorderData(show: false),
        // Garis putus-putus harga saat ini sebagai referensi
        extraLinesData: ExtraLinesData(horizontalLines: [
          HorizontalLine(
            y: hargaAktual,
            color: Colors.grey.withValues(alpha: 0.4),
            strokeWidth: 1,
            dashArray: [5, 5],
            label: HorizontalLineLabel(
              show: true,
              alignment: Alignment.topRight,
              labelResolver: (_) => 'Saat ini',
              style: TextStyle(fontSize: 9, color: Colors.grey[500]),
            ),
          ),
        ]),
        lineTouchData: LineTouchData(
          touchCallback: (event, response) {
            setState(() {
              if (response?.lineBarSpots == null ||
                  response!.lineBarSpots!.isEmpty) {
                _touchedForecastIndex = -1;
              } else {
                _touchedForecastIndex =
                    response.lineBarSpots!.first.spotIndex;
              }
            });
          },
          touchTooltipData: LineTouchTooltipData(
            getTooltipColor: (_) => const Color(0xFF7C3AED),
            getTooltipItems: (spots) => spots.map((s) {
              final idx = s.x.toInt();
              final date = idx < points.length
                  ? DateFormat('dd MMM').format(points[idx].date)
                  : '';
              return LineTooltipItem(
                '$date\n${_rupiahFmt.format(s.y)}',
                const TextStyle(color: Colors.white, fontSize: 11,
                    fontWeight: FontWeight.w600),
              );
            }).toList(),
          ),
        ),
        lineBarsData: [
          LineChartBarData(
            spots: spots,
            isCurved: false, // FIX: straight line jauh lebih ringan dari curved
            color: lineColor,
            barWidth: 2,
            isStrokeCapRound: true,
            // FIX: hanya render dot saat di-touch, hemat GPU
            dotData: FlDotData(
              show: _touchedForecastIndex >= 0,
              getDotPainter: (spot, percent, bar, index) {
                if (index != _touchedForecastIndex) {
                  return FlDotCirclePainter(
                    radius: 0, color: Colors.transparent,
                    strokeWidth: 0, strokeColor: Colors.transparent,
                  );
                }
                return FlDotCirclePainter(
                  radius: 5,
                  color: lineColor,
                  strokeWidth: 2,
                  strokeColor: Colors.white,
                );
              },
            ),
            belowBarData: BarAreaData(
              show: true,
              gradient: LinearGradient(
                begin: Alignment.topCenter, end: Alignment.bottomCenter,
                colors: [
                  lineColor.withValues(alpha: 0.18),
                  lineColor.withValues(alpha: 0.0),
                ],
              ),
            ),
          ),
          ],
        ),
      ),
    ),
    );
  }

  // ── Forecast Table (ringkasan mingguan) ───────────────────

  Widget _buildForecastTable(
      CommodityForecastModel forecast, bool isDark) {
    final points      = forecast.forecastForPeriod(_selectedForecastDays);
    final hargaAktual = forecast.hargaAktual;
    if (points.isEmpty) return const SizedBox.shrink();

    // Buat ringkasan per minggu
    final weeks = <Map<String, dynamic>>[];
    for (int i = 0; i < points.length; i += 7) {
      final end   = (i + 7 < points.length) ? i + 7 : points.length;
      final slice = points.sublist(i, end);
      final avg   = slice.map((p) => p.harga).reduce((a, b) => a + b) /
          slice.length;
      final delta = hargaAktual > 0
          ? double.parse(
              ((avg - hargaAktual) / hargaAktual * 100).toStringAsFixed(1))
          : 0.0;
      weeks.add({
        'label': 'W${weeks.length + 1}',
        'start': slice.first.date,
        'end':   slice.last.date,
        'avg':   avg,
        'delta': delta,
      });
    }

    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text('Ringkasan Mingguan',
          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
              color: Colors.grey[500])),
      const SizedBox(height: 8),

      // Header tabel
      Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isDark
              ? Colors.white.withValues(alpha: 0.04)
              : const Color(0xFFF8FAFC),
          borderRadius:
              const BorderRadius.vertical(top: Radius.circular(10)),
        ),
        child: Row(children: [
          _tblHeader('MINGGU', flex: 1, isDark: isDark),
          _tblHeader('PERIODE', flex: 3, isDark: isDark),
          _tblHeader('RATA-RATA', flex: 2, isDark: isDark, right: true),
          _tblHeader('TREN', flex: 2, isDark: isDark, right: true),
        ]),
      ),

      ...weeks.asMap().entries.map((entry) {
        final i      = entry.key;
        final w      = entry.value;
        final isUp   = (w['delta'] as double) >= 0;
        final isLast = i == weeks.length - 1;

        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
          decoration: BoxDecoration(
            color: isDark
                ? Colors.white.withValues(alpha: 0.02)
                : Colors.white,
            border: Border(
              bottom: isLast
                  ? BorderSide.none
                  : BorderSide(
                      color: isDark
                          ? Colors.white.withValues(alpha: 0.05)
                          : const Color(0xFFF1F5F9)),
              left: BorderSide(
                  color: isDark
                      ? Colors.white.withValues(alpha: 0.05)
                      : const Color(0xFFE2E8F0)),
              right: BorderSide(
                  color: isDark
                      ? Colors.white.withValues(alpha: 0.05)
                      : const Color(0xFFE2E8F0)),
            ),
            borderRadius: isLast
                ? const BorderRadius.vertical(bottom: Radius.circular(10))
                : BorderRadius.zero,
          ),
          child: Row(children: [
            Expanded(flex: 1, child: Text(w['label'],
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700,
                    color: isDark
                        ? Colors.white : const Color(0xFF1A1A2E)))),
            Expanded(flex: 3, child: Text(
              '${DateFormat('dd/MM').format(w['start'])} – '
              '${DateFormat('dd/MM').format(w['end'])}',
              style: TextStyle(fontSize: 10, color: Colors.grey[500]),
            )),
            Expanded(flex: 2, child: Text(
              _rupiahFmt.format(w['avg']),
              textAlign: TextAlign.right,
              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
                  color: isDark ? Colors.white : const Color(0xFF1A1A2E)),
            )),
            Expanded(flex: 2, child: Align(
              alignment: Alignment.centerRight,
              child: Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: (isUp
                      ? const Color(0xFFF44336)
                      : const Color(0xFF10B981))
                      .withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${isUp ? '+' : ''}${w['delta']}%',
                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700,
                      color: isUp
                          ? const Color(0xFFF44336)
                          : const Color(0xFF10B981)),
                ),
              ),
            )),
          ]),
        );
      }),
    ]);
  }

  Widget _tblHeader(String label,
      {required int flex, required bool isDark, bool right = false}) {
    return Expanded(
      flex: flex,
      child: Text(label,
          textAlign: right ? TextAlign.right : TextAlign.left,
          style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700,
              letterSpacing: 0.4, color: Colors.grey[500])),
    );
  }

  Widget _buildAccuracyRow(
      CommodityForecastModel forecast, bool isDark) {
    final acc  = forecast.accuracy;
    final mape = acc?['mape'];
    final accVal = acc?['accuracy'];

    return Padding(
      padding: const EdgeInsets.only(top: 12),
      child: Row(children: [
        const Icon(Icons.verified_rounded,
            size: 13, color: Color(0xFFF59E0B)),
        const SizedBox(width: 6),
        if (accVal != null)
          Text('Akurasi: ${(accVal as num).toStringAsFixed(1)}%',
              style: const TextStyle(fontSize: 11,
                  color: Color(0xFFF59E0B), fontWeight: FontWeight.w600)),
        if (accVal != null && mape != null)
          Text('  ·  ',
              style: TextStyle(color: Colors.grey[400])),
        if (mape != null)
          Text('MAPE: ${(mape as num).toStringAsFixed(2)}%',
              style: TextStyle(fontSize: 11, color: Colors.grey[500])),
        if (forecast.generatedAt != null) ...[
          const Spacer(),
          Text('Update: ${forecast.generatedAt}',
              style: TextStyle(fontSize: 10, color: Colors.grey[400])),
        ],
      ]),
    );
  }

  Widget _buildForecastError(String error, bool isDark) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFF44336).withValues(alpha: 0.07),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
            color: const Color(0xFFF44336).withValues(alpha: 0.2)),
      ),
      child: Row(children: [
        const Icon(Icons.info_outline_rounded,
            size: 16, color: Color(0xFFF44336)),
        const SizedBox(width: 10),
        Expanded(child: Text(error,
            style: const TextStyle(
                fontSize: 12, color: Color(0xFFF44336)))),
        TextButton(
          onPressed: _loadForecast,
          child: const Text('Coba Lagi',
              style: TextStyle(fontSize: 11, color: Color(0xFF7C3AED))),
        ),
      ]),
    );
  }

  Widget _buildForecastEmpty(bool isDark) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 24),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Icon(Icons.show_chart_rounded, size: 36, color: Colors.grey[400]),
          const SizedBox(height: 8),
          Text('Prediksi belum tersedia untuk komoditas ini',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 12, color: Colors.grey[500])),
        ]),
      ),
    );
  }

  // ── Chart Card (historis) ─────────────────────────────────

  Widget _buildChartCard(CommodityProvider provider, bool isDark) {
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.06),
            blurRadius: 12, offset: const Offset(0, 3))],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Text('Grafik Harga Historis',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600,
                    color: isDark
                        ? Colors.white : const Color(0xFF1A1A2E))),
            const Spacer(),
            if (_isLoadingHistory)
              const SizedBox(width: 14, height: 14,
                  child: CircularProgressIndicator(
                      strokeWidth: 2, color: Color(0xFF1976D2))),
          ]),
          const SizedBox(height: 12),
          Row(children: [
            _buildPeriodChip('7 Hari',  '7days',   isDark),
            const SizedBox(width: 8),
            _buildPeriodChip('30 Hari', '30days',  isDark),
            const SizedBox(width: 8),
            _buildPeriodChip('3 Bulan', '3months', isDark),
          ]),
          const SizedBox(height: 20),
          Padding(
            padding: const EdgeInsets.only(left: 4),
            child: SizedBox(
              height: 220,
              child: _isLoadingHistory
                  ? const Center(child: CircularProgressIndicator(
                      color: Color(0xFF1976D2), strokeWidth: 2))
                  : _historyError != null
                      ? Center(child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.bar_chart_outlined,
                                color: Colors.grey[400], size: 36),
                            const SizedBox(height: 8),
                            Text('Gagal memuat grafik',
                                style: TextStyle(color: Colors.grey[500])),
                            TextButton(
                              onPressed: () =>
                                  _loadHistory(_selectedPeriod),
                              child: const Text('Coba Lagi'),
                            ),
                          ],
                        ))
                      : provider.priceHistory.isEmpty
                          ? Center(child: Text('Belum ada data historis',
                              style: TextStyle(color: Colors.grey[500])))
                          : _buildLineChart(
                              provider.priceHistory, isDark),
            ),
          ),
        ]),
      ),
    );
  }

  Widget _buildLineChart(List<PriceModel> history, bool isDark) {
    final spots = history.asMap().entries
        .map((e) => FlSpot(e.key.toDouble(), e.value.hargaSekarang))
        .toList();

    final prices    = history.map((e) => e.hargaSekarang).toList();
    final minVal    = prices.reduce((a, b) => a < b ? a : b);
    final maxVal    = prices.reduce((a, b) => a > b ? a : b);
    final padding   = (maxVal - minVal) == 0
        ? minVal * 0.1 : (maxVal - minVal) * 0.25;
    final minY      = minVal - padding;
    final maxY      = maxVal + padding;
    final yInterval = (maxY - minY) / 3;
    final xInterval =
        ((history.length / 4).ceilToDouble()).clamp(2.0, double.infinity);

    return LineChart(LineChartData(
      minY: minY, maxY: maxY,
      gridData: FlGridData(
        show: true, drawVerticalLine: false,
        getDrawingHorizontalLine: (_) => FlLine(
          color: isDark
              ? Colors.white.withValues(alpha: 0.05)
              : Colors.grey.withValues(alpha: 0.1),
          strokeWidth: 1,
        ),
      ),
      titlesData: FlTitlesData(
        leftTitles: AxisTitles(sideTitles: SideTitles(
          showTitles: true, reservedSize: 78, interval: yInterval,
          getTitlesWidget: (value, meta) {
            String label;
            if (value >= 1000000) {
              label = '${(value / 1000000).toStringAsFixed(1)}jt';
            }
            else if (value >= 1000) {
              final rb = value / 1000;
              label = rb == rb.roundToDouble()
                  ? '${rb.toInt()}rb'
                  : '${rb.toStringAsFixed(1)}rb';
            } else {
              label = value.toInt().toString();
            }
            return SideTitleWidget(axisSide: meta.axisSide, space: 8,
                child: Text(label, style: TextStyle(
                    color: Colors.grey[500], fontSize: 9)));
          },
        )),
        bottomTitles: AxisTitles(sideTitles: SideTitles(
          showTitles: true, reservedSize: 32, interval: xInterval,
          getTitlesWidget: (value, meta) {
            final idx = value.toInt();
            if (idx < 0 || idx >= history.length) return const Text('');
            return Padding(
              padding: const EdgeInsets.only(top: 6),
              child: Text(
                DateFormat('dd/MM').format(history[idx].date),
                style: TextStyle(color: Colors.grey[500], fontSize: 9),
              ),
            );
          },
        )),
        rightTitles: const AxisTitles(
            sideTitles: SideTitles(showTitles: false)),
        topTitles: const AxisTitles(
            sideTitles: SideTitles(showTitles: false)),
      ),
      borderData: FlBorderData(show: false),
      lineTouchData: LineTouchData(
        touchTooltipData: LineTouchTooltipData(
          getTooltipColor: (_) => const Color(0xFF1565C0),
          getTooltipItems: (spots) => spots.map((s) {
            final idx  = s.x.toInt();
            final date = idx < history.length
                ? DateFormat('dd MMM').format(history[idx].date) : '';
            return LineTooltipItem('$date\n${_rupiahFmt.format(s.y)}',
                const TextStyle(color: Colors.white, fontSize: 11,
                    fontWeight: FontWeight.w600));
          }).toList(),
        ),
      ),
      lineBarsData: [
        LineChartBarData(
          spots: spots,
          isCurved: false, // FIX: straight line lebih ringan
          color: const Color(0xFF1976D2), barWidth: 2.5,
          isStrokeCapRound: true,
          // FIX: sembunyikan semua dot di history chart — 90 dots sangat berat
          dotData: const FlDotData(show: false),
          belowBarData: BarAreaData(show: true,
              gradient: LinearGradient(
                begin: Alignment.topCenter, end: Alignment.bottomCenter,
                colors: [
                  const Color(0xFF1976D2).withValues(alpha: 0.2),
                  const Color(0xFF1976D2).withValues(alpha: 0.0),
                ],
              )),
        ),
      ],
    ));
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
        child: Text(label, style: TextStyle(
          fontSize: 11,
          fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
          color: isActive
              ? Colors.white
              : (isDark ? Colors.grey[300] : Colors.grey[700]),
        )),
      ),
    );
  }
}