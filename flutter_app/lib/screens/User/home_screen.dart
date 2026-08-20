import 'package:flutter/material.dart';
import 'package:flutter_app/models/price_latest_model.dart';
import 'package:flutter_app/providers/price_provider.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class UserHomeScreen extends StatefulWidget {
  final void Function(String commodityId)? onOpenCommodity;

  const UserHomeScreen({super.key, this.onOpenCommodity});

  @override
  State<UserHomeScreen> createState() => _UserHomeScreenState();
}

class _UserHomeScreenState extends State<UserHomeScreen> {
  final TextEditingController _searchController = TextEditingController();
  final NumberFormat _rupiahFmt =
      NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  String _searchQuery = '';
  String _selectedCategory = 'Semua';
  int _currentPage = 1;
  static const int _perPage = 10;

  static const Map<String, Color> _catColors = {
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

  static const Map<String, IconData> _catIcons = {
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

  Color _color(String cat) {
    final k = cat.toLowerCase();
    for (final e in _catColors.entries) {
      if (k.contains(e.key)) return e.value;
    }
    return const Color(0xFF1976D2);
  }

  IconData _icon(String cat) {
    final k = cat.toLowerCase();
    for (final e in _catIcons.entries) {
      if (k.contains(e.key)) return e.value;
    }
    return Icons.inventory_2;
  }

  void _onTapCommodity(String commodityId) =>
      widget.onOpenCommodity?.call(commodityId);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadAll());
    _searchController.addListener(() => setState(() {
          _searchQuery = _searchController.text;
          _currentPage = 1;
        }));
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadAll({bool force = false}) async {
    final p = context.read<PriceProvider>();
    await Future.wait([
      p.loadLatestPrices(forceReload: force),
      p.loadTopPrices(),
      p.loadCategories(),
    ]);
  }

  List<PriceLatestModel> _filtered(PriceProvider p) {
    var list = p.latestPrices;
    if (_selectedCategory != 'Semua') {
      list = list
          .where((x) =>
              x.category.toLowerCase() == _selectedCategory.toLowerCase())
          .toList();
    }
    if (_searchQuery.isNotEmpty) {
      list = list
          .where((x) => x.commodityName
              .toLowerCase()
              .contains(_searchQuery.toLowerCase()))
          .toList();
    }
    return list;
  }

  List<PriceLatestModel> _paginated(List<PriceLatestModel> all) {
    final start = (_currentPage - 1) * _perPage;
    final end = (start + _perPage).clamp(0, all.length);
    if (start >= all.length) return [];
    return all.sublist(start, end);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Consumer<PriceProvider>(
      builder: (context, provider, _) {
        final filtered = _filtered(provider);
        final paginated = _paginated(filtered);
        final totalPages = (filtered.length / _perPage).ceil();

        return RefreshIndicator(
          onRefresh: () => _loadAll(force: true),
          child: CustomScrollView(
            slivers: [
              SliverToBoxAdapter(child: _buildHeader(isDark)),
              if (provider.categories.isNotEmpty)
                SliverToBoxAdapter(
                    child: _buildCategoryChips(provider.categories)),
              SliverToBoxAdapter(
                  child: _buildFeaturedSection(provider, isDark)),
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 6),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Semua Komoditas',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color:
                                isDark ? Colors.white : const Color(0xFF1A1A2E),
                          )),
                      if (!provider.isLoading)
                        Text('${filtered.length} item',
                            style: TextStyle(
                                fontSize: 12, color: Colors.grey[500])),
                    ],
                  ),
                ),
              ),
              if (provider.isLoading)
                const SliverFillRemaining(child: Center(child: LoadingWidget()))
              else if (filtered.isEmpty)
                SliverFillRemaining(
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.search_off,
                            size: 48, color: Colors.grey[400]),
                        const SizedBox(height: 8),
                        Text('Tidak ada data komoditas',
                            style: TextStyle(color: Colors.grey[500])),
                      ],
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.symmetric(horizontal: 14),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, i) => _buildListItem(paginated[i], isDark),
                      childCount: paginated.length,
                    ),
                  ),
                ),
              if (!provider.isLoading && totalPages > 1)
                SliverToBoxAdapter(child: _buildPagination(totalPages, isDark)),
              const SliverToBoxAdapter(child: SizedBox(height: 32)),
            ],
          ),
        );
      },
    );
  }

  // ── Header ───────────────────────────────────────────────────────────────

  Widget _buildHeader(bool isDark) {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 48, 16, 16),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF1565C0), Color(0xFF1976D2)],
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.storefront, color: Colors.white70, size: 14),
              const SizedBox(width: 6),
              Text('Harga Pasar Hari Ini',
                  style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.85),
                      fontSize: 12)),
              const Spacer(),
              Row(children: [
                const Icon(Icons.calendar_today,
                    color: Colors.white54, size: 12),
                const SizedBox(width: 4),
                Text(DateFormat('dd MMM yyyy').format(DateTime.now()),
                    style:
                        const TextStyle(color: Colors.white70, fontSize: 12)),
              ]),
            ],
          ),
          const SizedBox(height: 6),
          const Text('Pantau Harga Komoditas',
              style: TextStyle(
                  color: Colors.white,
                  fontSize: 20,
                  fontWeight: FontWeight.w600)),
          const SizedBox(height: 14),
          Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF2C2C2C) : Colors.white,
              borderRadius: BorderRadius.circular(12),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
            child: Row(
              children: [
                Icon(Icons.search, color: Colors.grey[500], size: 18),
                const SizedBox(width: 8),
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    style: TextStyle(
                        fontSize: 13,
                        color: isDark ? Colors.white : Colors.black),
                    decoration: InputDecoration(
                      hintText: 'Cari komoditas...',
                      hintStyle:
                          TextStyle(color: Colors.grey[500], fontSize: 13),
                      border: InputBorder.none,
                      isDense: true,
                      contentPadding: const EdgeInsets.symmetric(vertical: 10),
                    ),
                  ),
                ),
                if (_searchQuery.isNotEmpty)
                  GestureDetector(
                    onTap: () {
                      _searchController.clear();
                      setState(() {
                        _searchQuery = '';
                        _currentPage = 1;
                      });
                    },
                    child: Icon(Icons.close, size: 16, color: Colors.grey[500]),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Category chips ───────────────────────────────────────────────────────

  Widget _buildCategoryChips(List<String> cats) {
    return Container(
      color: const Color(0xFF1565C0),
      padding: const EdgeInsets.only(bottom: 12, top: 4),
      child: SizedBox(
        height: 34,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 14),
          itemCount: cats.length,
          separatorBuilder: (_, __) => const SizedBox(width: 8),
          itemBuilder: (_, i) {
            final cat = cats[i];
            final isActive = cat == _selectedCategory;
            return GestureDetector(
              onTap: () => setState(() {
                _selectedCategory = cat;
                _currentPage = 1;
              }),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                decoration: BoxDecoration(
                  color: isActive
                      ? Colors.white
                      : Colors.white.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isActive
                        ? Colors.white
                        : Colors.white.withValues(alpha: 0.3),
                    width: 0.5,
                  ),
                ),
                child: Text(cat,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
                      color: isActive ? const Color(0xFF1565C0) : Colors.white,
                    )),
              ),
            );
          },
        ),
      ),
    );
  }

  // ── Featured section ─────────────────────────────────────────────────────

  Widget _buildFeaturedSection(PriceProvider provider, bool isDark) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 10),
          child: Text('Harga Tertinggi Hari Ini',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: isDark ? Colors.white : const Color(0xFF1A1A2E),
              )),
        ),
        if (provider.isLoadingTop)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
          )
        else if (provider.topPrices.isEmpty)
          const SizedBox.shrink()
        else
          SizedBox(
            height: 170,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              itemCount: provider.topPrices.length,
              separatorBuilder: (_, __) => const SizedBox(width: 10),
              itemBuilder: (_, i) =>
                  _buildFeaturedCard(provider.topPrices[i], isDark),
            ),
          ),
        const SizedBox(height: 4),
      ],
    );
  }

  Widget _buildFeaturedCard(PriceLatestModel item, bool isDark) {
    final color = _color(item.category);
    final icon = _icon(item.category);
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;

    return GestureDetector(
      onTap: () => _onTapCommodity(item.commodityId),
      child: Container(
        width: 148,
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: cardBg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withValues(alpha: 0.2), width: 0.8),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.06),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Icon + Badge status ──────────────────────────────
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.all(7),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, color: color, size: 16),
                ),
                if (item.isPrediction) _buildStatusBadge(item, mini: true),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              item.commodityName,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: isDark ? Colors.white : const Color(0xFF1A1A2E),
              ),
            ),
            const SizedBox(height: 2),
            Text(item.category,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(fontSize: 10, color: Colors.grey[500])),
            const Spacer(),
            Text(
              _rupiahFmt.format(item.hargaSekarang),
              style: TextStyle(
                  fontSize: 13, fontWeight: FontWeight.w700, color: color),
            ),
            const SizedBox(height: 4),
            // ── % perubahan ──────────────────────────────────────
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: item.isNaik
                    ? const Color(0xFF3B6D11)
                        .withValues(alpha: isDark ? 0.3 : 0.1)
                    : item.isTurun
                        ? const Color(0xFFA32D2D)
                            .withValues(alpha: isDark ? 0.3 : 0.1)
                        : Colors.grey.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                item.isNaik
                    ? '▲ ${item.persenFormatted}'
                    : item.isTurun
                        ? '▼ ${item.persenFormatted}'
                        : '— 0%',
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w500,
                  color: item.isNaik
                      ? const Color(0xFF639922)
                      : item.isTurun
                          ? const Color(0xFFE24B4A)
                          : Colors.grey,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── List item ────────────────────────────────────────────────────────────

  Widget _buildListItem(PriceLatestModel item, bool isDark) {
    final color = _color(item.category);
    final icon = _icon(item.category);
    final cardBg = isDark ? const Color(0xFF1E1E1E) : Colors.white;

    return GestureDetector(
      onTap: () => _onTapCommodity(item.commodityId),
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: cardBg,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isDark
                ? Colors.grey.withValues(alpha: 0.2)
                : Colors.grey.withValues(alpha: 0.12),
            width: 0.5,
          ),
        ),
        child: Row(
          children: [
            // ── Icon ────────────────────────────────────────────
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(width: 12),

            // ── Nama + kategori + badge ──────────────────────────
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.commodityName,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: isDark ? Colors.white : const Color(0xFF1A1A2E),
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      Text(item.category,
                          style:
                              TextStyle(fontSize: 11, color: Colors.grey[500])),
                      if (item.isPrediction) ...[
                        const SizedBox(width: 6),
                        _buildStatusBadge(item, mini: true),
                      ],
                    ],
                  ),
                ],
              ),
            ),

            // ── Harga + perubahan ────────────────────────────────
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  _rupiahFmt.format(item.hargaSekarang),
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white : const Color(0xFF1A1A2E),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  item.satuan.isNotEmpty ? '/ ${item.satuan}' : '',
                  style: TextStyle(fontSize: 10, color: Colors.grey[400]),
                ),
                const SizedBox(height: 2),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      item.isNaik
                          ? Icons.arrow_upward
                          : item.isTurun
                              ? Icons.arrow_downward
                              : Icons.remove,
                      size: 11,
                      color: item.isNaik
                          ? const Color(0xFF639922)
                          : item.isTurun
                              ? const Color(0xFFE24B4A)
                              : Colors.grey,
                    ),
                    const SizedBox(width: 2),
                    Text(
                      item.persenFormatted,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                        color: item.isNaik
                            ? const Color(0xFF639922)
                            : item.isTurun
                                ? const Color(0xFFE24B4A)
                                : Colors.grey,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // ── Badge status prediksi ────────────────────────────────────────────────

  Widget _buildStatusBadge(PriceLatestModel item, {bool mini = false}) {
    if (item.tidakAdaData || !item.isPrediction) return const SizedBox.shrink();

    final Color bgColor;
    final Color textColor;
    final IconData badgeIcon;

    switch (item.statusPrediksi) {
      case 'aktif':
        bgColor = const Color(0xFFDCFCE7);
        textColor = const Color(0xFF166534);
        badgeIcon = Icons.check_circle_rounded;
        break;
      case 'kadaluarsa':
        bgColor = const Color(0xFFFEF3C7);
        textColor = const Color(0xFF92400E);
        badgeIcon = Icons.access_time_rounded;
        break;
      case 'belum_mulai':
        bgColor = const Color(0xFFEFF6FF);
        textColor = const Color(0xFF1D4ED8);
        badgeIcon = Icons.calendar_month_rounded;
        break;
      default:
        return const SizedBox.shrink();
    }

    if (mini) {
      // Hanya dot berwarna kecil
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(badgeIcon, size: 9, color: textColor),
            const SizedBox(width: 3),
            Text(
              item.statusLabel,
              style: TextStyle(
                  fontSize: 9, fontWeight: FontWeight.w700, color: textColor),
            ),
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(badgeIcon, size: 11, color: textColor),
          const SizedBox(width: 4),
          Text(
            item.statusLabel,
            style: TextStyle(
                fontSize: 11, fontWeight: FontWeight.w600, color: textColor),
          ),
        ],
      ),
    );
  }

  // ── Pagination ───────────────────────────────────────────────────────────

  Widget _buildPagination(int totalPages, bool isDark) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 14),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          _pgBtn(
              icon: Icons.chevron_left,
              enabled: _currentPage > 1,
              onTap: () => setState(() => _currentPage--),
              isDark: isDark),
          const SizedBox(width: 8),
          ...List.generate(totalPages, (i) {
            final page = i + 1;
            final isActive = page == _currentPage;

            if (totalPages > 5 &&
                page != 1 &&
                page != totalPages &&
                (page - _currentPage).abs() > 1) {
              if (page == 2 && _currentPage > 3) {
                return const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 2),
                  child: Text('...', style: TextStyle(color: Colors.grey)),
                );
              }
              if (page == totalPages - 1 && _currentPage < totalPages - 2) {
                return const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 2),
                  child: Text('...', style: TextStyle(color: Colors.grey)),
                );
              }
              return const SizedBox.shrink();
            }

            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 3),
              child: GestureDetector(
                onTap: () => setState(() => _currentPage = page),
                child: Container(
                  width: 34,
                  height: 34,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: isActive
                        ? const Color(0xFF1976D2)
                        : (isDark ? const Color(0xFF2C2C2C) : Colors.grey[100]),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '$page',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      fontSize: 13,
                      color: isActive
                          ? Colors.white
                          : (isDark ? Colors.grey[300] : Colors.grey[700]),
                    ),
                  ),
                ),
              ),
            );
          }),
          const SizedBox(width: 8),
          _pgBtn(
              icon: Icons.chevron_right,
              enabled: _currentPage < totalPages,
              onTap: () => setState(() => _currentPage++),
              isDark: isDark),
        ],
      ),
    );
  }

  Widget _pgBtn({
    required IconData icon,
    required bool enabled,
    required VoidCallback onTap,
    required bool isDark,
  }) {
    return GestureDetector(
      onTap: enabled ? onTap : null,
      child: Container(
        width: 34,
        height: 34,
        decoration: BoxDecoration(
          color: enabled
              ? const Color(0xFF1976D2)
              : (isDark ? const Color(0xFF2C2C2C) : Colors.grey[200]),
          borderRadius: BorderRadius.circular(8),
        ),
        child:
            Icon(icon, size: 18, color: enabled ? Colors.white : Colors.grey),
      ),
    );
  }
}
