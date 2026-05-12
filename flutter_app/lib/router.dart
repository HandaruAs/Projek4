import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

// ── Import semua screen ──────────────────────────────────
import 'package:flutter_app/screens/splash_screen.dart';
import 'package:flutter_app/screens/user/home_screen.dart';
import 'package:flutter_app/screens/user/notification_screen.dart';
import 'package:flutter_app/screens/user/prediction_screen.dart';
import 'package:flutter_app/screens/user/simulation_screen.dart';
import 'package:flutter_app/screens/user/profile_screen.dart';
import 'package:flutter_app/screens/user/settings_screen.dart';
import 'package:flutter_app/screens/user/statistics_screen.dart';
import 'package:flutter_app/screens/user/commodity_detail_screen.dart';
import 'package:flutter_app/screens/user/chat_ai_screen.dart';
import 'package:flutter_app/screens/user/change_password_screen.dart';
import 'package:flutter_app/screens/user/main_screen.dart';

// ── Route name constants ─────────────────────────────────
class AppRoutes {
  static const String splash = '/';
  static const String main = '/main';
  static const String home = '/home';
  static const String notification = '/notification';
  static const String prediction = '/prediction';
  static const String simulation = '/simulation';
  static const String profile = '/profile';
  static const String settings = '/settings';
  static const String statistics = '/statistics';
  static const String chatAi = '/chat-ai';
  static const String changePassword = '/change-password';

  static const String commodityDetail = '/commodity/:id';
  static String commodityDetailPath(String id) => '/commodity/$id';
}

// ── GoRouter instance ────────────────────────────────────
final GoRouter appRouter = GoRouter(
  initialLocation: AppRoutes.splash,
  debugLogDiagnostics: true, // ubah false sebelum release

  routes: [
    // ── Splash ──
    GoRoute(
      path: AppRoutes.splash,
      builder: (context, state) => const SplashScreen(),
    ),

    // ── Main (Bottom Navigation) ──
    // Panggil setelah login: context.go(AppRoutes.main)
    GoRoute(
      path: AppRoutes.main,
      builder: (context, state) => const UserMainScreen(),
    ),

    // ── Home ──
    GoRoute(
      path: AppRoutes.home,
      builder: (context, state) => const UserHomeScreen(),
    ),

    // ── Notifikasi ──
    GoRoute(
      path: AppRoutes.notification,
      builder: (context, state) => const NotificationScreen(),
    ),

    // ── Prediksi Harga ──
    // extra String? = nama komoditas untuk pre-fill (opsional)
    // Contoh: context.push(AppRoutes.prediction, extra: 'Cabai Merah')
    GoRoute(
      path: AppRoutes.prediction,
      builder: (context, state) {
        final commodity = state.extra as String?;
        return UserPredictionScreen(initialCommodity: commodity);
      },
    ),

    // ── Simulasi Pengeluaran ──
    // extra String? = nama komoditas untuk pre-fill (opsional)
    // Contoh: context.push(AppRoutes.simulation, extra: 'Beras Medium')
    GoRoute(
      path: AppRoutes.simulation,
      builder: (context, state) {
        final commodity = state.extra as String?;
        return UserSimulationScreen(initialCommodity: commodity);
      },
    ),

    // ── Profil ──
    GoRoute(
      path: AppRoutes.profile,
      builder: (context, state) => const ProfileScreen(),
    ),

    // ── Pengaturan ──
    GoRoute(
      path: AppRoutes.settings,
      builder: (context, state) => const SettingsScreen(),
    ),

    // ── Statistik ──
    GoRoute(
      path: AppRoutes.statistics,
      builder: (context, state) => const UserStatisticsScreen(),
    ),

    // ── Chat AI ──
    GoRoute(
      path: AppRoutes.chatAi,
      builder: (context, state) => const ChatAiScreen(),
    ),

    // ── Ganti Password ──
    GoRoute(
      path: AppRoutes.changePassword,
      builder: (context, state) => const ChangePasswordScreen(),
    ),

    // ── Detail Komoditas ──
    // Contoh: context.push(AppRoutes.commodityDetailPath('12'))
    GoRoute(
      path: AppRoutes.commodityDetail,
      builder: (context, state) {
        final id = state.pathParameters['id']!;
        return CommodityDetailScreen(commodityId: id);
      },
    ),
  ],

  // ── Error page ──
  errorBuilder: (context, state) => Scaffold(
    body: Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 48, color: Colors.red),
          const SizedBox(height: 12),
          Text('Halaman tidak ditemukan: ${state.uri}'),
          const SizedBox(height: 12),
          ElevatedButton(
            onPressed: () => context.go(AppRoutes.main),
            child: const Text('Kembali ke Beranda'),
          ),
        ],
      ),
    ),
  ),
);
