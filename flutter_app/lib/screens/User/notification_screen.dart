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
      'title': 'Harga Cabai Merah Naik!',
      'body': 'Harga cabai merah naik 12.5% dalam 7 hari terakhir.',
      'type': 'price_alert',
      'commodity': 'Cabai Merah',
      'time': '2 jam lalu',
      'isRead': false,
    },
    {
      'id': 2,
      'title': 'Prediksi Beras Minggu Ini',
      'body':
          'Model memproyeksikan kenaikan harga beras 3.2% dalam 30 hari ke depan.',
      'type': 'prediction',
      'commodity': 'Beras',
      'time': '5 jam lalu',
      'isRead': false,
    },
    {
      'id': 3,
      'title': 'Simulasi Anggaran Tersedia',
      'body': 'Coba simulasi anggaran belanja untuk komoditas minggu ini.',
      'type': 'simulation',
      'commodity': null,
      'time': '1 hari lalu',
      'isRead': false,
    },
    {
      'id': 4,
      'title': 'Harga Minyak Goreng Turun',
      'body': 'Harga minyak goreng turun 5.8% dibanding minggu lalu.',
      'type': 'price_alert',
      'commodity': 'Minyak Goreng',
      'time': '1 hari lalu',
      'isRead': true,
    },
    {
      'id': 5,
      'title': 'Prediksi Gula Pasir',
      'body': 'Harga gula pasir diprediksi stabil selama 2 minggu ke depan.',
      'type': 'prediction',
      'commodity': 'Gula Pasir',
      'time': '2 hari lalu',
      'isRead': true,
    },
  ];

  void _markAllRead() {
    setState(() {
      for (final n in _notifications) {
        n['isRead'] = true;
      }
    });
  }

  void _showActionSheet(Map<String, dynamic> notif) {
    final type = notif['type'] as String;
    final commodity = notif['commodity'] as String?;

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      backgroundColor: Theme.of(context).brightness == Brightness.dark
          ? const Color(0xFF1E1E1E)
          : Colors.white,
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 12),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Handle bar
                Container(
                  width: 36,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(height: 16),

                // Ikon besar
                Container(
                  width: 52,
                  height: 52,
                  decoration: BoxDecoration(
                    color: _typeColor(type).withOpacity(0.12),
                    shape: BoxShape.circle,
                  ),
                  child:
                      Icon(_typeIcon(type), color: _typeColor(type), size: 26),
                ),
                const SizedBox(height: 12),

                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Text(
                    notif['title'],
                    style: const TextStyle(
                        fontWeight: FontWeight.w700, fontSize: 15),
                    textAlign: TextAlign.center,
                  ),
                ),
                const SizedBox(height: 6),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Text(
                    notif['body'],
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                    textAlign: TextAlign.center,
                  ),
                ),

                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                  child: Divider(height: 1),
                ),

                // Aksi berdasarkan tipe
                if (type == 'price_alert' || type == 'prediction') ...[
                  _actionTile(
                    ctx: ctx,
                    icon: Icons.trending_up,
                    color: Colors.blue,
                    label:
                        'Lihat Prediksi${commodity != null ? ' $commodity' : ''}',
                    subtitle: 'Buka tab Prediksi',
                    onTap: () {
                      Navigator.pop(ctx);
                      Navigator.pop(context, {
                        'action': 'navigate_tab',
                        'tabIndex': 1,
                      });
                    },
                  ),
                  _actionTile(
                    ctx: ctx,
                    icon: Icons.calculate_outlined,
                    color: Colors.orange,
                    label: 'Buka Simulasi',
                    subtitle: 'Hitung estimasi anggaran',
                    onTap: () {
                      Navigator.pop(ctx);
                      Navigator.pop(context, {
                        'action': 'navigate_tab',
                        'tabIndex': 3,
                      });
                    },
                  ),
                ],

                if (type == 'simulation') ...[
                  _actionTile(
                    ctx: ctx,
                    icon: Icons.calculate,
                    color: Colors.orange,
                    label: 'Buka Simulasi',
                    subtitle: 'Hitung estimasi anggaran',
                    onTap: () {
                      Navigator.pop(ctx);
                      Navigator.pop(context, {
                        'action': 'navigate_tab',
                        'tabIndex': 3,
                      });
                    },
                  ),
                  _actionTile(
                    ctx: ctx,
                    icon: Icons.trending_up,
                    color: Colors.blue,
                    label: 'Lihat Prediksi',
                    subtitle: 'Buka tab Prediksi',
                    onTap: () {
                      Navigator.pop(ctx);
                      Navigator.pop(context, {
                        'action': 'navigate_tab',
                        'tabIndex': 1,
                      });
                    },
                  ),
                ],

                // Tanya AI — selalu muncul
                _actionTile(
                  ctx: ctx,
                  icon: Icons.auto_awesome_rounded,
                  color: const Color(0xFF1565C0),
                  label: commodity != null
                      ? 'Tanya AI tentang $commodity'
                      : 'Tanya AI Chat',
                  subtitle: 'Buka asisten AI',
                  onTap: () {
                    Navigator.pop(ctx);
                    Navigator.pop(context, {'action': 'open_ai'});
                  },
                ),

                const SizedBox(height: 8),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _actionTile({
    required BuildContext ctx,
    required IconData icon,
    required Color color,
    required String label,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Container(
        width: 42,
        height: 42,
        decoration: BoxDecoration(
          color: color.withOpacity(0.12),
          shape: BoxShape.circle,
        ),
        child: Icon(icon, color: color, size: 22),
      ),
      title: Text(label,
          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
      subtitle: Text(subtitle,
          style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
      onTap: onTap,
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 2),
    );
  }

  Color _typeColor(String type) {
    return switch (type) {
      'prediction' => Colors.blue,
      'simulation' => Colors.orange,
      _ => Colors.red,
    };
  }

  IconData _typeIcon(String type) {
    return switch (type) {
      'prediction' => Icons.trending_up,
      'simulation' => Icons.calculate,
      _ => Icons.notifications,
    };
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final unreadCount =
        _notifications.where((n) => n['isRead'] == false).length;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const Text('Notifikasi'),
            if (unreadCount > 0) ...[
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.red,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '$unreadCount',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ],
        ),
        actions: [
          if (unreadCount > 0)
            TextButton(
              onPressed: _markAllRead,
              child: const Text('Tandai Dibaca'),
            ),
        ],
      ),
      body: _notifications.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.notifications_none,
                      size: 64, color: Colors.grey.shade400),
                  const SizedBox(height: 12),
                  Text('Tidak ada notifikasi',
                      style: TextStyle(color: Colors.grey.shade500)),
                ],
              ),
            )
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: _notifications.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (_, i) {
                final notif = _notifications[i];
                final isRead = notif['isRead'] as bool;
                final type = notif['type'] as String;
                final accentColor = _typeColor(type);
                final accentIcon = _typeIcon(type);

                return GestureDetector(
                  onTap: () {
                    setState(() => notif['isRead'] = true);
                    _showActionSheet(notif);
                  },
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 300),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: isRead
                          ? (isDark ? const Color(0xFF1E1E1E) : Colors.white)
                          : accentColor.withOpacity(isDark ? 0.12 : 0.06),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: isRead
                            ? (isDark
                                ? Colors.grey.shade800
                                : Colors.grey.shade200)
                            : accentColor.withOpacity(0.35),
                        width: isRead ? 1 : 1.5,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.04),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Ikon tipe
                        Container(
                          width: 42,
                          height: 42,
                          decoration: BoxDecoration(
                            color: accentColor.withOpacity(0.15),
                            shape: BoxShape.circle,
                          ),
                          child: Icon(accentIcon, color: accentColor, size: 20),
                        ),
                        const SizedBox(width: 12),

                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      notif['title'],
                                      style: TextStyle(
                                        fontWeight: isRead
                                            ? FontWeight.w500
                                            : FontWeight.w700,
                                        fontSize: 13,
                                        color: isDark
                                            ? Colors.white
                                            : const Color(0xFF1A1A2E),
                                      ),
                                    ),
                                  ),
                                  if (!isRead)
                                    Container(
                                      width: 8,
                                      height: 8,
                                      decoration: BoxDecoration(
                                        color: accentColor,
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                notif['body'],
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey.shade600,
                                  height: 1.4,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  Icon(Icons.access_time,
                                      size: 11, color: Colors.grey.shade400),
                                  const SizedBox(width: 3),
                                  Text(
                                    notif['time'],
                                    style: TextStyle(
                                        fontSize: 10,
                                        color: Colors.grey.shade500),
                                  ),
                                  const Spacer(),
                                  // Chip aksi cepat
                                  GestureDetector(
                                    onTap: () {
                                      setState(() => notif['isRead'] = true);
                                      _showActionSheet(notif);
                                    },
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: accentColor.withOpacity(0.12),
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(
                                          color: accentColor.withOpacity(0.25),
                                        ),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(Icons.bolt,
                                              size: 11, color: accentColor),
                                          const SizedBox(width: 3),
                                          Text(
                                            'Lihat Aksi',
                                            style: TextStyle(
                                              fontSize: 10,
                                              fontWeight: FontWeight.w600,
                                              color: accentColor,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }
}
