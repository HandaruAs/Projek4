import 'dart:math';
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_app/screens/user/notification_screen.dart';
import 'package:flutter_app/services/notification_service.dart';
import 'package:flutter_app/screens/user/settings_screen.dart';
import 'package:flutter_app/screens/user/simulation_screen.dart';
import 'package:flutter_app/screens/user/home_screen.dart';
import 'package:flutter_app/screens/user/prediction_screen.dart';
import 'package:flutter_app/screens/user/statistics_screen.dart';
import 'package:flutter_app/screens/user/profile_screen.dart';
import 'package:flutter_app/screens/user/chat_ai_screen.dart';

class UserMainScreen extends StatefulWidget {
  const UserMainScreen({super.key});

  @override
  State<UserMainScreen> createState() => _UserMainScreenState();
}

class _UserMainScreenState extends State<UserMainScreen>
    with SingleTickerProviderStateMixin {
  int _selectedIndex = 0;

  late AnimationController _pulseController;
  late Animation<double> _pulseAnim;

  final List<String> _titles = [
    'Beranda',
    'Prediksi',
    'Statistik',
    'Simulasi',
    'Profil',
  ];

  final List<Widget> _screens = const [
    UserHomeScreen(),
    UserPredictionScreen(),
    UserStatisticsScreen(),
    UserSimulationScreen(),
    ProfileScreen(),
  ];

  int _unreadCount = 0; // mulai 0, fetch dari API
  Timer? _badgeTimer;
  final _notifService = NotificationApiService();

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    )..repeat(reverse: true);

    _pulseAnim = Tween<double>(begin: 1.0, end: 1.08).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );

    // Fetch badge saat pertama buka
    _fetchUnreadCount();
    // Polling badge tiap 30 detik
    _badgeTimer = Timer.periodic(
      const Duration(seconds: 30),
      (_) => _fetchUnreadCount(),
    );
  }

  Future<void> _fetchUnreadCount() async {
    final notifs = await _notifService.fetchNotifications();
    if (mounted) {
      setState(() {
        _unreadCount = notifs.where((n) => n['isRead'] == false).length;
      });
    }
  }

  @override
  void dispose() {
    _pulseController.dispose();
    _badgeTimer?.cancel();
    super.dispose();
  }

  void _openChatAI() {
    final size = MediaQuery.of(context).size;

    Navigator.push(
      context,
      PageRouteBuilder(
        pageBuilder: (_, animation, __) => const ChatAiScreen(),
        transitionsBuilder: (context, animation, secondaryAnimation, child) {
          return _CircularRevealTransition(
            animation: animation,
            centerOffset: Offset(size.width, size.height),
            child: child,
          );
        },
        transitionDuration: const Duration(milliseconds: 500),
      ),
    );
  }

  // ── Buka notifikasi dan tangani hasil navigasi ──
  Future<void> _openNotifications() async {
    final result = await Navigator.push<Map<String, dynamic>>(
      context,
      MaterialPageRoute(builder: (_) => const NotificationScreen()),
    );

    // Fetch ulang badge dari API setelah kembali
    await _fetchUnreadCount();

    if (result != null && mounted) {
      final action = result['action'] as String?;
      final tabIndex = result['tabIndex'] as int?;

      if (action == 'navigate_tab' && tabIndex != null) {
        setState(() => _selectedIndex = tabIndex);
      } else if (action == 'open_ai') {
        _openChatAI();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_titles[_selectedIndex]),
        actions: [
          // ── Notifikasi ──
          Stack(
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined),
                onPressed: _openNotifications,
              ),
              if (_unreadCount > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.all(3),
                    decoration: const BoxDecoration(
                      color: Colors.red,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(
                      minWidth: 16,
                      minHeight: 16,
                    ),
                    child: Text(
                      '$_unreadCount',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),
          // ── Pengaturan ──
          IconButton(
            icon: const Icon(Icons.settings_outlined),
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const SettingsScreen()),
            ),
          ),
        ],
      ),

      body: IndexedStack(index: _selectedIndex, children: _screens),

      // ── Floating Button AI ──────────────────────────────
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: ScaleTransition(
          scale: _pulseAnim,
          child: GestureDetector(
            onTap: _openChatAI,
            child: Container(
              width: 58,
              height: 58,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [Color(0xFF1E88E5), Color(0xFF1565C0)],
                ),
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: Color.fromRGBO(21, 101, 192, 0.45),
                    blurRadius: 16,
                    offset: Offset(0, 6),
                  ),
                  BoxShadow(
                    color: Color.fromRGBO(255, 255, 255, 0.2),
                    blurRadius: 4,
                    offset: Offset(-2, -2),
                  ),
                ],
              ),
              child: Stack(
                alignment: Alignment.center,
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: const BoxDecoration(
                      color: Color.fromRGBO(255, 255, 255, 0.1),
                      shape: BoxShape.circle,
                    ),
                  ),
                  const Icon(
                    Icons.auto_awesome_rounded,
                    color: Colors.white,
                    size: 26,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,

      // ── Bottom Navigation ────────────────────────────────
      bottomNavigationBar: BottomAppBar(
        shape: const CircularNotchedRectangle(),
        notchMargin: 6,
        child: SizedBox(
          height: 60,
          child: Row(
            children: [
              _buildNavItem(0, Icons.home_outlined, Icons.home, 'Beranda'),
              _buildNavItem(
                1,
                Icons.trending_up_outlined,
                Icons.trending_up,
                'Prediksi',
              ),
              _buildNavItem(
                2,
                Icons.bar_chart_outlined,
                Icons.bar_chart,
                'Statistik',
              ),
              _buildNavItem(
                3,
                Icons.calculate_outlined,
                Icons.calculate,
                'Simulasi',
              ),
              _buildNavItem(4, Icons.person_outline, Icons.person, 'Profil'),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(
    int index,
    IconData icon,
    IconData activeIcon,
    String label,
  ) {
    final isActive = _selectedIndex == index;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _selectedIndex = index),
        behavior: HitTestBehavior.opaque,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              isActive ? activeIcon : icon,
              size: 22,
              color: isActive ? const Color(0xFF1976D2) : Colors.grey[500],
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                color: isActive ? const Color(0xFF1976D2) : Colors.grey[500],
                fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════════
// CIRCULAR REVEAL TRANSITION
// ═══════════════════════════════════════════════════════════════
class _CircularRevealTransition extends StatelessWidget {
  final Animation<double> animation;
  final Offset centerOffset;
  final Widget child;

  const _CircularRevealTransition({
    required this.animation,
    required this.centerOffset,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: animation,
      builder: (context, _) {
        return ClipPath(
          clipper: _CircularRevealClipper(
            fraction: animation.value,
            centerOffset: centerOffset,
          ),
          child: child,
        );
      },
    );
  }
}

class _CircularRevealClipper extends CustomClipper<Path> {
  final double fraction;
  final Offset centerOffset;

  _CircularRevealClipper({required this.fraction, required this.centerOffset});

  @override
  Path getClip(Size size) {
    final double maxRadius = sqrt(
      pow(centerOffset.dx, 2) + pow(centerOffset.dy, 2),
    );
    final double radius = maxRadius * fraction;

    return Path()
      ..addOval(Rect.fromCircle(center: centerOffset, radius: radius));
  }

  @override
  bool shouldReclip(_CircularRevealClipper oldClipper) =>
      oldClipper.fraction != fraction;
}
