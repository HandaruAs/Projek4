import 'package:flutter/material.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  final List<Map<String, dynamic>> _notifications = [
    {
      'id': 1,
      'icon': Icons.trending_up,
      'color': Colors.red,
      'title': 'Harga Cabai Naik',
      'body': 'Cabe Merah Keriting naik 12% hari ini',
      'time': '10 menit lalu',
      'read': false,
      'category': 'alert',
    },
    {
      'id': 2,
      'icon': Icons.trending_down,
      'color': Colors.green,
      'title': 'Harga Bawang Turun',
      'body': 'Bawang Merah turun 5% dari kemarin',
      'time': '1 jam lalu',
      'read': false,
      'category': 'alert',
    },
    {
      'id': 3,
      'icon': Icons.bar_chart,
      'color': Colors.blue,
      'title': 'Laporan Mingguan Tersedia',
      'body': 'Ringkasan harga komoditas minggu ini sudah siap',
      'time': '2 jam lalu',
      'read': false,
      'category': 'report',
    },
    {
      'id': 4,
      'icon': Icons.info_outline,
      'color': Colors.orange,
      'title': 'Data Diperbarui',
      'body': 'Data harga pasar telah diperbarui untuk hari ini',
      'time': 'Kemarin',
      'read': true,
      'category': 'info',
    },
    {
      'id': 5,
      'icon': Icons.warning_amber,
      'color': Colors.deepOrange,
      'title': 'Stok Menipis',
      'body': 'Beras Medium dilaporkan stok terbatas di beberapa pasar',
      'time': 'Kemarin',
      'read': true,
      'category': 'alert',
    },
    {
      'id': 6,
      'icon': Icons.check_circle_outline,
      'color': Colors.teal,
      'title': 'Prediksi Diperbarui',
      'body': 'Model prediksi harga minggu depan telah diperbarui',
      'time': '2 hari lalu',
      'read': true,
      'category': 'info',
    },
  ];

  String _selectedFilter = 'Semua';
  final List<String> _filters = ['Semua', 'Alert', 'Laporan', 'Info'];

  List<Map<String, dynamic>> get _filtered {
    if (_selectedFilter == 'Semua') return _notifications;
    final map = {'Alert': 'alert', 'Laporan': 'report', 'Info': 'info'};
    return _notifications
        .where((n) => n['category'] == map[_selectedFilter])
        .toList();
  }

  int get _unreadCount =>
      _notifications.where((n) => n['read'] == false).length;

  void _markAllRead() {
    setState(() {
      for (final n in _notifications) {
        n['read'] = true;
      }
    });
  }

  void _markOneRead(int id) {
    setState(() {
      final notif = _notifications.firstWhere((n) => n['id'] == id);
      notif['read'] = true;
    });
  }

  void _deleteOne(int id) {
    setState(() => _notifications.removeWhere((n) => n['id'] == id));
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filtered;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const Text('Notifikasi'),
            if (_unreadCount > 0) ...[
              const SizedBox(width: 8),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.red,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '$_unreadCount',
                  style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ],
        ),
        actions: [
          if (_unreadCount > 0)
            TextButton(
              onPressed: _markAllRead,
              child: const Text('Tandai dibaca',
                  style: TextStyle(fontSize: 13)),
            ),
        ],
      ),
      body: Column(
        children: [
          // ── FILTER CHIPS ──
          SizedBox(
            height: 52,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              itemCount: _filters.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, index) {
                final label = _filters[index];
                final isSelected = _selectedFilter == label;
                return GestureDetector(
                  onTap: () => setState(() => _selectedFilter = label),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 6),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? const Color(0xFF1976D2)
                          : Colors.grey[100],
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      label,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color:
                            isSelected ? Colors.white : Colors.grey[600],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),

          const Divider(height: 1),

          // ── LIST ──
          Expanded(
            child: filtered.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.notifications_off_outlined,
                            size: 56, color: Colors.grey[300]),
                        const SizedBox(height: 12),
                        Text('Tidak ada notifikasi',
                            style: TextStyle(color: Colors.grey[400])),
                      ],
                    ),
                  )
                : ListView.separated(
                    itemCount: filtered.length,
                    separatorBuilder: (_, __) =>
                        const Divider(height: 1, indent: 72),
                    itemBuilder: (context, index) {
                      final n = filtered[index];
                      return Dismissible(
                        key: Key('notif_${n['id']}'),
                        direction: DismissDirection.endToStart,
                        background: Container(
                          alignment: Alignment.centerRight,
                          padding: const EdgeInsets.only(right: 20),
                          color: Colors.red[50],
                          child: const Icon(Icons.delete_outline,
                              color: Colors.red),
                        ),
                        onDismissed: (_) => _deleteOne(n['id'] as int),
                        child: InkWell(
                          onTap: () => _markOneRead(n['id'] as int),
                          child: Container(
                            color: n['read'] == true
                                ? null
                                : const Color(0xFF1976D2).withOpacity(0.04),
                            child: ListTile(
                              contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 6),
                              leading: CircleAvatar(
                                backgroundColor: (n['color'] as Color)
                                    .withOpacity(0.12),
                                child: Icon(n['icon'] as IconData,
                                    color: n['color'] as Color, size: 20),
                              ),
                              title: Text(
                                n['title'] as String,
                                style: TextStyle(
                                  fontWeight: n['read'] == true
                                      ? FontWeight.w500
                                      : FontWeight.w700,
                                  fontSize: 14,
                                ),
                              ),
                              subtitle: Column(
                                crossAxisAlignment:
                                    CrossAxisAlignment.start,
                                children: [
                                  const SizedBox(height: 2),
                                  Text(n['body'] as String,
                                      style: TextStyle(
                                          fontSize: 12,
                                          color: Colors.grey[600])),
                                  const SizedBox(height: 4),
                                  Text(n['time'] as String,
                                      style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey[400])),
                                ],
                              ),
                              trailing: n['read'] == false
                                  ? Container(
                                      width: 8,
                                      height: 8,
                                      decoration: const BoxDecoration(
                                        color: Color(0xFF1976D2),
                                        shape: BoxShape.circle,
                                      ),
                                    )
                                  : null,
                            ),
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}