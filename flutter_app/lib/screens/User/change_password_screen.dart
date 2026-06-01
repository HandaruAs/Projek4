import 'package:flutter/material.dart';
import 'package:flutter_app/services/api_service.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey     = GlobalKey<FormState>();
  final _oldCtrl     = TextEditingController();
  final _newCtrl     = TextEditingController();
  final _confirmCtrl = TextEditingController();
  final _apiService  = ApiService();

  bool _obscureOld     = true;
  bool _obscureNew     = true;
  bool _obscureConfirm = true;
  bool _isLoading      = false;
  bool _isSuccess      = false;

  // Password hint tracking
  bool _hasMinLength   = false;
  bool _hasUpperCase   = false;
  bool _hasLowerCase   = false;
  bool _hasNumber      = false;
  bool _hasSpecialChar = false;
  bool _showPasswordHints = false;

  @override
  void dispose() {
    _oldCtrl.dispose();
    _newCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  void _validatePasswordHints(String value) {
    setState(() {
      _hasMinLength    = value.length >= 6;
      _hasUpperCase    = value.contains(RegExp(r'[A-Z]'));
      _hasLowerCase    = value.contains(RegExp(r'[a-z]'));
      _hasNumber       = value.contains(RegExp(r'[0-9]'));
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

  Future<void> _handleChange() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    try {
      final response = await _apiService.changePassword(
        oldPassword: _oldCtrl.text.trim(),
        newPassword: _newCtrl.text.trim(),
      );

      if (!mounted) return;

      if (response['status'] == 'success') {
        setState(() => _isSuccess = true);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? 'Gagal mengubah password'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
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

  Widget _buildPasswordField({
    required TextEditingController controller,
    required String label,
    required bool obscure,
    required VoidCallback onToggle,
    String? Function(String?)? validator,
    void Function(String)? onChanged,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: obscure,
      validator: validator,
      onChanged: onChanged,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: const Icon(Icons.lock_outline),
        suffixIcon: IconButton(
          icon: Icon(
            obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined,
          ),
          onPressed: onToggle,
        ),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF1976D2)),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ubah Password'),
        automaticallyImplyLeading: !_isSuccess,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            const SizedBox(height: 20),

            // ── Icon ──
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: const Color(0xFF1976D2).withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.lock_outline,
                size: 40,
                color: Color(0xFF1976D2),
              ),
            ),

            const SizedBox(height: 24),

            if (!_isSuccess) ...[
              // ── Form ──
              Form(
                key: _formKey,
                child: Column(
                  children: [

                    // Password lama
                    _buildPasswordField(
                      controller: _oldCtrl,
                      label: 'Password Lama',
                      obscure: _obscureOld,
                      onToggle: () =>
                          setState(() => _obscureOld = !_obscureOld),
                      validator: (v) => v == null || v.isEmpty
                          ? 'Password lama tidak boleh kosong'
                          : null,
                    ),

                    const SizedBox(height: 16),

                    // Password baru
                    _buildPasswordField(
                      controller: _newCtrl,
                      label: 'Password Baru',
                      obscure: _obscureNew,
                      onToggle: () =>
                          setState(() => _obscureNew = !_obscureNew),
                      onChanged: _validatePasswordHints,
                      validator: (v) {
                        if (v == null || v.isEmpty) {
                          return 'Password baru tidak boleh kosong';
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
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _buildHintItem(
                                      _hasMinLength, 'Minimal 6 karakter'),
                                  _buildHintItem(
                                      _hasUpperCase, 'Huruf besar (A-Z)'),
                                  _buildHintItem(
                                      _hasLowerCase, 'Huruf kecil (a-z)'),
                                  _buildHintItem(_hasNumber, 'Angka (0-9)'),
                                  _buildHintItem(_hasSpecialChar,
                                      'Karakter spesial (!@#\$%^&*_)'),
                                ],
                              ),
                            )
                          : const SizedBox.shrink(),
                    ),

                    const SizedBox(height: 16),

                    // Konfirmasi password
                    _buildPasswordField(
                      controller: _confirmCtrl,
                      label: 'Konfirmasi Password Baru',
                      obscure: _obscureConfirm,
                      onToggle: () =>
                          setState(() => _obscureConfirm = !_obscureConfirm),
                      validator: (v) {
                        if (v == null || v.isEmpty) {
                          return 'Konfirmasi password tidak boleh kosong';
                        }
                        if (v != _newCtrl.text) return 'Password tidak cocok';
                        return null;
                      },
                    ),

                    const SizedBox(height: 28),

                    // Tombol simpan
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _handleChange,
                        child: _isLoading
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2,
                                ),
                              )
                            : const Text('Simpan Password Baru'),
                      ),
                    ),
                  ],
                ),
              ),
            ] else ...[
              // ── Success State ──
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.green.shade50,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.green.shade200),
                ),
                child: Column(
                  children: [
                    Icon(Icons.check_circle_rounded,
                        size: 56, color: Colors.green.shade600),
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
                      'Password akun Anda telah diperbarui.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Colors.green.shade700,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () => Navigator.pop(context),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.green,
                          foregroundColor: Colors.white,
                        ),
                        child: const Text('Kembali'),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}