import 'package:flutter/material.dart';
import 'package:flutter_app/services/api_service.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';

class UserStatisticsScreen extends StatefulWidget {
  const UserStatisticsScreen({super.key});

  @override
  State<UserStatisticsScreen> createState() => _UserStatisticsScreenState();
}

class _UserStatisticsScreenState extends State<UserStatisticsScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  String? _error;
  Map<String, dynamic>? _data;

  final NumberFormat _currencyFormat = NumberFormat.currency(
    locale: 'id',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  @override
  void initState() {
    super.initState();
    _loadStatistics();
  }

  Future<void> _loadStatistics() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final response = await _apiService.getStatistics();
      if (response['success'] == true) {
        setState(() => _data = response['data']);
      } else {
        setState(() => _error = response['message'] ?? 'Gagal memuat statistik');
      }
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;
    final textTheme   = Theme.of(context).textTheme;
    final isDark      = Theme.of(context).brightness == Brightness.dark;

    if (_isLoading) return const Center(child: LoadingWidget());
    if (_error != null) return _buildError();

    final ringkasan   = _data!['ringkasan'];
    final topNaik     = List<Map<String, dynamic>>.from(_data!['top_naik']);
    final topTurun    = List<Map<String, dynamic>>.from(_data!['top_turun']);
    final perKategori = List<Map<String, dynamic>>.from(_data!['per_kategori']);

    final maxRataRata = perKategori.isEmpty ? 1.0 :
        perKategori.map((e) => (e['rata_rata'] as num).toDouble()).reduce((a, b) => a > b ? a : b);

    return RefreshIndicator(
      onRefresh: _loadStatistics,
      color: colorScheme.primary,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [

            // ── Ringkasan ──
            Row(
              children: [
                Expanded(child: _metricCard(
                  label: 'Total Komoditas',
                  value: '${ringkasan['total_komoditas']}',
                  sub: 'terdaftar',
                  color: colorScheme.primary,
                  isDark: isDark,
                )),
                const SizedBox(width: 10),
                Expanded(child: _metricCard(
                  label: 'Harga Naik',
                  value: '${ringkasan['naik']}',
                  sub: 'komoditas',
                  color: Colors.green,
                  isDark: isDark,
                )),
                const SizedBox(width: 10),
                Expanded(child: _metricCard(
                  label: 'Harga Turun',
                  value: '${ringkasan['turun']}',
                  sub: 'komoditas',
                  color: Colors.red,
                  isDark: isDark,
                )),
              ],
            ),

            const SizedBox(height: 24),

            _sectionTitle('Kenaikan Tertinggi', Icons.trending_up, Colors.green, textTheme),
            const SizedBox(height: 10),
            _buildPriceList(topNaik, isUp: true, isDark: isDark),

            const SizedBox(height: 24),

            _sectionTitle('Penurunan Terbesar', Icons.trending_down, Colors.red, textTheme),
            const SizedBox(height: 10),
            _buildPriceList(topTurun, isUp: false, isDark: isDark),

            const SizedBox(height: 24),

            _sectionTitle('Rata-rata harga per kategori', Icons.category, colorScheme.primary, textTheme),
            const SizedBox(height: 10),
            _buildKategoriChart(perKategori, maxRataRata, isDark: isDark, primary: colorScheme.primary),

            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.wifi_off, size: 64, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          Text(_error!, style: TextStyle(color: Colors.grey.shade600)),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: _loadStatistics,
            icon: const Icon(Icons.refresh),
            label: const Text('Coba Lagi'),
          ),
        ],
      ),
    );
  }

  Widget _metricCard({
    required String label,
    required String value,
    required String sub,
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
          Text(label, style: TextStyle(fontSize: 10, color: Colors.grey.shade500)),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: color)),
          Text(sub, style: TextStyle(fontSize: 10, color: Colors.grey.shade500)),
        ],
      ),
    );
  }

  Widget _sectionTitle(String title, IconData icon, Color color, TextTheme textTheme) {
    return Row(
      children: [
        Icon(icon, size: 18, color: color),
        const SizedBox(width: 8),
        Text(title, style: textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w700)),
      ],
    );
  }

  Widget _buildPriceList(List<Map<String, dynamic>> items, {required bool isUp, required bool isDark}) {
    final cardColor = isDark ? const Color(0xFF1E1E1E) : Colors.white;

    if (items.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: cardColor,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Center(child: Text('Tidak ada data', style: TextStyle(color: Colors.grey.shade500))),
      );
    }

    return Container(
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        children: items.asMap().entries.map((entry) {
          final i      = entry.key;
          final item   = entry.value;
          final persen = (item['persen'] as num).toDouble();
          final isLast = i == items.length - 1;

          return Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              border: isLast ? null : Border(
                bottom: BorderSide(color: isDark ? Colors.grey.shade800 : Colors.grey.shade100),
              ),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item['commodity_name'] ?? '-',
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                          color: isDark ? Colors.white : const Color(0xFF1A1A2E),
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        item['category'] ?? '-',
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      _currencyFormat.format(item['harga_sekarang']),
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: isDark ? Colors.white : const Color(0xFF1A1A2E),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: isUp ? Colors.green.withOpacity(0.15) : Colors.red.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '${isUp ? '+' : ''}${persen.toStringAsFixed(1)}%',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: isUp ? Colors.green.shade400 : Colors.red.shade400,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildKategoriChart(List<Map<String, dynamic>> items, double maxVal, {required bool isDark, required Color primary}) {
    if (items.isEmpty) return const SizedBox();

    final cardColor = isDark ? const Color(0xFF1E1E1E) : Colors.white;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        children: items.map((item) {
          final rataRata = (item['rata_rata'] as num).toDouble();
          final ratio    = maxVal > 0 ? rataRata / maxVal : 0.0;

          return Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Row(
              children: [
                SizedBox(
                  width: 90,
                  child: Text(
                    item['category'] ?? '-',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: LinearProgressIndicator(
                      value: ratio,
                      minHeight: 8,
                      backgroundColor: isDark ? Colors.grey.shade800 : Colors.grey.shade100,
                      valueColor: AlwaysStoppedAnimation<Color>(primary),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                SizedBox(
                  width: 65,
                  child: Text(
                    _currencyFormat.format(rataRata),
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: isDark ? Colors.white : const Color(0xFF1A1A2E),
                    ),
                    textAlign: TextAlign.right,
                  ),
                ),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }
}