import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_app/models/user_model.dart';
import 'package:flutter_app/providers/auth_provider.dart';
import 'package:flutter_app/screens/auth/login_screen.dart';
import 'package:flutter_app/services/api_service.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _apiService        = ApiService();
  final _imagePicker       = ImagePicker();

  bool _isLoading  = false;
  bool _isEditing  = false;
  File? _pickedImage;          // file foto yang baru dipilih (belum disimpan)

  @override
  void initState() {
    super.initState();
    _loadProfile();
    _refreshProfile();
  }

  // ─── Fetch profil terbaru dari server ─────────────────────
Future<void> _refreshProfile() async {
  try {
    final response = await _apiService.getProfile();
    if (!mounted) return;
    if (response['status'] == 'success') {
      final updatedUser = UserModel.fromJson(response['data']);
      await Provider.of<AuthProvider>(context, listen: false)
          .updateCurrentUser(updatedUser);
    }
  } catch (_) {}
}

  //  Fungsi async untuk load profile (digunakan oleh FutureBuilder)
  Future<UserModel?> _loadProfile() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      _nameController.text = user.name;
      _emailController.text = user.email;
      _phoneController.text = user.phone;
      _addressController.text = user.address;
      return user;
    }
    return null;
  }

  // ─── Pilih foto dari galeri atau kamera ───────────────────
  Future<void> _pickImage(ImageSource source) async {
    Navigator.pop(context); // tutup bottom sheet
    try {
      final picked = await _imagePicker.pickImage(
        source: source,
        imageQuality: 80,
        maxWidth: 800,
      );
      if (picked != null) {
        setState(() => _pickedImage = File(picked.path));
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memilih foto: $e'), backgroundColor: Colors.red),
      );
    }
  }

  // ─── Bottom sheet pilih sumber foto ───────────────────────
  void _showImageSourceSheet() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 12),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const Text(
                'Pilih Foto Profil',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              ListTile(
                leading: const CircleAvatar(
                  child: Icon(Icons.photo_library_outlined),
                ),
                title: const Text('Pilih dari Galeri'),
                onTap: () => _pickImage(ImageSource.gallery),
              ),
              ListTile(
                leading: const CircleAvatar(
                  child: Icon(Icons.camera_alt_outlined),
                ),
                title: const Text('Ambil Foto'),
                onTap: () => _pickImage(ImageSource.camera),
              ),
              // Hapus foto hanya muncul jika sudah ada foto
              Consumer<AuthProvider>(
                builder: (_, auth, __) {
                  final hasPhoto = (auth.currentUser?.avatarUrl ?? '').isNotEmpty
                      || _pickedImage != null;
                  if (!hasPhoto) return const SizedBox.shrink();
                  return ListTile(
                    leading: const CircleAvatar(
                      backgroundColor: Color(0xFFFFEEEE),
                      child: Icon(Icons.delete_outline, color: Colors.red),
                    ),
                    title: const Text(
                      'Hapus Foto',
                      style: TextStyle(color: Colors.red),
                    ),
                    onTap: () {
                      Navigator.pop(context);
                      setState(() => _pickedImage = null);
                      // Opsional: langsung hapus foto dari server juga
                      _removeAvatar();
                    },
                  );
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─── Hapus avatar (panggil API) ────────────────────────────
  Future<void> _removeAvatar() async {
    try {
      await _apiService.removeAvatar();
      if (!mounted) return;
      final auth = Provider.of<AuthProvider>(context, listen: false);
      final user = auth.currentUser;
      if (user != null) {
        // Update lokal: kosongkan avatarUrl
        await auth.updateCurrentUser(user.copyWith(avatarUrl: ''));
      }
    } catch (_) {}
  }

  // ─── Simpan perubahan profil ───────────────────────────────
  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    try {
      final response = await _apiService.updateProfile(
        name:      _nameController.text.trim(),
        email:     _emailController.text.trim(),
        phone:     _phoneController.text.trim(),
        address:   _addressController.text.trim(),
        avatarFile: _pickedImage,          // ← kirim file foto jika ada
      );

      if (!mounted) return;

      if (response['status'] == 'success') {
        final updatedUser = UserModel.fromJson(response['data']);
        await Provider.of<AuthProvider>(context, listen: false)
            .updateCurrentUser(updatedUser);

        setState(() {
          _isEditing    = false;
          _pickedImage  = null;   // reset setelah berhasil simpan
        });

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Profil berhasil diperbarui'),
            backgroundColor: Colors.green,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? 'Gagal memperbarui profil'),
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

  Future<void> _handleLogout() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.logout();
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
    );
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  // ─── Builder utama ─────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        actions: [
          if (!_isEditing)
            IconButton(
              icon: const Icon(Icons.edit),
              tooltip: 'Edit Profil',
              onPressed: () => setState(() => _isEditing = true),
            )
          else
            IconButton(
              icon: const Icon(Icons.close),
              tooltip: 'Batal',
              onPressed: () {
                setState(() {
                  _isEditing   = false;
                  _pickedImage = null;
                });
                _loadProfile();
              },
            ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            children: [

              // ── Avatar ──────────────────────────────────────
              _buildAvatar(colorScheme),

              const SizedBox(height: 8),

              Consumer<AuthProvider>(
                builder: (context, auth, _) => Text(
                  auth.currentUser?.name ?? '',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),

              const SizedBox(height: 4),

              Consumer<AuthProvider>(
                builder: (context, auth, _) => Text(
                  auth.currentUser?.email ?? '',
                  style: TextStyle(fontSize: 14, color: Colors.grey.shade500),
                ),
              ),

              const SizedBox(height: 32),

              // ── Field Nama ───────────────────────────────────
              _buildField(
                controller: _nameController,
                label: 'Nama',
                icon: Icons.person_outline,
                enabled: _isEditing,
                isDark: isDark,
                primary: colorScheme.primary,
                validator: (v) =>
                    v == null || v.isEmpty ? 'Nama tidak boleh kosong' : null,
              ),

              const SizedBox(height: 16),

              // ── Field Email ──────────────────────────────────
              _buildField(
                controller: _emailController,
                label: 'Email',
                icon: Icons.email_outlined,
                enabled: _isEditing,
                isDark: isDark,
                primary: colorScheme.primary,
                keyboardType: TextInputType.emailAddress,
                validator: (v) =>
                    v == null || v.isEmpty ? 'Email tidak boleh kosong' : null,
              ),

              const SizedBox(height: 16),

              // ── Field No HP ──────────────────────────────────
              _buildField(
                controller: _phoneController,
                label: 'No. HP (opsional)',
                icon: Icons.phone_outlined,
                enabled: _isEditing,
                isDark: isDark,
                primary: colorScheme.primary,
                keyboardType: TextInputType.phone,
                hint: 'ex: 08562561612',
              ),

              const SizedBox(height: 16),

              // ── Field Alamat ─────────────────────────────────
              _buildField(
                controller: _addressController,
                label: 'Alamat (opsional)',
                icon: Icons.location_on_outlined,
                enabled: _isEditing,
                isDark: isDark,
                primary: colorScheme.primary,
                keyboardType: TextInputType.streetAddress,
                maxLines: 3,
                hint: 'ex: Jl. Hayam Wuruk No. 1, Jember',
              ),

              const SizedBox(height: 32),

              // ── Tombol Simpan ────────────────────────────────
              if (_isEditing)
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _saveProfile,
                    child: _isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              color: Colors.white,
                              strokeWidth: 2,
                            ),
                          )
                        : const Text('Simpan Perubahan'),
                  ),
                ),

              const SizedBox(height: 16),

              // ── Tombol Logout ────────────────────────────────
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: _handleLogout,
                  icon: const Icon(Icons.logout, color: Colors.red),
                  label: const Text(
                    'Logout',
                    style: TextStyle(color: Colors.red),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Colors.red),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─── Widget Avatar dengan tombol edit ─────────────────────
  Widget _buildAvatar(ColorScheme colorScheme) {
    return Consumer<AuthProvider>(
      builder: (_, auth, __) {
        final avatarUrl = auth.currentUser?.avatarUrl ?? '';

        return Stack(
          alignment: Alignment.bottomRight,
          children: [
            // Lingkaran foto
            CircleAvatar(
              radius: 56,
              backgroundColor: colorScheme.primary.withOpacity(0.1),
              backgroundImage: _pickedImage != null
                  ? FileImage(_pickedImage!) as ImageProvider  // preview lokal
                  : (avatarUrl.isNotEmpty
                      ? NetworkImage(avatarUrl) as ImageProvider
                      : null),
              child: (_pickedImage == null && avatarUrl.isEmpty)
                  ? Icon(Icons.person, size: 56, color: colorScheme.primary)
                  : null,
            ),

            // Tombol kamera (hanya muncul saat mode edit)
            if (_isEditing)
              GestureDetector(
                onTap: _showImageSourceSheet,
                child: Container(
                  padding: const EdgeInsets.all(7),
                  decoration: BoxDecoration(
                    color: colorScheme.primary,
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 2),
                  ),
                  child: const Icon(
                    Icons.camera_alt,
                    color: Colors.white,
                    size: 18,
                  ),
                ),
              ),
          ],
        );
      },
    );
  }

  // ─── Widget field input ────────────────────────────────────
  Widget _buildField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    required bool isDark,
    required Color primary,
    bool enabled = true,
    TextInputType keyboardType = TextInputType.text,
    String? Function(String?)? validator,
    int maxLines = 1,
    String? hint,
  }) {
    return TextFormField(
      controller: controller,
      enabled: enabled,
      keyboardType: keyboardType,
      validator: validator,
      maxLines: maxLines,
      style: TextStyle(color: isDark ? Colors.white : const Color(0xFF1A1A2E)),
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: Icon(icon, color: primary),
        filled: true,
        fillColor: enabled
            ? (isDark ? const Color(0xFF2C2C2C) : Colors.grey.shade50)
            : (isDark ? const Color(0xFF1E1E1E) : Colors.grey.shade100),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(
              color: isDark ? Colors.grey.shade700 : Colors.grey.shade300),
        ),
        disabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(
              color: isDark ? Colors.grey.shade800 : Colors.grey.shade200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: primary),
        ),
      ),
    );
  }
}