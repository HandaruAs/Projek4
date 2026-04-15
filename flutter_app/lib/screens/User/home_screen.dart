import 'package:flutter/material.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/screens/User/commodity_detail_screen.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class UserHomeScreen extends StatefulWidget {
  const UserHomeScreen({super.key});

  @override
  State<UserHomeScreen> createState() => _UserHomeScreenState();
}

class _UserHomeScreenState extends State<UserHomeScreen> {
  DateTime _selectedDate = DateTime.now();
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';

  // Pagination
  int _currentPage = 1;
  static const int _itemsPerPage = 10;

  static const Map<String, Color> _categoryColors = {
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

  static const Map<String, IconData> _categoryIcons = {
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
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadCommodities());
    _searchController.addListener(() {
      setState(() {
        _searchQuery = _searchController.text;
        _currentPage = 1; // reset ke halaman 1 saat search berubah
      });
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadCommodities({bool forceReload = false}) async {
    final provider = Provider.of<CommodityProvider>(context, listen: false);
    await provider.loadCommodities(forceReload: forceReload);
  }

  Future<void> _selectDate(BuildContext context) async {
    final provider = Provider.of<CommodityProvider>(context, listen: false);
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );
    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
        _currentPage = 1;
      });
      await provider.loadCommodities(forceReload: true);
    }
  }

  List<CommodityModel> _getPaginatedItems(List<CommodityModel> all) {
    final start = (_currentPage - 1) * _itemsPerPage;
    final end = (start + _itemsPerPage).clamp(0, all.length);
    return all.sublist(start, end);
  }

  int _totalPages(int totalItems) {
    return (totalItems / _itemsPerPage).ceil();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<CommodityProvider>(
      builder: (context, provider, _) {
        final filtered = _searchQuery.isNotEmpty
            ? provider.searchCommodities(_searchQuery)
            : provider.commodities;

        final totalPages = _totalPages(filtered.length);
        final paginated = filtered.isEmpty ? [] : _getPaginatedItems(filtered);

        return RefreshIndicator(
          onRefresh: () => _loadCommodities(forceReload: true),
          child: CustomScrollView(
            slivers: [

              // ── HEADER ──
              SliverToBoxAdapter(
                child: Container(
                  padding: const EdgeInsets.fromLTRB(16, 48, 16, 16),
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Color(0xFF1565C0), Color(0xFF1976D2)],
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.calendar_today,
                              color: Colors.white70, size: 14),
                          const SizedBox(width: 6),
                          GestureDetector(
                            onTap: () => _selectDate(context),
                            child: Text(
                              DateFormat('dd MMM yyyy').format(_selectedDate),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _searchController,
                        style: const TextStyle(fontSize: 14),
                        decoration: InputDecoration(
                          hintText: 'Cari komoditas...',
                          hintStyle: TextStyle(color: Colors.grey[400]),
                          prefixIcon: const Icon(Icons.search, size: 20),
                          filled: true,
                          fillColor: Colors.white,
                          contentPadding: const EdgeInsets.symmetric(
                              vertical: 0, horizontal: 12),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: BorderSide.none,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // ── INFO BAR ──
              if (!provider.isLoading && filtered.isNotEmpty)
                SliverToBoxAdapter(
                  child: Padding(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          '${filtered.length} komoditas ditemukan',
                          style: TextStyle(
                              fontSize: 12, color: Colors.grey[600]),
                        ),
                        Text(
                          'Halaman $_currentPage / $totalPages',
                          style: TextStyle(
                              fontSize: 12, color: Colors.grey[600]),
                        ),
                      ],
                    ),
                  ),
                ),

              // ── LOADING ──
              if (provider.isLoading)
                const SliverFillRemaining(
                  child: Center(child: LoadingWidget()),
                )

              // ── EMPTY ──
              else if (filtered.isEmpty)
                const SliverFillRemaining(
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.search_off,
                            size: 48, color: Colors.grey),
                        SizedBox(height: 8),
                        Text('Tidak ada data',
                            style: TextStyle(color: Colors.grey)),
                      ],
                    ),
                  ),
                )

              // ── GRID ──
              else
                SliverPadding(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 12, vertical: 8),
                  sliver: SliverGrid(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        final commodity = paginated[index];
                        final color = _getCategoryColor(commodity.category);
                        final icon = _getCategoryIcon(commodity.category);

                        return GestureDetector(
                          onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => CommodityDetailScreen(
                                commodityId: commodity.id,
                              ),
                            ),
                          ),
                          child: Card(
                            elevation: 2,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: color.withOpacity(0.12),
                                      shape: BoxShape.circle,
                                    ),
                                    child: Icon(icon, color: color, size: 26),
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    commodity.name,
                                    textAlign: TextAlign.center,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 8, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: color.withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(20),
                                    ),
                                    child: Text(
                                      commodity.category,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: TextStyle(
                                        fontSize: 10,
                                        color: color,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      },
                      childCount: paginated.length,
                    ),
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 8,
                      mainAxisSpacing: 8,
                      childAspectRatio: 1.0,
                    ),
                  ),
                ),

              // ── PAGINATION CONTROLS ──
              if (!provider.isLoading && totalPages > 1)
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                        vertical: 16, horizontal: 12),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [

                        // Tombol Prev
                        IconButton(
                          onPressed: _currentPage > 1
                              ? () => setState(() => _currentPage--)
                              : null,
                          icon: const Icon(Icons.chevron_left),
                          style: IconButton.styleFrom(
                            backgroundColor: _currentPage > 1
                                ? const Color(0xFF1976D2)
                                : Colors.grey[200],
                            foregroundColor: _currentPage > 1
                                ? Colors.white
                                : Colors.grey,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        ),

                        const SizedBox(width: 8),

                        // Nomor halaman
                        ...List.generate(totalPages, (i) {
                          final page = i + 1;
                          final isActive = page == _currentPage;

                          // Tampilkan hanya halaman yang dekat current
                          if (totalPages > 5 &&
                              page != 1 &&
                              page != totalPages &&
                              (page - _currentPage).abs() > 1) {
                            if (page == 2 && _currentPage > 3) {
                              return const Padding(
                                padding: EdgeInsets.symmetric(horizontal: 2),
                                child: Text('...',
                                    style: TextStyle(color: Colors.grey)),
                              );
                            }
                            if (page == totalPages - 1 &&
                                _currentPage < totalPages - 2) {
                              return const Padding(
                                padding: EdgeInsets.symmetric(horizontal: 2),
                                child: Text('...',
                                    style: TextStyle(color: Colors.grey)),
                              );
                            }
                            return const SizedBox.shrink();
                          }

                          return Padding(
                            padding:
                                const EdgeInsets.symmetric(horizontal: 3),
                            child: InkWell(
                              onTap: () =>
                                  setState(() => _currentPage = page),
                              borderRadius: BorderRadius.circular(8),
                              child: Container(
                                width: 36,
                                height: 36,
                                alignment: Alignment.center,
                                decoration: BoxDecoration(
                                  color: isActive
                                      ? const Color(0xFF1976D2)
                                      : Colors.grey[100],
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  '$page',
                                  style: TextStyle(
                                    fontWeight: FontWeight.w600,
                                    fontSize: 13,
                                    color: isActive
                                        ? Colors.white
                                        : Colors.grey[700],
                                  ),
                                ),
                              ),
                            ),
                          );
                        }),

                        const SizedBox(width: 8),

                        // Tombol Next
                        IconButton(
                          onPressed: _currentPage < totalPages
                              ? () => setState(() => _currentPage++)
                              : null,
                          icon: const Icon(Icons.chevron_right),
                          style: IconButton.styleFrom(
                            backgroundColor: _currentPage < totalPages
                                ? const Color(0xFF1976D2)
                                : Colors.grey[200],
                            foregroundColor: _currentPage < totalPages
                                ? Colors.white
                                : Colors.grey,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

              // Bottom padding
              const SliverToBoxAdapter(child: SizedBox(height: 24)),
            ],
          ),
        );
      },
    );
  }
}