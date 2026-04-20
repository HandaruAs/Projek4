import 'package:flutter/material.dart';
import 'package:flutter_app/providers/auth_provider.dart';
import 'package:flutter_app/screens/auth/login_screen.dart';
import 'package:provider/provider.dart';

class UserMainScreen extends StatefulWidget {
  const UserMainScreen({super.key});

  @override
  State<UserMainScreen> createState() => _UserMainScreenState();
}

class _UserMainScreenState extends State<UserMainScreen> {

  int _selectedIndex = 0;

  final List<String> _titles = [
    "Beranda",
    "Prediksi",
    "Grafik"
  ];

  final List<Widget> _screens = const [
    Center(child: Text("Dashboard Harga Pangan")),
    Center(child: Text("Prediksi Harga Komoditas")),
    Center(child: Text("Grafik Perubahan Harga")),
  ];

  /// PROFILE MENU
  void _showProfileMenu() {

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final user = authProvider.currentUser;

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) {

        return Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [

              /// AVATAR
              CircleAvatar(
                radius: 40,
                backgroundColor: const Color(0xFF1976D2).withOpacity(0.1),
                child: const Icon(
                  Icons.person,
                  size: 40,
                  color: Color(0xFF1976D2),
                ),
              ),

              const SizedBox(height: 12),

              /// USER NAME
              Text(
                user?.name ?? "User",
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),

              const SizedBox(height: 4),

              /// USER EMAIL
              Text(
                user?.email ?? "-",
                style: const TextStyle(color: Colors.grey),
              ),

              const SizedBox(height: 20),

              const Divider(),

              /// LOGOUT
              ListTile(
                leading: const Icon(Icons.logout, color: Colors.red),
                title: const Text(
                  "Logout",
                  style: TextStyle(color: Colors.red),
                ),
                onTap: _handleLogout,
              ),

            ],
          ),
        );
      },
    );
  }

  /// LOGOUT
  Future<void> _handleLogout() async {

    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    await authProvider.logout();

    if (!mounted) return;

    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (_) => const LoginScreen(),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {

    return Scaffold(

      appBar: AppBar(
        title: Text(_titles[_selectedIndex]),
        actions: [

          /// PROFILE BUTTON
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
            label: "Beranda",
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