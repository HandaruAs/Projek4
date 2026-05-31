import 'dart:async';
import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/models/price_model.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/services/api_service.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

// ── Model prediksi lokal ─────────────────────────────────────────────────────
class _PredPoint {
  final DateTime date;
  final double price;
  _PredPoint(this.date, this.price);
}

class _PredResult {
  final List<_PredPoint> points;
  final double hargaTerakhir;
  final int totalDays;
  final double? accuracy;
  final double? mape;
  final DateTime cachedAt;

  _PredResult({
    required this.points,
    required this.hargaTerakhir,
    required this.totalDays,
    this.accuracy,
    this.mape,
    required this.cachedAt,
  });

  // Ambil slice sesuai period yang dipilih (30/60/90)
  List<_PredPoint> slice(int days) =>
      points.length <= days ? points : points.sublist(0, days);

  double estimasiAkhir(int days) {
    final s = slice(days);
    return s.isEmpty ? hargaTerakhir : s.last.price;
  }

  double trenPersen(int days) {
    final est = estimasiAkhir(days);
    if (hargaTerakhir <= 0) return 0;
    return double.parse(
        ((est - hargaTerakhir) / hargaTerakhir * 100).toStringAsFixed(1));
  }
}

class CommodityDetailScreen extends StatefulWidget {
  final String commodityId;
  const CommodityDetailScreen({super.key, required this.commodityId});

  @override
  State<CommodityDetailScreen> createState() => _CommodityDetailScreenState();
}

class _CommodityDetailScreenState extends State<CommodityDetailScreen>
    with SingleTickerProviderStateMixin {

  final NumberFormat _rupiahFmt = NumberFormat.currency(
    locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0,
  );

  // ── State utama ──────────────────────────────────────────────────────────────
  bool _isLoadingDetail  = true;
  bool _isLoadingHistory = true;
  bool _isLoadingPred    = false;
  String? _detailError;
  String? _historyError;
  String? _predError;
  String _selectedPeriod = '7days';

  // ── State prediksi ───────────────────────────────────────────────────────────
  _PredResult? _predResult;
  int _selectedPredDays = 30;   // 30 | 60 | 90
  int _touchedPredIndex = -1;

  // ── Polling & refresh ────────────────────────────────────────────────────────
  Timer? _pollingTimer;
  // Refresh data tiap tengah malam (dicheck tiap menit)
  Timer? _midnightTimer;
  DateTime? _lastPredDate;

  late AnimationController _pulseController;

  final _api = ApiService();

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this, duration: const Duration(seconds: 2),
    );
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadDetail();
      _loadHistory(_selectedPeriod);
      _startPolling();
      _startMidnightRefresh();
    });
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _midnightTimer?.cancel();
    _pulseController.dispose();
    super.dispose();
  }

  // ── Loaders ──────────────────────────────────────────────────────────────────

  Future<void> _loadDetail() async {
    if (!mounted) return;
    setState(() { _isLoadingDetail = true; _detailError = null; });
    final provider = context.read<CommodityProvider>();
    await provider.loadCommodityDetail(widget.commodityId);
    if (!mounted) return;
    setState(() {
      _isLoadingDetail = false;
      _detailError = provider.errorMessage;
    });
    // Load prediksi setelah detail tersedia (butuh nama komoditas)
    final commodity = provider.selectedCommodity;
    if (commodity != null) _loadPrediction(commodity.name);
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

  Future<void> _loadPrediction(String commodityName) async {
    if (!mounted) return;
    setState(() { _isLoadingPred = true; _predError = null; });
    try {
      final res = await _api.getPrediction(commodityName);
      if (!mounted) return;
      if (res['success'] == true && res['data'] != null) {
        final data  = res['data'] as Map<String, dynamic>;
        final forecast  = List<num>.from(data['forecast'] ?? []);
        final tanggal   = List<String>.from(data['tanggal_pred'] ?? []);
        final hargaKini = (data['harga_terakhir'] as num).toDouble();
        final acc       = data['accuracy'] as Map<String, dynamic>? ?? {};

        final points = <_PredPoint>[];
        for (int i = 0; i < forecast.length && i < tanggal.length; i++) {
          try {
            points.add(_PredPoint(
              DateTime.parse(tanggal[i]),
              forecast[i].toDouble(),
            ));
          } catch (_) {}
        }

        // Sesuaikan default tab prediksi dengan data yang tersedia
        final totalDays = points.length;
        int defaultDays = 30;
        if (totalDays >= 90) defaultDays = 30;
        else if (totalDays >= 60) defaultDays = 30;
        else defaultDays = totalDays;

        setState(() {
          _predResult = _PredResult(
            points: points,
            hargaTerakhir: hargaKini,
            totalDays: totalDays,
            accuracy: acc['accuracy'] != null
                ? (acc['accuracy'] as num).toDouble() : null,
            mape: acc['mape'] != null
                ? (acc['mape'] as num).toDouble() : null,
            cachedAt: DateTime.now(),
          );
          _selectedPredDays = defaultDays;
          _touchedPredIndex = -1;
          _lastPredDate = DateTime.now();
        });
      } else {
        setState(() => _predError = res['message'] ?? 'Data prediksi tidak tersedia');
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _predError = 'Gagal memuat prediksi');
    } finally {
      if (mounted) setState(() => _isLoadingPred = false);
    }
  }

  void _startPolling() {
    _pollingTimer = Timer.periodic(const Duration(seconds: 30), (_) async {
      if (!mounted) return;
      final provider = context.read<CommodityProvider>();
      await provider.loadCommodityDetail(widget.commodityId);
    });
  }

  // Cek tiap menit apakah sudah lewat tengah malam → refresh prediksi
  void _startMidnightRefresh() {
    _midnightTimer = Timer.periodic(const Duration(minutes: 1), (_) async {
      if (!mounted) return;
      final now = DateTime.now();
      if (_lastPredDate != null &&
          now.day != _lastPredDate!.day) {
        // Hari sudah berganti → reload prediksi
        final provider = context.read<CommodityProvider>();
        final commodity = provider.selectedCommodity;
        if (commodity != null) await _loadPrediction(commodity.name);
      }
    });
  }

  Future<void> _onRefresh() async {
    await Future.wait([
      _loadDetail(),
      _loadHistory(_selectedPeriod),
    ]);
  }

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

  // ── Build ─────────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Consumer<CommodityProvider>(
      builder: (context, provider, _) {
        final commodity = provider.selectedCommodity;

        if (_isLoadingDetail && commodity == null) {
          return Scaffold(
            backgroundColor: isDark ? const Color(0xFF121212) : const Color(0xFFF5F7FA),
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
                  Text('Memuat data komoditas...', style: TextStyle(color: Colors.grey)),
                ],
              ),
            ),
          );
        }

        if (_detailError != null && commodity == null) {
          return Scaffold(
            backgroundColor: isDark ? const Color(0xFF121212) : const Color(0xFFF5F7FA),
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
                    const Icon(Icons.cloud_off_rounded, size: 56, color: Colors.grey),
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
                  ],
                ),
              ),
            ),
          );
        }

        final color = _categoryColor(commodity?.category ?? '');
        final icon  = _categoryIcon(commodity?.category ?? '');

        return Scaffold(
          backgroundColor: isDark ? const Color(0xFF121212) : const Color(0xFFF5F7FA),
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
                      // ── BARU: Section prediksi inline ────────────────────
                      _buildPredictionCard(isDark),
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

  // ── Sliver App Bar ────────────────────────────────────────────────────────────

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
              colors: [const Color(0xFF1565C0), color.withValues(alpha: 0.85)],
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
                            style: const TextStyle(
                                color: Colors.white, fontSize: 20,
                                fontWeight: FontWeight.w700)),
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

  // ── Price Card ────────────────────────────────────────────────────────────────

  Widget _buildPriceCard(CommodityModel commodity, Color color, bool isDark) {
    final isNaik  = commodity.isIncreasing;
    final cardBg  = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    final selisih = commodity.currentPrice - commodity.previousPrice;

    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.2), width: 0.8),
        boxShadow: [BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.06),
            blurRadius: 12, offset: const Offset(0, 3))],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(children: [
              Text('Harga Aktual',
                  style: TextStyle(fontSize: 12, color: Colors.grey[500],
                      fontWeight: FontWeight.w500)),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: isNaik
                      ? const Color(0xFF4CAF50).withValues(alpha: 0.1)
                      : const Color(0xFFF44336).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(mainAxisSize: MainAxisSize.min, children: [
                  Icon(
                    isNaik ? Icons.arrow_upward_rounded : Icons.arrow_downward_rounded,
                    size: 12,
                    color: isNaik ? const Color(0xFF4CAF50) : const Color(0xFFF44336),
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
                  icon: Icons.history, color: Colors.grey[600]!, isDark: isDark)),
              Container(width: 1, height: 40,
                  color: isDark
                      ? Colors.grey.withValues(alpha: 0.2)
                      : Colors.grey.withValues(alpha: 0.15)),
              Expanded(child: _buildStatItem(
                  label: 'Selisih Harga',
                  value: (selisih >= 0 ? '+' : '') + _rupiahFmt.format(selisih),
                  icon: Icons.swap_vert,
                  color: selisih > 0
                      ? const Color(0xFF4CAF50)
                      : selisih < 0 ? const Color(0xFFF44336) : Colors.grey[600]!,
                  isDark: isDark)),
            ]),
          ],
        ),
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

  // ── Prediction Banner (navigate ke tab prediksi) ──────────────────────────────

  Widget _buildPredictionBanner(CommodityModel commodity, bool isDark) {
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
            color: const Color(0xFF7C3AED).withValues(alpha: 0.25), width: 0.8),
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
                  Text('Buka halaman prediksi lengkap',
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

  // ── BARU: Prediction Card inline ──────────────────────────────────────────────

  Widget _buildPredictionCard(bool isDark) {
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
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Header ──────────────────────────────────────────────────────
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
              if (_isLoadingPred)
                const SizedBox(width: 14, height: 14,
                    child: CircularProgressIndicator(
                        strokeWidth: 2, color: Color(0xFF7C3AED))),
            ]),
            const SizedBox(height: 14),

            // ── Tab 30/60/90 hari (sesuai data tersedia) ─────────────────────
            if (_predResult != null) ...[
              _buildPredDaysTabs(isDark),
              const SizedBox(height: 16),
              _buildPredSummaryRow(isDark),
              const SizedBox(height: 16),
              _buildPredChart(isDark),
              const SizedBox(height: 14),
              _buildPredTable(isDark),
              const SizedBox(height: 4),
              // ── Akurasi model ──────────────────────────────────────────────
              if (_predResult!.accuracy != null || _predResult!.mape != null)
                _buildAccuracyRow(isDark),
            ] else if (_isLoadingPred)
              const SizedBox(
                height: 120,
                child: Center(child: CircularProgressIndicator(
                    color: Color(0xFF7C3AED), strokeWidth: 2)),
              )
            else if (_predError != null)
              _buildPredError(isDark)
            else
              _buildPredEmpty(isDark),
          ],
        ),
      ),
    );
  }

  // Tab 30 / 60 / 90 hari
  Widget _buildPredDaysTabs(bool isDark) {
    final total = _predResult!.totalDays;
    // Hanya tampilkan tab yang datanya ada
    final tabs = <int>[
      if (total >= 30) 30,
      if (total >= 60) 60,
      if (total >= 90) 90,
      // Kalau kurang dari 30, tampilkan satu tab sesuai total
      if (total < 30) total,
    ];

    return Row(
      children: tabs.map((days) {
        final isActive = _selectedPredDays == days;
        return Padding(
          padding: const EdgeInsets.only(right: 8),
          child: GestureDetector(
            onTap: () => setState(() {
              _selectedPredDays = days;
              _touchedPredIndex = -1;
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
              child: Text(
                days < 30 ? '$days Hari' : '${days ~/ 30} Bulan',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
                  color: isActive
                      ? Colors.white
                      : (isDark ? Colors.grey[300] : Colors.grey[700]),
                ),
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  // Summary: estimasi akhir + tren
  Widget _buildPredSummaryRow(bool isDark) {
    final pred    = _predResult!;
    final tren    = pred.trenPersen(_selectedPredDays);
    final estimasi = pred.estimasiAkhir(_selectedPredDays);
    final isNaik  = tren >= 0;

    return Row(children: [
      Expanded(child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [const Color(0xFF7C3AED),
              const Color(0xFF7C3AED).withValues(alpha: 0.75)],
            begin: Alignment.topLeft, end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('ESTIMASI ${_selectedPredDays < 30 ? '$_selectedPredDays HARI' : '${_selectedPredDays ~/ 30} BULAN'}',
              style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700,
                  letterSpacing: 0.6,
                  color: Colors.white.withValues(alpha: 0.75))),
          const SizedBox(height: 6),
          Text(_rupiahFmt.format(estimasi),
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800,
                  color: Colors.white, letterSpacing: -0.5)),
          const SizedBox(height: 4),
          Text('Saat ini: ${_rupiahFmt.format(pred.hargaTerakhir)}',
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
              : const Color(0xFF10B981)).withValues(alpha: isDark ? 0.15 : 0.08),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: (isNaik
                ? const Color(0xFFF44336)
                : const Color(0xFF10B981)).withValues(alpha: 0.3),
          ),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              isNaik ? Icons.trending_up_rounded : Icons.trending_down_rounded,
              color: isNaik ? const Color(0xFFF44336) : const Color(0xFF10B981),
              size: 26,
            ),
            const SizedBox(height: 6),
            Text('${isNaik ? '+' : ''}$tren%',
                style: TextStyle(
                  fontSize: 16, fontWeight: FontWeight.w800,
                  color: isNaik
                      ? const Color(0xFFF44336) : const Color(0xFF10B981),
                )),
            Text('tren', style: TextStyle(fontSize: 10, color: Colors.grey[500])),
          ],
        ),
      ),
    ]);
  }

  // Grafik line prediksi
  Widget _buildPredChart(bool isDark) {
    final points = _predResult!.slice(_selectedPredDays);
    if (points.isEmpty) return const SizedBox.shrink();

    final prices  = points.map((p) => p.price).toList();
    final minVal  = prices.reduce((a, b) => a < b ? a : b);
    final maxVal  = prices.reduce((a, b) => a > b ? a : b);
    final range   = maxVal - minVal;
    final pad     = range == 0 ? minVal * 0.05 : range * 0.2;
    final minY    = (minVal - pad).clamp(0, double.infinity).toDouble();
    final maxY    = maxVal + pad;

    // Harga saat ini sebagai referensi
    final hargaKini = _predResult!.hargaTerakhir;
    final isNaik    = _predResult!.trenPersen(_selectedPredDays) >= 0;
    final lineColor = isNaik ? const Color(0xFFF44336) : const Color(0xFF10B981);

    final spots = points.asMap().entries
        .map((e) => FlSpot(e.key.toDouble(), e.value.price))
        .toList();

    final xInterval = ((points.length / 4).ceilToDouble()).clamp(2.0, double.infinity);

    return SizedBox(
      height: 200,
      child: LineChart(
        LineChartData(
          minY: minY,
          maxY: maxY,
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
            rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
            topTitles:   const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          ),
          borderData: FlBorderData(show: false),
          // Garis horizontal harga saat ini sebagai referensi
          extraLinesData: ExtraLinesData(horizontalLines: [
            HorizontalLine(
              y: hargaKini,
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
                if (response?.lineBarSpots == null || response!.lineBarSpots!.isEmpty) {
                  _touchedPredIndex = -1;
                } else {
                  _touchedPredIndex = response.lineBarSpots!.first.spotIndex;
                }
              });
            },
            touchTooltipData: LineTouchTooltipData(
              getTooltipColor: (_) => const Color(0xFF7C3AED),
              getTooltipItems: (spots) => spots.map((s) {
                final idx = s.x.toInt();
                final date = idx < points.length
                    ? DateFormat('dd MMM').format(points[idx].date) : '';
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
              isCurved: true,
              curveSmoothness: 0.3,
              color: lineColor,
              barWidth: 2.5,
              isStrokeCapRound: true,
              dotData: FlDotData(
                show: true,
                getDotPainter: (spot, percent, bar, index) {
                  final isTouched = index == _touchedPredIndex;
                  return FlDotCirclePainter(
                    radius: isTouched ? 4.5 : 2.5,
                    color: lineColor,
                    strokeWidth: 1.5,
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
    );
  }

  // Tabel mingguan prediksi
  Widget _buildPredTable(bool isDark) {
    final points   = _predResult!.slice(_selectedPredDays);
    final hargaKini = _predResult!.hargaTerakhir;
    if (points.isEmpty) return const SizedBox.shrink();

    // Grouping per 7 hari
    final weeks = <Map<String, dynamic>>[];
    for (int i = 0; i < points.length; i += 7) {
      final end    = (i + 7 < points.length) ? i + 7 : points.length;
      final slice  = points.sublist(i, end);
      final avg    = slice.map((p) => p.price).reduce((a, b) => a + b) / slice.length;
      final delta  = hargaKini > 0
          ? double.parse(((avg - hargaKini) / hargaKini * 100).toStringAsFixed(1))
          : 0.0;
      weeks.add({
        'label'  : 'W${weeks.length + 1}',
        'start'  : slice.first.date,
        'end'    : slice.last.date,
        'avg'    : avg,
        'delta'  : delta,
      });
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Ringkasan Mingguan',
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
                color: Colors.grey[500])),
        const SizedBox(height: 8),
        // Header
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: isDark
                ? Colors.white.withValues(alpha: 0.04)
                : const Color(0xFFF8FAFC),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(10)),
          ),
          child: Row(children: [
            _tblHeader('MINGGU', flex: 1, isDark: isDark),
            _tblHeader('PERIODE', flex: 3, isDark: isDark),
            _tblHeader('RATA-RATA', flex: 2, isDark: isDark, right: true),
            _tblHeader('TREN', flex: 2, isDark: isDark, right: true),
          ]),
        ),
        // Rows
        ...weeks.asMap().entries.map((entry) {
          final i    = entry.key;
          final w    = entry.value;
          final isUp = (w['delta'] as double) >= 0;
          final isLast = i == weeks.length - 1;
          return Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
            decoration: BoxDecoration(
              color: isDark ? Colors.white.withValues(alpha: 0.02) : Colors.white,
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
                      color: isDark ? Colors.white : const Color(0xFF1A1A2E)))),
              Expanded(flex: 3, child: Text(
                '${DateFormat('dd/MM').format(w['start'])} – ${DateFormat('dd/MM').format(w['end'])}',
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
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                    color: (isUp ? const Color(0xFFF44336) : const Color(0xFF10B981))
                        .withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    '${isUp ? '+' : ''}${w['delta']}%',
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700,
                        color: isUp ? const Color(0xFFF44336) : const Color(0xFF10B981)),
                  ),
                ),
              )),
            ]),
          );
        }),
      ],
    );
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

  // Baris akurasi model
  Widget _buildAccuracyRow(bool isDark) {
    final pred = _predResult!;
    return Padding(
      padding: const EdgeInsets.only(top: 10),
      child: Row(children: [
        const Icon(Icons.verified_rounded, size: 13, color: Color(0xFFF59E0B)),
        const SizedBox(width: 6),
        if (pred.accuracy != null)
          Text('Akurasi model: ${pred.accuracy!.toStringAsFixed(1)}%',
              style: const TextStyle(fontSize: 11, color: Color(0xFFF59E0B),
                  fontWeight: FontWeight.w600)),
        if (pred.accuracy != null && pred.mape != null)
          Text('  ·  ', style: TextStyle(color: Colors.grey[400])),
        if (pred.mape != null)
          Text('MAPE: ${pred.mape!.toStringAsFixed(2)}%',
              style: TextStyle(fontSize: 11, color: Colors.grey[500])),
      ]),
    );
  }

  Widget _buildPredError(bool isDark) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFF44336).withValues(alpha: 0.07),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFF44336).withValues(alpha: 0.2)),
      ),
      child: Row(children: [
        const Icon(Icons.info_outline_rounded,
            size: 16, color: Color(0xFFF44336)),
        const SizedBox(width: 10),
        Expanded(child: Text(_predError!,
            style: const TextStyle(fontSize: 12, color: Color(0xFFF44336)))),
        TextButton(
          onPressed: () {
            final provider = context.read<CommodityProvider>();
            final commodity = provider.selectedCommodity;
            if (commodity != null) _loadPrediction(commodity.name);
          },
          child: const Text('Coba Lagi',
              style: TextStyle(fontSize: 11, color: Color(0xFF7C3AED))),
        ),
      ]),
    );
  }

  Widget _buildPredEmpty(bool isDark) {
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

  // ── Chart Card (historis) ─────────────────────────────────────────────────────

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
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(children: [
              Text('Grafik Harga Historis',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600,
                      color: isDark ? Colors.white : const Color(0xFF1A1A2E))),
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
                                onPressed: () => _loadHistory(_selectedPeriod),
                                child: const Text('Coba Lagi'),
                              ),
                            ],
                          ))
                        : provider.priceHistory.isEmpty
                            ? Center(child: Text('Belum ada data historis',
                                style: TextStyle(color: Colors.grey[500])))
                            : _buildLineChart(provider.priceHistory, isDark),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLineChart(List<PriceModel> history, bool isDark) {
    final spots = history.asMap().entries
        .map((e) => FlSpot(e.key.toDouble(), e.value.hargaSekarang))
        .toList();

    final prices = history.map((e) => e.hargaSekarang).toList();
    final minVal = prices.reduce((a, b) => a < b ? a : b);
    final maxVal = prices.reduce((a, b) => a > b ? a : b);
    final padding = (maxVal - minVal) == 0
        ? minVal * 0.1 : (maxVal - minVal) * 0.25;
    final minY = minVal - padding;
    final maxY = maxVal + padding;
    final yInterval  = (maxY - minY) / 3;
    final xInterval  =
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
            if (value >= 1000000)      label = '${(value / 1000000).toStringAsFixed(1)}jt';
            else if (value >= 1000) {
              final rb = value / 1000;
              label = rb == rb.roundToDouble()
                  ? '${rb.toInt()}rb' : '${rb.toStringAsFixed(1)}rb';
            } else                     label = value.toInt().toString();
            return SideTitleWidget(axisSide: meta.axisSide, space: 8,
                child: Text(label,
                    style: TextStyle(color: Colors.grey[500], fontSize: 9)));
          },
        )),
        bottomTitles: AxisTitles(sideTitles: SideTitles(
          showTitles: true, reservedSize: 32, interval: xInterval,
          getTitlesWidget: (value, meta) {
            final idx = value.toInt();
            if (idx < 0 || idx >= history.length) return const Text('');
            return Padding(
              padding: const EdgeInsets.only(top: 6),
              child: Text(DateFormat('dd/MM').format(history[idx].date),
                  style: TextStyle(color: Colors.grey[500], fontSize: 9)),
            );
          },
        )),
        rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
        topTitles:   const AxisTitles(sideTitles: SideTitles(showTitles: false)),
      ),
      borderData: FlBorderData(show: false),
      lineTouchData: LineTouchData(
        touchTooltipData: LineTouchTooltipData(
          getTooltipColor: (_) => const Color(0xFF1565C0),
          getTooltipItems: (spots) => spots.map((s) {
            final idx = s.x.toInt();
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
          spots: spots, isCurved: true, curveSmoothness: 0.3,
          color: const Color(0xFF1976D2), barWidth: 2.5,
          isStrokeCapRound: true,
          dotData: FlDotData(show: true,
              getDotPainter: (spot, percent, bar, index) => FlDotCirclePainter(
                  radius: 2.5, color: const Color(0xFF1976D2),
                  strokeWidth: 1.5, strokeColor: Colors.white)),
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
              : (isDark ? Colors.white.withValues(alpha: 0.08) : Colors.grey[100]),
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