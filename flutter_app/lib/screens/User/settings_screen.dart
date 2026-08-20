import 'package:flutter/material.dart';
import 'package:flutter_app/providers/auth_provider.dart';
import 'package:flutter_app/providers/theme_provider.dart';
import 'package:flutter_app/screens/auth/login_screen.dart';
import 'package:flutter_app/screens/user/change_password_screen.dart';
import 'package:provider/provider.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _notifications = true;
  bool _priceAlerts = true;

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Keluar'),
        content: const Text('Apakah kamu yakin ingin keluar dari akun ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              Provider.of<AuthProvider>(context, listen: false).logout();
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (route) => false,
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
  }

  void _confirmSwitchAccount() {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Ganti Akun'),
        content: const Text('Kamu akan keluar dan diarahkan ke halaman login.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              Provider.of<AuthProvider>(context, listen: false).logout();
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (route) => false,
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF1976D2),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('Lanjutkan'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pengaturan'),
      ),
      body: ListView(
        children: [
          // ── SECTION: Akun ──
          const _SectionHeader(label: 'Akun'),

          _SettingsTile(
            icon: Icons.swap_horiz,
            iconColor: Colors.orange,
            title: 'Ganti Akun',
            subtitle: 'Masuk dengan akun lain',
            trailing: const Icon(Icons.chevron_right, color: Colors.grey),
            onTap: _confirmSwitchAccount,
          ),

          _SettingsTile(
            icon: Icons.lock_outline,
            iconColor: Colors.purple,
            title: 'Ubah Password',
            subtitle: 'Ganti kata sandi akun',
            trailing: const Icon(Icons.chevron_right, color: Colors.grey),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ChangePasswordScreen()),
            ),
          ),

          const Divider(height: 1, indent: 16),

          // ── SECTION: Notifikasi ──
          const _SectionHeader(label: 'Notifikasi'),

          _SettingsSwitchTile(
            icon: Icons.notifications_outlined,
            iconColor: Colors.blue,
            title: 'Notifikasi',
            subtitle: 'Aktifkan semua notifikasi',
            value: _notifications,
            onChanged: (v) => setState(() => _notifications = v),
          ),

          _SettingsSwitchTile(
            icon: Icons.price_change_outlined,
            iconColor: Colors.red,
            title: 'Alert Harga',
            subtitle: 'Notifikasi saat harga berubah signifikan',
            value: _priceAlerts,
            onChanged:
                _notifications ? (v) => setState(() => _priceAlerts = v) : null,
          ),

          const Divider(height: 1, indent: 16),

          // ── SECTION: Tampilan ──
          const _SectionHeader(label: 'Tampilan'),

          // ← Hubungkan ke ThemeProvider
          Consumer<ThemeProvider>(
            builder: (context, themeProvider, _) => _SettingsSwitchTile(
              icon: Icons.dark_mode_outlined,
              iconColor: Colors.indigo,
              title: 'Mode Gelap',
              subtitle: 'Gunakan tema gelap',
              value: themeProvider.isDarkMode,
              onChanged: (v) => themeProvider.toggleTheme(v),
            ),
          ),

          const Divider(height: 1, indent: 16),

          // ── SECTION: Tentang ──
          const _SectionHeader(label: 'Tentang'),

          _SettingsTile(
            icon: Icons.info_outline,
            iconColor: Colors.blueGrey,
            title: 'Tentang Aplikasi',
            subtitle: 'SIMOPANG v1.0.0',
            trailing: const Icon(Icons.chevron_right, color: Colors.grey),
            onTap: () => showAboutDialog(
              context: context,
              applicationName: 'SIMOPANG',
              applicationVersion: '1.0.0',
              applicationIcon: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFF1976D2).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.bar_chart,
                    color: Color(0xFF1976D2), size: 32),
              ),
              children: const [
                Text(
                  'Sistem Monitoring Harga Pangan — aplikasi pemantau harga komoditas pangan di Kabupaten Jember.',
                ),
              ],
            ),
          ),

          // ── LOGOUT ──
          Padding(
            padding: const EdgeInsets.all(16),
            child: OutlinedButton.icon(
              onPressed: _confirmLogout,
              icon: const Icon(Icons.logout, color: Colors.red),
              label: const Text('Keluar dari Akun',
                  style: TextStyle(color: Colors.red)),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
                side: const BorderSide(color: Colors.red),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ),

          const SizedBox(height: 16),
        ],
      ),
    );
  }
}

// ── HELPER WIDGETS ──

class _SectionHeader extends StatelessWidget {
  final String label;
  const _SectionHeader({required this.label});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
      child: Text(
        label.toUpperCase(),
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: Colors.grey[500],
          letterSpacing: 1.2,
        ),
      ),
    );
  }
}

class _SettingsTile extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final String subtitle;
  final Widget? trailing;
  final VoidCallback? onTap;

  const _SettingsTile({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.subtitle,
    this.trailing,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: iconColor.withOpacity(0.12),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, color: iconColor, size: 20),
      ),
      title: Text(title,
          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
      subtitle: Text(subtitle,
          style: TextStyle(fontSize: 12, color: Colors.grey[500])),
      trailing: trailing,
      onTap: onTap,
    );
  }
}

class _SettingsSwitchTile extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final String subtitle;
  final bool value;
  final ValueChanged<bool>? onChanged;

  const _SettingsSwitchTile({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.subtitle,
    required this.value,
    this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    final isDisabled = onChanged == null;
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: (isDisabled ? Colors.grey : iconColor).withOpacity(0.12),
          borderRadius: BorderRadius.circular(8),
        ),
        child:
            Icon(icon, color: isDisabled ? Colors.grey : iconColor, size: 20),
      ),
      title: Text(title,
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w500,
            color: isDisabled ? Colors.grey : null,
          )),
      subtitle: Text(subtitle,
          style: TextStyle(fontSize: 12, color: Colors.grey[500])),
      trailing: Switch(
        value: value,
        onChanged: onChanged,
        activeThumbColor: const Color(0xFF1976D2),
      ),
    );
  }
}
