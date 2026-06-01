import 'package:flutter/material.dart';
import 'package:flutter_app/providers/auth_provider.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:provider/provider.dart';

class ResetPasswordScreen extends StatefulWidget {
  final String email;
  final String otp;

  const ResetPasswordScreen({
    super.key,
    required this.email,
    required this.otp,
  });

  @override
  State<ResetPasswordScreen> createState() => _ResetPasswordScreenState();
}

class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();

  bool _obscurePassword = true;
  bool _obscureConfirm = true;
  bool _isSuccess = false;

  // Password hint tracking
  bool _hasMinLength = false;
  bool _hasUpperCase = false;
  bool _hasLowerCase = false;
  bool _hasNumber = false;
  bool _hasSpecialChar = false;
  bool _showPasswordHints = false;

  @override
  void dispose() {
    _passwordController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  void _validatePasswordHints(String value) {
    setState(() {
      _hasMinLength = value.length >= 6;
      _hasUpperCase = value.contains(RegExp(r'[A-Z]'));
      _hasLowerCase = value.contains(RegExp(r'[a-z]'));
      _hasNumber = value.contains(RegExp(r'[0-9]'));
      _hasSpecialChar = value.contains(RegExp(r'[!@#$%^&*(),.?":{}|<>_]'));
      _showPasswordHints = value.isNotEmpty;
    });
  }

  bool get _isPasswordValid =>
      _hasMinLength &&
      _hasUpperCase &&
      _hasLowerCase &&
      _hasNumber &&
      _hasSpecialChar;

  Future<void> _handleReset() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.resetPassword(
      widget.email,
      widget.otp,
      _passwordController.text,
      _confirmController.text,
    );

    if (!mounted) return;

    if (success) {
      setState(() => _isSuccess = true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(authProvider.errorMessage ?? 'Gagal mereset password'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Widget _buildHintItem(bool isValid, String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        children: [
          AnimatedSwitcher(
            duration: const Duration(milliseconds: 200),
            child: Icon(
              isValid ? Icons.check_circle_rounded : Icons.circle_outlined,
              key: ValueKey(isValid),
              size: 16,
              color: isValid ? Colors.green : Colors.grey,
            ),
          ),
          const SizedBox(width: 8),
          Text(
            text,
            style: TextStyle(
              fontSize: 12,
              color: isValid ? Colors.green[700] : Colors.grey[600],
              fontWeight: isValid ? FontWeight.w500 : FontWeight.normal,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Buat Password Baru'),
        automaticallyImplyLeading: !_isSuccess,
        leading: _isSuccess
            ? null
            : IconButton(
                icon: const Icon(Icons.arrow_back_ios),
                onPressed: () => Navigator.of(context).pop(),
              ),
      ),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          return Stack(
            children: [
              SingleChildScrollView(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 20),

                    // ── Icon ──
                    Center(
                      child: Container(
                        width: 80,
                        height: 80,
                        decoration: BoxDecoration(
                          color: const Color(0xFF1976D2).withOpacity(0.1),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.lock_open_rounded,
                          size: 40,
                          color: Color(0xFF1976D2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    if (!_isSuccess) ...[
                      // ── Title ──
                      Text(
                        'Password Baru',
                        style: Theme.of(context)
                            .textTheme
                            .titleLarge
                            ?.copyWith(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Buat password baru untuk akun ${widget.email}.',
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 14,
                          height: 1.5,
                        ),
                      ),
                      const SizedBox(height: 32),

                      // ── Form ──
                      Form(
                        key: _formKey,
                        child: Column(
                          children: [
                            // Password baru
                            TextFormField(
                              controller: _passwordController,
                              obscureText: _obscurePassword,
                              onChanged: _validatePasswordHints,
                              decoration: InputDecoration(
                                hintText: 'Password Baru',
                                prefixIcon: const Icon(Icons.lock_outlined),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _obscurePassword
                                        ? Icons.visibility_outlined
                                        : Icons.visibility_off_outlined,
                                    color: Colors.grey,
                                  ),
                                  onPressed: () => setState(
                                    () => _obscurePassword = !_obscurePassword,
                                  ),
                                ),
                              ),
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'Password tidak boleh kosong';
                                }
                                if (!_isPasswordValid) {
                                  return 'Password belum memenuhi semua ketentuan';
                                }
                                return null;
                              },
                            ),

                            // ── Password Hints ──
                            AnimatedSize(
                              duration: const Duration(milliseconds: 250),
                              curve: Curves.easeInOut,
                              child: _showPasswordHints
                                  ? Container(
                                      margin: const EdgeInsets.only(top: 10),
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 14,
                                        vertical: 10,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.grey[100],
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(
                                          color: _isPasswordValid
                                              ? Colors.green.withOpacity(0.5)
                                              : Colors.grey[300]!,
                                        ),
                                      ),
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          _buildHintItem(
                                              _hasMinLength, 'Minimal 6 karakter'),
                                          _buildHintItem(
                                              _hasUpperCase, 'Huruf besar (A-Z)'),
                                          _buildHintItem(
                                              _hasLowerCase, 'Huruf kecil (a-z)'),
                                          _buildHintItem(
                                              _hasNumber, 'Angka (0-9)'),
                                          _buildHintItem(_hasSpecialChar,
                                              'Karakter spesial (!@#\$%^&*_)'),
                                        ],
                                      ),
                                    )
                                  : const SizedBox.shrink(),
                            ),

                            const SizedBox(height: 16),

                            // Konfirmasi password
                            TextFormField(
                              controller: _confirmController,
                              obscureText: _obscureConfirm,
                              decoration: InputDecoration(
                                hintText: 'Konfirmasi Password',
                                prefixIcon:
                                    const Icon(Icons.lock_reset_outlined),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _obscureConfirm
                                        ? Icons.visibility_outlined
                                        : Icons.visibility_off_outlined,
                                    color: Colors.grey,
                                  ),
                                  onPressed: () => setState(
                                    () => _obscureConfirm = !_obscureConfirm,
                                  ),
                                ),
                              ),
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'Konfirmasi password tidak boleh kosong';
                                }
                                if (value != _passwordController.text) {
                                  return 'Password tidak cocok';
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 28),

                            // Submit button
                            ElevatedButton(
                              onPressed: authProvider.isLoading
                                  ? null
                                  : _handleReset,
                              style: ElevatedButton.styleFrom(
                                minimumSize: const Size(double.infinity, 50),
                                backgroundColor: const Color(0xFF1976D2),
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                              child: const Text(
                                'Simpan Password Baru',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ] else ...[
                      // ── SUCCESS STATE ──
                      Container(
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: Colors.green.shade50,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: Colors.green.shade200),
                        ),
                        child: Column(
                          children: [
                            Icon(
                              Icons.check_circle_rounded,
                              size: 56,
                              color: Colors.green.shade600,
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'Password Berhasil Diubah!',
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: Colors.green.shade800,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Password akun Anda telah diperbarui. Silakan login menggunakan password baru.',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                color: Colors.green.shade700,
                                fontSize: 14,
                                height: 1.5,
                              ),
                            ),
                            const SizedBox(height: 24),
                            ElevatedButton(
                              onPressed: () {
                                Navigator.of(context).popUntil(
                                  (route) => route.isFirst,
                                );
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.green,
                                foregroundColor: Colors.white,
                                minimumSize: const Size(double.infinity, 50),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                              child: const Text(
                                'Kembali ke Login',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),

              // Loading overlay
              if (authProvider.isLoading) const LoadingWidget(),
            ],
          );
        },
      ),
    );
  }
}