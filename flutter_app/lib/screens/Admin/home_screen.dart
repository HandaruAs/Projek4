import 'package:flutter/material.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/screens/Admin/commodity_detail_screen.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  DateTime _selectedDate = DateTime.now();
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';

  static const Map<String, Color> _categoryColors = {
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

  static const Map<String, IconData> _categoryIcons = {
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

  Color _getCategoryColor(String category) {
    final key = category.toLowerCase();
    for (final entry in _categoryColors.entries) {
      if (key.contains(entry.key)) return entry.value;
    }
    return const Color(0xFF1976D2);
  }

  IconData _getCategoryIcon(String category) {
    final key = category.toLowerCase();
    for (final entry in _categoryIcons.entries) {
      if (key.contains(entry.key)) return entry.value;
    }
    return Icons.inventory_2;
  }

  @override
  void initState() {
    super.initState();
    // Simpan provider sebelum async gap
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadCommodities());
    _searchController.addListener(() {
      setState(() => _searchQuery = _searchController.text);
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadCommodities({bool forceReload = false}) async {
    if (!mounted) return;
    // Simpan provider sebelum await untuk menghindari BuildContext async gap
    final provider = Provider.of<CommodityProvider>(context, listen: false);
    await provider.loadCommodities(forceReload: forceReload);
  }

  Future<void> _selectDate(BuildContext context) async {
    // Simpan provider sebelum await untuk menghindari BuildContext async gap
    final provider = Provider.of<CommodityProvider>(context, listen: false);

    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
      builder: (context, child) => Theme(
        data: Theme.of(context).copyWith(
          colorScheme: const ColorScheme.light(primary: Color(0xFF1976D2)),
        ),
        child: child!,
      ),
    );

    if (picked != null && picked != _selectedDate) {
      if (!mounted) return;
      setState(() => _selectedDate = picked);
      await provider.loadCommodities(forceReload: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<CommodityProvider>(
      builder: (context, provider, _) {
        final List<CommodityModel> filtered = _searchQuery.isNotEmpty
            ? provider.searchCommodities(_searchQuery)
            : provider.commodities;

        final totalKomoditas = provider.commodities.length;
        final Map<String, int> categoryCounts = {};
        for (final c in provider.commodities) {
          if (c.category.isNotEmpty) {
            categoryCounts[c.category] =
                (categoryCounts[c.category] ?? 0) + 1;
          }
        }
        final topCategory = categoryCounts.isNotEmpty
            ? (categoryCounts.entries.toList()
                  ..sort((a, b) => b.value.compareTo(a.value)))
                .first
            : null;

        return RefreshIndicator(
          onRefresh: () => _loadCommodities(forceReload: true),
          color: const Color(0xFF1976D2),
          child: CustomScrollView(
            slivers: [

              // ── Header ──────────────────────────────────
              SliverToBoxAdapter(
                child: Container(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Color(0xFF1565C0), Color(0xFF1976D2)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.vertical(
                      bottom: Radius.circular(28),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          _headerChip(
                            Icons.calendar_today,
                            DateFormat('dd MMM yyyy').format(_selectedDate),
                            onTap: () => _selectDate(context),
                          ),
                          const SizedBox(width: 10),
                          _headerChip(Icons.location_on, 'Jember'),
                        ],
                      ),
                      const SizedBox(height: 16),

                      if (!provider.isLoading && totalKomoditas > 0)
                        Row(
                          children: [
                            Expanded(
                              child: _summaryCard(
                                label: 'Total Komoditas',
                                value: '$totalKomoditas',
                                subtitle: 'terdaftar',
                                icon: Icons.inventory_2,
                                color: Colors.lightBlue.shade200,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _summaryCard(
                                label: 'Total Kategori',
                                value: '${categoryCounts.length}',
                                subtitle: 'kategori',
                                icon: Icons.category,
                                color: Colors.orange.shade200,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _summaryCard(
                                label: 'Terbanyak',
                                value: '${topCategory?.value ?? 0}',
                                subtitle: topCategory?.key ?? '-',
                                icon: Icons.star,
                                color: Colors.yellow.shade300,
                              ),
                            ),
                          ],
                        ),
                    ],
                  ),
                ),
              ),

              // ── Search ──────────────────────────────────
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'Cari komoditas...',
                      prefixIcon: const Icon(Icons.search),
                      suffixIcon: _searchQuery.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.close),
                              onPressed: () => _searchController.clear(),
                            )
                          : const Icon(Icons.tune),
                      filled: true,
                      fillColor: Colors.grey.shade50,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: BorderSide(color: Colors.grey.shade200),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: BorderSide(color: Colors.grey.shade200),
                      ),
                    ),
                  ),
                ),
              ),

              // ── Content ──────────────────────────────────
              if (provider.isLoading)
                const SliverFillRemaining(
                  child: Center(child: LoadingWidget()),
                )
              else if (provider.errorMessage != null)
                SliverFillRemaining(
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.wifi_off,
                            size: 64, color: Colors.grey.shade400),
                        const SizedBox(height: 16),
                        Text(
                          provider.errorMessage!,
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.grey.shade600),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () => _loadCommodities(forceReload: true),
                          icon: const Icon(Icons.refresh),
                          label: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  ),
                )
              else if (filtered.isEmpty)
                SliverFillRemaining(
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.inbox,
                            size: 64, color: Colors.grey.shade400),
                        const SizedBox(height: 12),
                        Text(
                          _searchQuery.isNotEmpty
                              ? '"$_searchQuery" tidak ditemukan'
                              : 'Tidak ada data komoditas',
                          style: TextStyle(
                              fontSize: 15, color: Colors.grey.shade600),
                        ),
                      ],
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                  sliver: SliverGrid(
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 12,
                      childAspectRatio: 1.15,
                    ),
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        final commodity = filtered[index];
                        final color = _getCategoryColor(commodity.category);
                        final icon  = _getCategoryIcon(commodity.category);

                        return GestureDetector(
                          onTap: () => Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => CommodityDetailScreen(
                                  commodityId: commodity.id),
                            ),
                          ),
                          child: Container(
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.06),
                                  blurRadius: 8,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Padding(
                              padding: const EdgeInsets.all(14),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    width: 42,
                                    height: 42,
                                    decoration: BoxDecoration(
                                      color: color.withOpacity(0.12),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: Icon(icon, color: color, size: 22),
                                  ),
                                  const Spacer(),
                                  Text(
                                    commodity.name,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 13,
                                      color: Color(0xFF1A1A2E),
                                    ),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 6),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 8, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: color.withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(20),
                                    ),
                                    child: Text(
                                      commodity.category,
                                      style: TextStyle(
                                        fontSize: 10,
                                        color: color,
                                        fontWeight: FontWeight.w600,
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      },
                      childCount: filtered.length,
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  Widget _headerChip(IconData icon, String label, {VoidCallback? onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.2),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Row(
          children: [
            Icon(icon, size: 14, color: Colors.white),
            const SizedBox(width: 6),
            Text(
              label,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _summaryCard({
    required String label,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.15),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 18),
          const SizedBox(height: 8),
          Text(
            value,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w800,
              fontSize: 20,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: TextStyle(
              color: Colors.white.withOpacity(0.85),
              fontSize: 10,
              fontWeight: FontWeight.w500,
            ),
          ),
          Text(
            subtitle,
            style: TextStyle(
              color: Colors.white.withOpacity(0.65),
              fontSize: 10,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}