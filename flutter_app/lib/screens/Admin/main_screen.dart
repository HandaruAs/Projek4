import 'package:flutter/material.dart';
import 'package:flutter_app/providers/auth_provider.dart';
import 'package:flutter_app/screens/Admin/home_screen.dart';
import 'package:flutter_app/screens/Admin/prediction_screen.dart';
import 'package:flutter_app/screens/Admin/price_chart_screen.dart';
import 'package:flutter_app/screens/auth/login_screen.dart';
import 'package:provider/provider.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _selectedIndex = 0;
  bool _darkMode = false;
  bool _notifications = true;

  final List<Widget> _screens = const [
    HomeScreen(),
    PredictionScreen(),
    PriceChartScreen(),
  ];

  final List<String> _titles = [
    'Dashboard',
    'Prediksi',
    'Grafik Harga',
  ];

  /// PROFILE MENU

  void _showProfileMenu() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        final authProvider = Provider.of<AuthProvider>(context);
        final user = authProvider.currentUser;

        return SafeArea(
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [

                  /// Handle bar
                  Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),

                  const SizedBox(height: 20),

                  /// Avatar
                  CircleAvatar(
                    radius: 40,
                    backgroundColor:
                        const Color(0xFF1976D2).withOpacity(0.1),
                    child: const Icon(
                      Icons.person,
                      size: 40,
                      color: Color(0xFF1976D2),
                    ),
                  ),

                  const SizedBox(height: 10),

                  Text(
                    user?.name ?? "User",
                    style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold),
                  ),

                  Text(
                    user?.email ?? "email@example.com",
                    style: TextStyle(color: Colors.grey.shade600),
                  ),

                  const SizedBox(height: 20),

                  const Divider(),

                  ListTile(
                    leading: const Icon(Icons.person_outline),
                    title: const Text("Profil Saya"),
                    onTap: () {
                      Navigator.pop(context);
                      _showProfile();
                    },
                  ),

                  ListTile(
                    leading: const Icon(Icons.settings),
                    title: const Text("Pengaturan"),
                    onTap: () {
                      Navigator.pop(context);
                      _showSettings();
                    },
                  ),

                  const Divider(),

                  /// LOGOUT
                  ListTile(
                    leading: const Icon(Icons.logout, color: Colors.red),
                    title: const Text(
                      "Logout",
                      style: TextStyle(color: Colors.red),
                    ),
                    onTap: () async {
                      Navigator.pop(context);
                      await _handleLogout();
                    },
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  /// NOTIFICATION PANEL

  void _showNotifications() {
    showModalBottomSheet(
      context: context,
      builder: (context) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [

                const Text(
                  "Notifikasi",
                  style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold),
                ),

                const SizedBox(height: 10),

                ListView.builder(
                  shrinkWrap: true,
                  itemCount: 3,
                  itemBuilder: (context, index) {
                    return ListTile(
                      leading: CircleAvatar(
                        backgroundColor:
                            const Color(0xFF1976D2).withOpacity(0.1),
                        child: const Icon(Icons.notifications),
                      ),
                      title: Text("Notifikasi ${index + 1}"),
                      subtitle:
                          const Text("Harga komoditas berubah hari ini"),
                    );
                  },
                ),

                const SizedBox(height: 10),

                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text("Tutup"),
                )
              ],
            ),
          ),
        );
      },
    );
  }

  /// PROFILE

  void _showProfile() {
    final authProvider =
        Provider.of<AuthProvider>(context, listen: false);
    final user = authProvider.currentUser;

    showDialog(
      context: context,
      builder: (_) {
        return AlertDialog(
          title: const Text("Profil Saya"),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircleAvatar(
                radius: 40,
                backgroundColor:
                    const Color(0xFF1976D2).withOpacity(0.1),
                child: const Icon(Icons.person, size: 40),
              ),
              const SizedBox(height: 10),
              Text(user?.name ?? "User"),
              Text(user?.email ?? "email@example.com"),
            ],
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text("Tutup"))
          ],
        );
      },
    );
  }

  /// SETTINGS

  void _showSettings() {
    showDialog(
      context: context,
      builder: (_) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: const Text("Pengaturan"),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [

                  SwitchListTile(
                    title: const Text("Notifikasi"),
                    value: _notifications,
                    onChanged: (value) {
                      setState(() {
                        _notifications = value;
                      });
                    },
                  ),

                  SwitchListTile(
                    title: const Text("Mode Gelap"),
                    value: _darkMode,
                    onChanged: (value) {
                      setState(() {
                        _darkMode = value;
                      });
                    },
                  ),
                ],
              ),
              actions: [
                TextButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text("Tutup"))
              ],
            );
          },
        );
      },
    );
  }

  /// LOGOUT

  Future<void> _handleLogout() async {
    final authProvider =
        Provider.of<AuthProvider>(context, listen: false);

    await authProvider.logout();

    if (!mounted) return;

    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (_) => const LoginScreen(),
      ),
    );
  }

  /// UI

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_titles[_selectedIndex]),
        actions: [

          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: _showNotifications,
          ),

          IconButton(
            icon: const Icon(Icons.person_outline),
            onPressed: _showProfileMenu,
          ),
        ],
      ),

      body: IndexedStack(
        index: _selectedIndex,
        children: _screens,
      ),

      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        onTap: (index) {
          setState(() {
            _selectedIndex = index;
          });
        },
        selectedItemColor: const Color(0xFF1976D2),
        type: BottomNavigationBarType.fixed,
        items: const [

          BottomNavigationBarItem(
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home),
            label: "Home",
          ),

          BottomNavigationBarItem(
            icon: Icon(Icons.trending_up_outlined),
            activeIcon: Icon(Icons.trending_up),
            label: "Prediksi",
          ),

          BottomNavigationBarItem(
            icon: Icon(Icons.bar_chart_outlined),
            activeIcon: Icon(Icons.bar_chart),
            label: "Grafik",
          ),
        ],
      ),
    );
  }
}