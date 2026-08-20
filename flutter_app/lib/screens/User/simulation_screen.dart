import 'package:flutter/material.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class UserSimulationScreen extends StatefulWidget {
  /// Nama komoditas untuk pre-fill otomatis dari notifikasi (opsional)
  final String? initialCommodity;

  const UserSimulationScreen({super.key, this.initialCommodity});

  @override
  State<UserSimulationScreen> createState() => _UserSimulationScreenState();
}

class _UserSimulationScreenState extends State<UserSimulationScreen>
    with SingleTickerProviderStateMixin {
  String? _selectedCommodityName;
  final TextEditingController _konsumsiController =
      TextEditingController(text: '0.5');

  double? _hargaTerbaru;
  double? _hargaPrediksi;
  double? _totalSekarang;
  double? _totalPrediksi;
  double? _selisih;
  double? _changePercent;
  List<double> _historicalPrices = []; // harga 3 bulan terakhir (aktual)
  String _wawasanAI =
      'AI kami memprediksi kenaikan harga beras sekitar 2.5% bulan depan '
      'dikarenakan faktor musim panen yang bergeser.';

  bool _hasilVisible = false;
  late AnimationController _animCtrl;
  late Animation<double> _fadeAnim;

  final NumberFormat _fmt = NumberFormat.currency(
    locale: 'id',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  @override
  void initState() {
    super.initState();
    _animCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 500));
    _fadeAnim = CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut);
    _loadCommodities();
  }

  Future<void> _loadCommodities() async {
    final provider = Provider.of<CommodityProvider>(context, listen: false);
    await provider.loadPredictableCommodities();
    if (provider.predictableCommodities.isNotEmpty &&
        _selectedCommodityName == null) {
      setState(
          () => _selectedCommodityName = provider.predictableCommodities.first);
    }
  }

  Future<void> _hitungSimulasi() async {
    if (_selectedCommodityName == null) {
      _showSnack('Pilih komoditas terlebih dahulu');
      return;
    }
    final konsumsi = double.tryParse(_konsumsiController.text) ?? 0;
    if (konsumsi <= 0) {
      _showSnack('Konsumsi harus lebih dari 0');
      return;
    }

    final provider = Provider.of<CommodityProvider>(context, listen: false);

    // Langsung kirim nama — sudah pasti valid karena dari Flask
    final success =
        await provider.predictPrice(_selectedCommodityName!, konsumsi);

    if (!mounted) return;

    if (success && provider.predictionResult != null) {
      final r = provider.predictionResult!;
      final hargaTerbaru = (r['current_price'] as num?)?.toDouble() ?? 0;
      final hargaPrediksi = (r['predicted_price'] as num?)?.toDouble() ?? 0;
      final totalSekarang = hargaTerbaru * konsumsi * 4;
      final totalPrediksi = hargaPrediksi * konsumsi * 4;
      final selisih = totalPrediksi - totalSekarang;
      final pct = totalSekarang > 0 ? (selisih / totalSekarang * 100) : 0.0;

      // Ambil historical prices dari response (3 bulan terakhir sebelum current)
      // Backend diharapkan mengirim field 'price_history' berupa List<num>
      // Jika tidak ada, fallback ke simulasi linier dari current ke predicted
      final rawHistory = r['price_history'];
      List<double> historical;
      if (rawHistory is List && rawHistory.isNotEmpty) {
        historical = rawHistory.map((e) => (e as num).toDouble()).toList();
      } else {
        // Fallback: buat 3 titik linier menuju hargaTerbaru
        final step = (hargaTerbaru - hargaPrediksi) / 4;
        historical = [
          hargaTerbaru - step * 3,
          hargaTerbaru - step * 2,
          hargaTerbaru - step,
        ];
      }

      setState(() {
        _hargaTerbaru = hargaTerbaru;
        _hargaPrediksi = hargaPrediksi;
        _totalSekarang = totalSekarang;
        _totalPrediksi = totalPrediksi;
        _selisih = selisih;
        _changePercent = pct;
        _historicalPrices = historical;
        _wawasanAI = r['insight'] as String? ?? _wawasanAI;
        _hasilVisible = true;
      });
      _animCtrl.forward(from: 0);
    } else {
      _showSnack(provider.errorMessage ?? 'Gagal menghitung simulasi',
          isError: true);
    }
  }

  void _showSnack(String msg, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: isError ? Colors.red : Colors.orange,
      ),
    );
  }

  @override
  void dispose() {
    _konsumsiController.dispose();
    _animCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Consumer<CommodityProvider>(
      builder: (context, provider, _) {
        return Scaffold(
          body: Stack(
            children: [
              SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildPageHeader(),
                    const SizedBox(height: 20),
                    _buildInputCard(provider, isDark),
                    const SizedBox(height: 16),
                    _buildAIInsight(isDark),
                    const SizedBox(height: 20),
                    if (_hasilVisible)
                      FadeTransition(
                        opacity: _fadeAnim,
                        child: Column(
                          children: [
                            _buildPriceGrid(),
                            const SizedBox(height: 16),
                            _buildBudgetCard(isDark),
                          ],
                        ),
                      ),
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

  // ── PAGE HEADER ── (gradient, tidak perlu isDark)
  Widget _buildPageHeader() {
    return Container(
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
      child: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Simulasi Pengeluaran AI',
            style: TextStyle(
                color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
          ),
          SizedBox(height: 6),
          Text(
            'Estimasi pengeluaran Anda berdasarkan tren harga komoditas '
            'terkini dan prediksi cerdas untuk perencanaan finansial yang lebih baik.',
            style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.4),
          ),
        ],
      ),
    );
  }

  // ── INPUT CARD ──
  Widget _buildInputCard(CommodityProvider provider, bool isDark) {
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    final labelColor = isDark ? Colors.white : const Color(0xFF1A237E);
    final subColor = isDark ? Colors.grey[400]! : Colors.grey;
    final iconBg = isDark
        ? const Color(0xFF1976D2).withOpacity(0.2)
        : const Color(0xFFE3F2FD);

    return Container(
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
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: iconBg,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.monitor,
                      color: Color(0xFF1976D2), size: 20),
                ),
                const SizedBox(width: 10),
                Text(
                  'Input Data Konsumsi',
                  style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                      color: labelColor),
                ),
              ],
            ),
            const SizedBox(height: 18),
            Text('Pilih Komoditas',
                style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white : const Color(0xFF1A1A2E))),
            const SizedBox(height: 8),
            // Ganti Consumer<CommodityProvider> di dropdown dengan ini
            DropdownButtonFormField<String>(
              isExpanded: true,
              initialValue: _selectedCommodityName,
              dropdownColor: isDark ? const Color(0xFF2C2C2C) : Colors.white,
              style: TextStyle(
                  color: isDark ? Colors.white : Colors.black, fontSize: 14),
              items: provider.predictableCommodities.map((name) {
                return DropdownMenuItem(value: name, child: Text(name));
              }).toList(),
              onChanged: (val) => setState(() => _selectedCommodityName = val),
              decoration: InputDecoration(
                filled: true,
                fillColor:
                    isDark ? const Color(0xFF2C2C2C) : Colors.grey.shade50,
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(
                      color:
                          isDark ? Colors.grey.shade700 : Colors.grey.shade300),
                ),
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              ),
            ),
            const SizedBox(height: 16),
            Text('Konsumsi per Minggu (Kg/Liter)',
                style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white : const Color(0xFF1A1A2E))),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _konsumsiController,
                    keyboardType:
                        const TextInputType.numberWithOptions(decimal: true),
                    style:
                        TextStyle(color: isDark ? Colors.white : Colors.black),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: isDark
                          ? const Color(0xFF2C2C2C)
                          : Colors.grey.shade50,
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10)),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: BorderSide(
                            color: isDark
                                ? Colors.grey.shade700
                                : Colors.grey.shade300),
                      ),
                      hintText: '0.5',
                      hintStyle: TextStyle(color: Colors.grey[500]),
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 12),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF1976D2).withOpacity(0.2)
                        : const Color(0xFFE3F2FD),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: isDark
                          ? const Color(0xFF1976D2).withOpacity(0.5)
                          : const Color(0xFFBBDEFB),
                    ),
                  ),
                  child: const Text('kg',
                      style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF1976D2))),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              '*Data ini akan digunakan untuk menghitung total bulanan',
              style: TextStyle(fontSize: 11, color: subColor),
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: Consumer<CommodityProvider>(
                builder: (_, prov, __) => ElevatedButton.icon(
                  onPressed: prov.isLoading ? null : _hitungSimulasi,
                  icon: const Icon(Icons.calculate_outlined, size: 18),
                  label: const Text('Hitung Estimasi',
                      style: TextStyle(fontSize: 15)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1976D2),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                    elevation: 2,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── AI INSIGHT ──
  Widget _buildAIInsight(bool isDark) {
    final bgColor = isDark
        ? const Color(0xFF1B5E20).withOpacity(0.2)
        : const Color(0xFFE8F5E9);
    final borderColor = isDark
        ? const Color(0xFF2E7D32).withOpacity(0.5)
        : const Color(0xFFA5D6A7);
    final titleColor =
        isDark ? const Color(0xFF81C784) : const Color(0xFF1B5E20);
    final textColor =
        isDark ? const Color(0xFF81C784) : const Color(0xFF2E7D32);
    final iconBg = isDark
        ? const Color(0xFF43A047).withOpacity(0.2)
        : const Color(0xFF43A047).withOpacity(0.15);

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: borderColor),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(color: iconBg, shape: BoxShape.circle),
            child: Icon(Icons.info_outline, color: textColor, size: 18),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Wawasan AI',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        color: titleColor)),
                const SizedBox(height: 4),
                Text(_wawasanAI,
                    style:
                        TextStyle(fontSize: 12, color: textColor, height: 1.5)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── PRICE GRID ── (gradient, tidak perlu isDark)
  Widget _buildPriceGrid() {
    return Row(
      children: [
        Expanded(child: _buildPriceCard(isPredict: false)),
        const SizedBox(width: 12),
        Expanded(child: _buildPriceCard(isPredict: true)),
      ],
    );
  }

  Widget _buildPriceCard({required bool isPredict}) {
    final harga = isPredict ? _hargaPrediksi : _hargaTerbaru;
    final total = isPredict ? _totalPrediksi : _totalSekarang;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: isPredict
              ? [const Color(0xFF0277BD), const Color(0xFF03A9F4)]
              : [const Color(0xFF1565C0), const Color(0xFF1E88E5)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color:
                (isPredict ? const Color(0xFF0288D1) : const Color(0xFF1565C0))
                    .withOpacity(0.3),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (isPredict)
            Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.25),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Text('AI Prediction',
                  style: TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                      fontWeight: FontWeight.bold)),
            ),
          Text(isPredict ? 'Prediksi Bulan Depan' : 'Harga Saat Ini',
              style: const TextStyle(color: Colors.white70, fontSize: 11)),
          const SizedBox(height: 6),
          Text(harga != null ? _fmt.format(harga) : 'Rp -',
              style: const TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.bold)),
          const Text('/kg',
              style: TextStyle(color: Colors.white60, fontSize: 11)),
          const SizedBox(height: 10),
          const Divider(color: Colors.white24, height: 1),
          const SizedBox(height: 8),
          Text(
              isPredict ? 'Estimasi Bulan Depan' : 'Total Pengeluaran Sekarang',
              style: const TextStyle(color: Colors.white70, fontSize: 10)),
          Text(total != null ? '${_fmt.format(total)}/bln' : 'Rp -/bln',
              style: const TextStyle(
                  color: Colors.white,
                  fontSize: 13,
                  fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }

  // ── BUDGET CARD ──
  Widget _buildBudgetCard(bool isDark) {
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;
    final titleColor = isDark ? Colors.white : const Color(0xFF1A237E);
    final subColor = isDark ? Colors.grey[400]! : Colors.grey;
    final konsumsiText =
        _konsumsiController.text.isNotEmpty ? _konsumsiController.text : '0.5';
    final selisih = _selisih ?? 0;
    final pct = _changePercent ?? 0;
    final isUp = selisih >= 0;

    return Container(
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
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Ringkasan Anggaran',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: titleColor)),
                    Text(
                      'Berdasarkan konsumsi $konsumsiText kg per minggu',
                      style: TextStyle(fontSize: 11, color: subColor),
                    ),
                  ],
                ),
                IconButton(
                  icon: const Icon(Icons.file_present_outlined,
                      color: Color(0xFF1976D2)),
                  tooltip: 'Unduh Ringkasan',
                  onPressed: () => _showSnack('Fitur unduh akan segera hadir'),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Divider(
                height: 1,
                color: isDark ? Colors.grey.shade800 : Colors.grey.shade200),
            const SizedBox(height: 14),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Selisih Pengeluaran',
                          style: TextStyle(fontSize: 12, color: subColor)),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          Icon(
                            isUp ? Icons.trending_up : Icons.trending_down,
                            color: isUp ? Colors.red : Colors.green,
                            size: 18,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            '${isUp ? '+' : '-'} ${_fmt.format(selisih.abs())}',
                            style: TextStyle(
                              color: isUp ? Colors.red : Colors.green,
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      RichText(
                        text: TextSpan(
                          style: TextStyle(fontSize: 11, color: subColor),
                          children: [
                            const TextSpan(
                                text: 'Peningkatan biaya estimasi sekitar '),
                            TextSpan(
                              text: '${pct.toStringAsFixed(1)}%',
                              style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.red),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Rekomendasi Tindakan',
                          style: TextStyle(fontSize: 12, color: subColor)),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          _actionBtn('Stok Lebih Awal',
                              isPrimary: true, isDark: isDark),
                          _actionBtn('Cari Promo',
                              isPrimary: false, isDark: isDark),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            _buildMiniChart(isDark),
          ],
        ),
      ),
    );
  }

  Widget _actionBtn(String label,
      {required bool isPrimary, required bool isDark}) {
    return GestureDetector(
      onTap: () {},
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          color: isPrimary
              ? const Color(0xFF1976D2)
              : (isDark
                  ? const Color(0xFF1976D2).withOpacity(0.2)
                  : const Color(0xFFE3F2FD)),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isPrimary ? Colors.white : const Color(0xFF1976D2),
            fontSize: 11,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
    );
  }

  Widget _buildMiniChart(bool isDark) {
    final gridColor = isDark ? Colors.grey.shade800 : const Color(0xFFE5EAF3);
    final labelColor = isDark ? Colors.grey[500]! : Colors.grey;

    // Bangun label bulan dinamis tanpa locale khusus
    const bulan = [
      'JAN',
      'FEB',
      'MAR',
      'APR',
      'MEI',
      'JUN',
      'JUL',
      'AGU',
      'SEP',
      'OKT',
      'NOV',
      'DES'
    ];
    final now = DateTime.now();
    String monthLabel(int offsetMonths) {
      final d = DateTime(now.year, now.month + offsetMonths);
      return bulan[(d.month - 1) % 12];
    }

    final labels = [
      monthLabel(-3),
      monthLabel(-2),
      monthLabel(-1),
      monthLabel(0),
      '${monthLabel(1)} →',
    ];

    // Kumpulkan semua harga untuk diteruskan ke painter
    final allPrices = [
      ..._historicalPrices,
      if (_hargaTerbaru != null) _hargaTerbaru!,
      if (_hargaPrediksi != null) _hargaPrediksi!,
    ];

    return Column(
      children: [
        SizedBox(
          height: 80,
          child: CustomPaint(
            painter: _MiniChartPainter(
              gridColor: gridColor,
              historicalPrices: _historicalPrices,
              currentPrice: _hargaTerbaru,
              predictedPrice: _hargaPrediksi,
            ),
            child: const SizedBox.expand(),
          ),
        ),
        const SizedBox(height: 6),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: labels
              .map((l) => Text(
                    l,
                    style: TextStyle(
                      fontSize: 10,
                      color: labelColor,
                      fontWeight:
                          l.contains('→') ? FontWeight.bold : FontWeight.normal,
                    ),
                  ))
              .toList(),
        ),
        const SizedBox(height: 4),
        Text(
          allPrices.isEmpty
              ? 'Grafik akan muncul setelah simulasi dihitung'
              : 'Grafik Tren Harga 4 Bulan Terakhir & Prediksi',
          style: TextStyle(fontSize: 11, color: labelColor),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }
}

// ── MINI CHART PAINTER ──
class _MiniChartPainter extends CustomPainter {
  final Color gridColor;

  /// 3 harga historis (bulan lalu, 2 bln lalu, 3 bln lalu).
  /// Boleh kosong — painter akan render placeholder flat line.
  final List<double> historicalPrices;

  /// Harga terkini (titik aktual terakhir).
  final double? currentPrice;

  /// Harga prediksi bulan depan.
  final double? predictedPrice;

  const _MiniChartPainter({
    required this.gridColor,
    this.historicalPrices = const [],
    this.currentPrice,
    this.predictedPrice,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;

    // ── Grid lines ──
    final gridPaint = Paint()
      ..color = gridColor
      ..strokeWidth = 1;
    for (final y in [h * .22, h * .5, h * .78]) {
      canvas.drawLine(Offset(0, y), Offset(w, y), gridPaint);
    }

    // Jika belum ada data, render placeholder flat line di tengah
    if (currentPrice == null) {
      canvas.drawLine(
        Offset(0, h * .5),
        Offset(w, h * .5),
        Paint()
          ..color = gridColor
          ..strokeWidth = 2
          ..strokeCap = StrokeCap.round,
      );
      return;
    }

    // ── Normalisasi harga ke koordinat canvas ──
    // Titik aktual = historicalPrices + currentPrice (maks 4 titik)
    final actualValues = [...historicalPrices, currentPrice!];

    // Semua nilai untuk menentukan skala min/max
    final allValues = [
      ...actualValues,
      if (predictedPrice != null) predictedPrice!,
    ];
    final minVal = allValues.reduce((a, b) => a < b ? a : b);
    final maxVal = allValues.reduce((a, b) => a > b ? a : b);

    // Padding vertikal agar titik tidak mepet tepi
    const vPad = 0.12;
    final range = (maxVal - minVal).abs();
    // Jika semua harga sama, beri range artifisial agar grafik tidak flat
    final effectiveRange = range < 1 ? currentPrice! * 0.05 : range;

    double toY(double val) {
      final norm = (val - minVal) / effectiveRange;
      // Flip: nilai tinggi = y kecil (atas canvas)
      return h * (1 - vPad) - norm * h * (1 - vPad * 2);
    }

    // Titik aktual terdistribusi merata di separuh kiri canvas (0 – w*0.5)
    final nActual = actualValues.length;
    final actualPoints = List.generate(nActual, (i) {
      final x = (w * 0.5) * i / (nActual - 1);
      return Offset(x, toY(actualValues[i]));
    });

    // Titik prediksi: mulai dari currentPrice, lanjut ke predictedPrice
    final predictPoints = <Offset>[
      actualPoints.last, // titik sambungan
      if (predictedPrice != null) Offset(w, toY(predictedPrice!)),
    ];

    // ── Area aktual ──
    final areaActualPath = Path()
      ..moveTo(actualPoints.first.dx, actualPoints.first.dy);
    _addCurve(areaActualPath, actualPoints);
    areaActualPath
      ..lineTo(actualPoints.last.dx, h)
      ..lineTo(actualPoints.first.dx, h)
      ..close();
    canvas.drawPath(
      areaActualPath,
      Paint()
        ..shader = LinearGradient(
          colors: [
            const Color(0xFF2563EB).withOpacity(.18),
            const Color(0xFF2563EB).withOpacity(0),
          ],
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
        ).createShader(Rect.fromLTWH(0, 0, w, h)),
    );

    // ── Garis aktual ──
    final linePath = Path()
      ..moveTo(actualPoints.first.dx, actualPoints.first.dy);
    _addCurve(linePath, actualPoints);
    canvas.drawPath(
      linePath,
      Paint()
        ..color = const Color(0xFF2563EB)
        ..strokeWidth = 2
        ..style = PaintingStyle.stroke
        ..strokeCap = StrokeCap.round,
    );

    // ── Area prediksi ──
    if (predictPoints.length >= 2) {
      final areaPredictPath = Path()
        ..moveTo(predictPoints.first.dx, predictPoints.first.dy);
      _addCurve(areaPredictPath, predictPoints);
      areaPredictPath
        ..lineTo(predictPoints.last.dx, h)
        ..lineTo(predictPoints.first.dx, h)
        ..close();
      canvas.drawPath(
        areaPredictPath,
        Paint()
          ..shader = LinearGradient(
            colors: [
              const Color(0xFF0EA5E9).withOpacity(.22),
              const Color(0xFF0EA5E9).withOpacity(0),
            ],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ).createShader(Rect.fromLTWH(0, 0, w, h)),
      );

      _drawDashedPath(canvas, predictPoints, const Color(0xFF0EA5E9));
    }

    // ── Garis pemisah aktual / prediksi ──
    canvas.drawLine(
      Offset(w * .5, h * .1),
      Offset(w * .5, h * .88),
      Paint()
        ..color = gridColor
        ..strokeWidth = 1
        ..style = PaintingStyle.stroke,
    );

    // ── Titik aktual ──
    for (int i = 0; i < actualPoints.length; i++) {
      final r = i == actualPoints.length - 1 ? 4.5 : 3.5;
      canvas.drawCircle(
          actualPoints[i], r, Paint()..color = const Color(0xFF2563EB));
    }

    // ── Titik prediksi (hollow) ──
    for (int i = 1; i < predictPoints.length; i++) {
      canvas.drawCircle(
          predictPoints[i],
          3.5,
          Paint()
            ..color = Colors.white
            ..style = PaintingStyle.fill);
      canvas.drawCircle(
          predictPoints[i],
          3.5,
          Paint()
            ..color = const Color(0xFF0EA5E9)
            ..style = PaintingStyle.stroke
            ..strokeWidth = 1.5);
    }
  }

  void _addCurve(Path path, List<Offset> pts) {
    for (int i = 0; i < pts.length - 1; i++) {
      final p0 = pts[i];
      final p1 = pts[i + 1];
      final cx = (p0.dx + p1.dx) / 2;
      path.cubicTo(cx, p0.dy, cx, p1.dy, p1.dx, p1.dy);
    }
  }

  void _drawDashedPath(Canvas canvas, List<Offset> pts, Color color) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = 2
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    final fullPath = Path()..moveTo(pts.first.dx, pts.first.dy);
    _addCurve(fullPath, pts);

    const dashLen = 6.0;
    const gapLen = 4.0;
    final metrics = fullPath.computeMetrics();
    for (final metric in metrics) {
      double dist = 0;
      while (dist < metric.length) {
        final end = (dist + dashLen).clamp(0.0, metric.length);
        canvas.drawPath(metric.extractPath(dist, end), paint);
        dist += dashLen + gapLen;
      }
    }
  }

  @override
  bool shouldRepaint(covariant _MiniChartPainter old) =>
      old.gridColor != gridColor ||
      old.currentPrice != currentPrice ||
      old.predictedPrice != predictedPrice ||
      old.historicalPrices != historicalPrices;
}
