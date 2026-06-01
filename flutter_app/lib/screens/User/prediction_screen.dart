import 'package:flutter/material.dart';
import 'package:flutter_app/services/api_service.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:intl/intl.dart';

class _WeekRow {
  final String minggu;
  final String periode;
  final int estimasi;
  final double deltaPct;
  _WeekRow({
    required this.minggu,
    required this.periode,
    required this.estimasi,
    required this.deltaPct,
  });
}

class _HistoryRow {
  final String komoditas;
  final String generated;
  final int? days;
  final double? mae;
  final double? rmse;
  final double? mape;
  _HistoryRow({
    required this.komoditas,
    required this.generated,
    this.days,
    this.mae,
    this.rmse,
    this.mape,
  });
}

class _PredData {
  final String komoditas;
  final String satuan;
  final double hargaKini;
  final double estimasiHarga;
  final double trenPersen;
  final double? kepercayaan;
  final double? mape;
  final List<_WeekRow> mingguan;

  _PredData({
    required this.komoditas,
    required this.satuan,
    required this.hargaKini,
    required this.estimasiHarga,
    required this.trenPersen,
    this.kepercayaan,
    this.mape,
    required this.mingguan,
  });

  factory _PredData.fromJson(Map<String, dynamic> data) {
    final forecast = List<num>.from(data['forecast'] ?? []);
    final tanggal = List<String>.from(data['tanggal_pred'] ?? []);
    final hargaKini = (data['harga_terakhir'] as num).toDouble();
    final acc = data['accuracy'] as Map<String, dynamic>? ?? {};
    final estimasi = forecast.isNotEmpty ? forecast.last.toDouble() : 0.0;
    final tren =
        hargaKini > 0 ? ((estimasi - hargaKini) / hargaKini * 100) : 0.0;

    return _PredData(
      komoditas: data['komoditas'] ?? '',
      satuan: data['satuan'] ?? 'kg',
      hargaKini: hargaKini,
      estimasiHarga: estimasi,
      trenPersen: double.parse(tren.toStringAsFixed(1)),
      kepercayaan:
          acc['accuracy'] != null ? (acc['accuracy'] as num).toDouble() : null,
      mape: acc['mape'] != null ? (acc['mape'] as num).toDouble() : null,
      mingguan: _buildWeekly(tanggal, forecast, hargaKini),
    );
  }

  static List<_WeekRow> _buildWeekly(
      List<String> tanggal, List<num> forecast, double hargaKini) {
    final weeks = <_WeekRow>[];
    for (int i = 0; i < forecast.length; i += 7) {
      final end = (i + 7 < forecast.length) ? i + 7 : forecast.length;
      final prices = forecast.sublist(i, end);
      final dates = tanggal.sublist(i, end);
      if (prices.isEmpty) continue;

      final avg = prices.map((p) => p.toDouble()).reduce((a, b) => a + b) /
          prices.length;
      final deltaPct = hargaKini > 0
          ? double.parse(
              ((avg - hargaKini) / hargaKini * 100).toStringAsFixed(1))
          : 0.0;

      weeks.add(_WeekRow(
        minggu: 'W${weeks.length + 1}',
        periode: '${dates.first} – ${dates.last}',
        estimasi: avg.round(),
        deltaPct: deltaPct,
      ));
    }
    return weeks;
  }
}

class UserPredictionScreen extends StatefulWidget {
  final String? initialCommodity;
  const UserPredictionScreen({super.key, this.initialCommodity});

  @override
  State<UserPredictionScreen> createState() => _UserPredictionScreenState();
}

class _UserPredictionScreenState extends State<UserPredictionScreen>
    with TickerProviderStateMixin {
  final ApiService _api = ApiService();

  List<String> _komoditasList = [];
  String? _selected;
  _PredData? _predData;
  List<_HistoryRow> _history = [];

  bool _isLoading = true;
  bool _isPredLoad = false;
  String? _error;

  int _touchedBarIndex = -1;

  late AnimationController _fadeCtrl;
  late Animation<double> _fadeAnim;

  final _fmt =
      NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0);

  static const _blue  = Color(0xFF1976D2);
  static const _green = Color(0xFF10B981);
  static const _red   = Color(0xFFEF4444);
  static const _amber = Color(0xFFF59E0B);

  @override
  void initState() {
    super.initState();
    _fadeCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 400));
    _fadeAnim = CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOut);
    _init();
  }

  // ── BARU: reaktif saat initialCommodity berubah dari luar ──
  // Karena pakai ValueKey di main_screen, widget ini sudah rebuild otomatis.
  // didUpdateWidget ini sebagai fallback keamanan jika key tidak berubah.
  @override
  void didUpdateWidget(UserPredictionScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.initialCommodity != null &&
        widget.initialCommodity != oldWidget.initialCommodity) {
      _init();
    }
  }

  @override
  void dispose() {
    _fadeCtrl.dispose();
    super.dispose();
  }

  Future<void> _init() async {
    setState(() => _isLoading = true);
    try {
      final list = await _api.getPredictableCommodities();
      _komoditasList = list;
      _selected = widget.initialCommodity != null &&
              list.contains(widget.initialCommodity)
          ? widget.initialCommodity
          : (list.isNotEmpty ? list.first : null);
      if (_selected != null) await _loadPrediction(_selected!);
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _loadPrediction(String komoditas) async {
    setState(() {
      _isPredLoad = true;
      _error = null;
      _touchedBarIndex = -1;
    });
    _fadeCtrl.reset();
    try {
      final res = await _api.getPrediction(komoditas);
      if (res['success'] == true) {
        setState(() {
          _predData = _PredData.fromJson(res['data'] as Map<String, dynamic>);
          _selected = komoditas;
        });
        _fadeCtrl.forward();
      } else {
        setState(() => _error = res['message'] ?? 'Gagal memuat prediksi');
      }
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _isPredLoad = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? const Color(0xFF0F1117) : const Color(0xFFF0F4F8);
    final cardBg = isDark ? const Color(0xFF1C1F2A) : Colors.white;
    final textPri = isDark ? Colors.white : const Color(0xFF0F172A);
    final textSub = isDark ? const Color(0xFF8892A4) : const Color(0xFF64748B);

    if (_isLoading) {
      return Scaffold(
        backgroundColor: bgColor,
        body: const Center(child: LoadingWidget()),
      );
    }

    return Scaffold(
      backgroundColor: bgColor,
      body: RefreshIndicator(
        color: _blue,
        onRefresh: _init,
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverAppBar(
              expandedHeight: 120,
              pinned: true,
              backgroundColor: isDark ? const Color(0xFF1C1F2A) : Colors.white,
              elevation: 0,
              surfaceTintColor: Colors.transparent,
              flexibleSpace: FlexibleSpaceBar(
                titlePadding:
                    const EdgeInsets.only(left: 16, bottom: 14, right: 16),
                title: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Prediksi Harga',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                        color: textPri,
                        letterSpacing: -0.5,
                      ),
                    ),
                    if (_predData != null)
                      Text(
                        _predData!.komoditas,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                          color: _blue,
                        ),
                      ),
                  ],
                ),
              ),
            ),

            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  _buildFilterCard(isDark, cardBg, textPri, textSub),
                  const SizedBox(height: 16),

                  if (_error != null)
                    _errorWidget(_error!, textSub)
                  else if (_isPredLoad)
                    const Center(
                      child: Padding(
                        padding: EdgeInsets.symmetric(vertical: 64),
                        child: CircularProgressIndicator(color: _blue),
                      ),
                    )
                  else if (_predData != null)
                    FadeTransition(
                      opacity: _fadeAnim,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildStatRow(_predData!, isDark, cardBg, textPri),
                          const SizedBox(height: 16),
                          _buildChart(_predData!, isDark, cardBg, textPri, textSub),
                          const SizedBox(height: 16),
                          _buildWeeklyTable(_predData!, isDark, cardBg, textPri, textSub),
                          const SizedBox(height: 16),
                        ],
                      ),
                    )
                  else if (_komoditasList.isEmpty)
                    _emptyWidget(
                      'Belum ada prediksi',
                      'Admin belum melakukan generate prediksi.',
                      textSub,
                    )
                  else
                    _emptyWidget(
                      'Pilih komoditas',
                      'Gunakan filter di atas untuk memilih komoditas.',
                      textSub,
                    ),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterCard(
      bool isDark, Color cardBg, Color textPri, Color textSub) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: _cardDecor(isDark, cardBg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: _blue.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.grain_rounded, color: _blue, size: 16),
              ),
              const SizedBox(width: 10),
              Text(
                'Pilih Komoditas',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: textPri,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (_komoditasList.isEmpty)
            Text('Tidak ada komoditas tersedia',
                style: TextStyle(color: textSub))
          else
            DropdownButtonFormField<String>(
              isExpanded: true,
              value: _selected,
              dropdownColor: isDark ? const Color(0xFF252836) : Colors.white,
              icon: const Icon(Icons.keyboard_arrow_down_rounded, color: _blue),
              style: TextStyle(
                  color: textPri, fontSize: 14, fontWeight: FontWeight.w600),
              items: _komoditasList.map((name) {
                return DropdownMenuItem(value: name, child: Text(name));
              }).toList(),
              onChanged: (val) {
                if (val != null) _loadPrediction(val);
              },
              decoration: InputDecoration(
                filled: true,
                fillColor:
                    isDark ? const Color(0xFF252836) : const Color(0xFFF8FAFC),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark
                        ? Colors.white.withValues(alpha: 0.1)
                        : const Color(0xFFE2E8F0),
                  ),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark
                        ? Colors.white.withValues(alpha: 0.1)
                        : const Color(0xFFE2E8F0),
                  ),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: _blue, width: 1.5),
                ),
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildStatRow(_PredData d, bool isDark, Color cardBg, Color textPri) {
    final isUp = d.trenPersen >= 0;

    return Column(
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [_blue, _blue.withValues(alpha: 0.75)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(18),
            boxShadow: [
              BoxShadow(
                color: _blue.withValues(alpha: 0.35),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.show_chart_rounded,
                      color: Colors.white70, size: 16),
                  const SizedBox(width: 6),
                  Text(
                    'ESTIMASI HARGA 30 HARI',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 0.8,
                      color: Colors.white.withValues(alpha: 0.7),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(
                _fmt.format(d.estimasiHarga),
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.w900,
                  color: Colors.white,
                  letterSpacing: -1,
                ),
              ),
              const SizedBox(height: 6),
              Row(
                children: [
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      'Saat ini: ${_fmt.format(d.hargaKini)}',
                      style: const TextStyle(
                          fontSize: 11,
                          color: Colors.white,
                          fontWeight: FontWeight.w600),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: (isUp ? Colors.red : _green).withValues(alpha: 0.25),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          isUp
                              ? Icons.arrow_upward_rounded
                              : Icons.arrow_downward_rounded,
                          size: 11,
                          color: Colors.white,
                        ),
                        const SizedBox(width: 2),
                        Text(
                          '${isUp ? '+' : ''}${d.trenPersen}%',
                          style: const TextStyle(
                              fontSize: 11,
                              color: Colors.white,
                              fontWeight: FontWeight.w700),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _miniStatCard(
                icon: isUp
                    ? Icons.trending_up_rounded
                    : Icons.trending_down_rounded,
                iconColor: isUp ? _red : _green,
                iconBg: (isUp ? _red : _green).withValues(alpha: 0.1),
                label: 'Tren Prediksi',
                value: '${isUp ? '+' : ''}${d.trenPersen}%',
                valueColor: isUp ? _red : _green,
                sub: isUp ? 'Cenderung naik' : 'Cenderung turun',
                isDark: isDark,
                cardBg: cardBg,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _miniStatCard(
                icon: Icons.verified_rounded,
                iconColor: _amber,
                iconBg: _amber.withValues(alpha: 0.1),
                label: 'Kepercayaan AI',
                value: d.kepercayaan != null
                    ? '${d.kepercayaan!.toStringAsFixed(1)}%'
                    : '—',
                valueColor: _amber,
                sub: d.mape != null
                    ? 'MAPE: ${d.mape!.toStringAsFixed(2)}%'
                    : 'MAPE: —',
                isDark: isDark,
                cardBg: cardBg,
                extra: d.kepercayaan != null
                    ? Padding(
                        padding: const EdgeInsets.only(top: 8),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(6),
                          child: LinearProgressIndicator(
                            value: (d.kepercayaan! / 100).clamp(0.0, 1.0),
                            minHeight: 6,
                            backgroundColor: Colors.grey.shade200,
                            valueColor: const AlwaysStoppedAnimation(_amber),
                          ),
                        ),
                      )
                    : null,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _miniStatCard({
    required IconData icon,
    required Color iconColor,
    required Color iconBg,
    required String label,
    required String value,
    required Color valueColor,
    required String sub,
    required bool isDark,
    required Color cardBg,
    Widget? extra,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: _cardDecor(isDark, cardBg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                    color: iconBg, borderRadius: BorderRadius.circular(8)),
                child: Icon(icon, color: iconColor, size: 14),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  label,
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color: isDark
                        ? const Color(0xFF8892A4)
                        : const Color(0xFF64748B),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            value,
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w900,
              color: valueColor,
              letterSpacing: -0.5,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            sub,
            style: TextStyle(
              fontSize: 10,
              color: isDark ? const Color(0xFF8892A4) : const Color(0xFF94A3B8),
            ),
          ),
          if (extra != null) extra,
        ],
      ),
    );
  }

  Widget _buildChart(
      _PredData d, bool isDark, Color cardBg, Color textPri, Color textSub) {
    if (d.mingguan.isEmpty) return const SizedBox.shrink();

    final isUp = d.trenPersen >= 0;
    final barColor = isUp ? _red : _green;
    final maxVal = d.mingguan
        .map((w) => w.estimasi.toDouble())
        .reduce((a, b) => a > b ? a : b);
    final minVal = d.mingguan
        .map((w) => w.estimasi.toDouble())
        .reduce((a, b) => a < b ? a : b);
    final range = maxVal - minVal;
    final padding = range * 0.15;
    final chartMin = (minVal - padding).clamp(0, double.infinity).toDouble();
    final chartMax = maxVal + padding;

    return Container(
      decoration: _cardDecor(isDark, cardBg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: _blue.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.bar_chart_rounded,
                      color: _blue, size: 16),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Grafik Prediksi Mingguan',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: textPri,
                    ),
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: barColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isUp
                            ? Icons.arrow_upward_rounded
                            : Icons.arrow_downward_rounded,
                        size: 10,
                        color: barColor,
                      ),
                      const SizedBox(width: 3),
                      Text(
                        '${isUp ? '+' : ''}${d.trenPersen}%',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: barColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          if (_touchedBarIndex >= 0 && _touchedBarIndex < d.mingguan.length)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
              child: _buildTooltip(d.mingguan[_touchedBarIndex], isDark),
            )
          else
            const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.fromLTRB(8, 8, 16, 8),
            child: SizedBox(
              height: 180,
              child: BarChart(
                BarChartData(
                  alignment: BarChartAlignment.spaceAround,
                  maxY: chartMax,
                  minY: chartMin,
                  barTouchData: BarTouchData(
                    enabled: true,
                    touchTooltipData: BarTouchTooltipData(
                      getTooltipColor: (_) => Colors.transparent,
                      tooltipPadding: EdgeInsets.zero,
                      tooltipMargin: 0,
                      getTooltipItem: (_, __, ___, ____) => null,
                    ),
                    touchCallback: (event, response) {
                      setState(() {
                        if (response == null ||
                            response.spot == null ||
                            event is FlPointerExitEvent) {
                          _touchedBarIndex = -1;
                        } else {
                          _touchedBarIndex =
                              response.spot!.touchedBarGroupIndex;
                        }
                      });
                    },
                  ),
                  titlesData: FlTitlesData(
                    show: true,
                    bottomTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        getTitlesWidget: (value, _) {
                          final idx = value.toInt();
                          if (idx < 0 || idx >= d.mingguan.length) {
                            return const SizedBox.shrink();
                          }
                          final isActive = idx == _touchedBarIndex;
                          return Padding(
                            padding: const EdgeInsets.only(top: 4),
                            child: Text(
                              d.mingguan[idx].minggu,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: isActive
                                    ? FontWeight.w800
                                    : FontWeight.w500,
                                color: isActive ? _blue : textSub,
                              ),
                            ),
                          );
                        },
                        reservedSize: 28,
                      ),
                    ),
                    leftTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        reservedSize: 56,
                        interval: range > 0 ? range / 3 : 1,
                        getTitlesWidget: (value, _) {
                          return Text(
                            _fmtShort(value),
                            style: TextStyle(fontSize: 9, color: textSub),
                          );
                        },
                      ),
                    ),
                    topTitles: const AxisTitles(
                        sideTitles: SideTitles(showTitles: false)),
                    rightTitles: const AxisTitles(
                        sideTitles: SideTitles(showTitles: false)),
                  ),
                  gridData: FlGridData(
                    show: true,
                    drawVerticalLine: false,
                    horizontalInterval: range > 0 ? range / 3 : 1,
                    getDrawingHorizontalLine: (_) => FlLine(
                      color: isDark
                          ? Colors.white.withValues(alpha: 0.05)
                          : Colors.grey.withValues(alpha: 0.12),
                      strokeWidth: 1,
                      dashArray: [4, 4],
                    ),
                  ),
                  borderData: FlBorderData(show: false),
                  barGroups: d.mingguan.asMap().entries.map((e) {
                    final i = e.key;
                    final w = e.value;
                    final isTouched = i == _touchedBarIndex;
                    final thisColor = isTouched
                        ? _blue
                        : barColor.withValues(
                            alpha: 0.55 + (i / d.mingguan.length) * 0.45);
                    return BarChartGroupData(
                      x: i,
                      barRods: [
                        BarChartRodData(
                          toY: w.estimasi.toDouble(),
                          fromY: chartMin,
                          color: thisColor,
                          width: 28,
                          borderRadius: const BorderRadius.only(
                            topLeft: Radius.circular(6),
                            topRight: Radius.circular(6),
                          ),
                          backDrawRodData: BackgroundBarChartRodData(
                            show: true,
                            toY: chartMax,
                            fromY: chartMin,
                            color: isDark
                                ? Colors.white.withValues(alpha: 0.03)
                                : Colors.grey.withValues(alpha: 0.06),
                          ),
                        ),
                      ],
                    );
                  }).toList(),
                ),
                duration: const Duration(milliseconds: 300),
                curve: Curves.easeOut,
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _legendDot(_blue),
                const SizedBox(width: 4),
                Text('Dipilih', style: TextStyle(fontSize: 10, color: textSub)),
                const SizedBox(width: 16),
                _legendDot(barColor),
                const SizedBox(width: 4),
                Text('Estimasi harga',
                    style: TextStyle(fontSize: 10, color: textSub)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTooltip(_WeekRow row, bool isDark) {
    final isUp = row.deltaPct >= 0;
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: _blue.withValues(alpha: isDark ? 0.15 : 0.06),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _blue.withValues(alpha: 0.2)),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '${row.minggu}  ·  ${row.periode}',
                  style: TextStyle(
                    fontSize: 10,
                    color: isDark
                        ? const Color(0xFF8892A4)
                        : const Color(0xFF64748B),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  _fmt.format(row.estimasi),
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: _blue,
                    letterSpacing: -0.5,
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: (isUp ? _red : _green).withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              '${isUp ? '+' : ''}${row.deltaPct}%',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: isUp ? _red : _green,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWeeklyTable(
      _PredData d, bool isDark, Color cardBg, Color textPri, Color textSub) {
    return Container(
      decoration: _cardDecor(isDark, cardBg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: _green.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.table_rows_rounded,
                      color: _green, size: 16),
                ),
                const SizedBox(width: 10),
                Text(
                  'Detail Prediksi Mingguan',
                  style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: textPri),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            decoration: BoxDecoration(
              color: isDark
                  ? Colors.white.withValues(alpha: 0.03)
                  : const Color(0xFFF8FAFC),
              border: Border(
                top: BorderSide(
                  color: isDark
                      ? Colors.white.withValues(alpha: 0.06)
                      : const Color(0xFFE2E8F0),
                ),
                bottom: BorderSide(
                  color: isDark
                      ? Colors.white.withValues(alpha: 0.06)
                      : const Color(0xFFE2E8F0),
                ),
              ),
            ),
            child: Row(
              children: [
                _colHeader('MINGGU', flex: 1, textSub: textSub),
                _colHeader('PERIODE', flex: 3, textSub: textSub),
                _colHeader('ESTIMASI',
                    flex: 2, textSub: textSub, align: TextAlign.right),
                _colHeader('PERUBAHAN',
                    flex: 2, textSub: textSub, align: TextAlign.right),
              ],
            ),
          ),
          if (d.mingguan.isEmpty)
            Padding(
              padding: const EdgeInsets.all(24),
              child: Center(
                child: Text('Tidak ada data prediksi.',
                    style: TextStyle(color: textSub)),
              ),
            )
          else
            ...d.mingguan.asMap().entries.map((entry) {
              final i = entry.key;
              final row = entry.value;
              final isUp = row.deltaPct >= 0;
              final isLast = i == d.mingguan.length - 1;
              final isActive = i == _touchedBarIndex;

              return GestureDetector(
                onTap: () => setState(() {
                  _touchedBarIndex = isActive ? -1 : i;
                }),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  decoration: BoxDecoration(
                    color: isActive
                        ? _blue.withValues(alpha: isDark ? 0.12 : 0.05)
                        : Colors.transparent,
                    border: isLast
                        ? null
                        : Border(
                            bottom: BorderSide(
                              color: isDark
                                  ? Colors.white.withValues(alpha: 0.05)
                                  : const Color(0xFFF1F5F9),
                            ),
                          ),
                  ),
                  padding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  child: Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: Row(
                          children: [
                            if (isActive)
                              Container(
                                width: 4,
                                height: 4,
                                margin: const EdgeInsets.only(right: 5),
                                decoration: const BoxDecoration(
                                  color: _blue,
                                  shape: BoxShape.circle,
                                ),
                              ),
                            Text(
                              row.minggu,
                              style: TextStyle(
                                fontWeight: FontWeight.w700,
                                fontSize: 13,
                                color: isActive ? _blue : textPri,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Expanded(
                        flex: 3,
                        child: Text(
                          row.periode,
                          style: TextStyle(
                              fontSize: 10,
                              color: isDark
                                  ? const Color(0xFF8892A4)
                                  : const Color(0xFF94A3B8)),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Text(
                          _fmt.format(row.estimasi),
                          textAlign: TextAlign.right,
                          style: TextStyle(
                            fontWeight: FontWeight.w700,
                            fontSize: 12,
                            color: textPri,
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Container(
                          alignment: Alignment.centerRight,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 7, vertical: 3),
                            decoration: BoxDecoration(
                              color:
                                  (isUp ? _red : _green).withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              '${isUp ? '+' : ''}${row.deltaPct}%',
                              style: TextStyle(
                                fontWeight: FontWeight.w700,
                                fontSize: 11,
                                color: isUp ? _red : _green,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
          const SizedBox(height: 4),
        ],
      ),
    );
  }

  BoxDecoration _cardDecor(bool isDark, Color cardBg) {
    return BoxDecoration(
      color: cardBg,
      borderRadius: BorderRadius.circular(18),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.06),
          blurRadius: 16,
          offset: const Offset(0, 4),
        ),
      ],
    );
  }

  Widget _colHeader(String label,
      {required int flex,
      required Color textSub,
      TextAlign align = TextAlign.left}) {
    return Expanded(
      flex: flex,
      child: Text(
        label,
        textAlign: align,
        style: TextStyle(
          fontSize: 9,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.5,
          color: textSub,
        ),
      ),
    );
  }

  Widget _legendDot(Color color) {
    return Container(
      width: 10,
      height: 10,
      decoration: BoxDecoration(color: color, shape: BoxShape.circle),
    );
  }

  String _fmtShort(double val) {
    if (val >= 1000000) return '${(val / 1000000).toStringAsFixed(1)}jt';
    if (val >= 1000) return '${(val / 1000).toStringAsFixed(0)}rb';
    return val.toStringAsFixed(0);
  }

  Widget _errorWidget(String msg, Color textSub) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _red.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: _red.withValues(alpha: 0.2)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: _red.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.error_outline_rounded, color: _red, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(msg, style: const TextStyle(fontSize: 12, color: _red)),
          ),
        ],
      ),
    );
  }

  Widget _emptyWidget(String title, String sub, Color textSub) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 64),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: _blue.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.show_chart_rounded, size: 40, color: _blue),
            ),
            const SizedBox(height: 16),
            Text(title,
                style: TextStyle(
                    fontSize: 15, fontWeight: FontWeight.w700, color: textSub)),
            const SizedBox(height: 6),
            Text(sub,
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12, color: textSub)),
          ],
        ),
      ),
    );
  }
}