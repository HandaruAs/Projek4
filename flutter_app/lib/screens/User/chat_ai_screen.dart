import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_app/providers/theme_provider.dart';

// ═══════════════════════════════════════════════════════════════
// MODEL
// ═══════════════════════════════════════════════════════════════
class ChatMessage {
  final String text;
  final bool isBot;
  final DateTime time;
  final bool isLoading;

  ChatMessage({
    required this.text,
    required this.isBot,
    DateTime? time,
    this.isLoading = false,
  }) : time = time ?? DateTime.now();
}

// ═══════════════════════════════════════════════════════════════
// SCREEN
// ═══════════════════════════════════════════════════════════════
class ChatAiScreen extends StatefulWidget {
  const ChatAiScreen({super.key});

  @override
  State<ChatAiScreen> createState() => _ChatAiScreenState();
}

class _ChatAiScreenState extends State<ChatAiScreen>
    with TickerProviderStateMixin {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://10.10.185.133:8000/api',
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(seconds: 60),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  ));

  final ScrollController _scrollController = ScrollController();
  final List<ChatMessage> _messages = [];

  int _step = 1;
  String? _periode;
  String? _anggota;
  String? _budget;
  List<String> _komoditas = [];
  String? _prioritas;

  List<String> _dbKomoditas = [];
  Set<String> _selectedKomSet = {};
  bool _loadingKomoditas = false;
  bool _isLoadingAI = false;

  final List<Map<String, String>> _periodeChoices = [
    {'label': '📅 1 Minggu', 'value': '1 minggu'},
    {'label': '📆 2 Minggu', 'value': '2 minggu'},
    {'label': '🗓️ 1 Bulan', 'value': '1 bulan'},
  ];

  final List<Map<String, String>> _anggotaChoices = [
    {'label': '👤 1 Orang', 'value': '1 orang'},
    {'label': '👥 2 Orang', 'value': '2 orang'},
    {'label': '👨‍👩‍👦 3–4 Orang', 'value': '3-4 orang'},
    {'label': '👨‍👩‍👧‍👦 5+ Orang', 'value': '5 orang atau lebih'},
  ];

  final List<Map<String, String>> _budgetChoices = [
    {'label': '💰 < Rp 200rb', 'value': 'di bawah Rp 200.000'},
    {'label': '💳 Rp 200rb – 500rb', 'value': 'Rp 200.000 – 500.000'},
    {'label': '💵 Rp 500rb – 1 juta', 'value': 'Rp 500.000 – 1.000.000'},
    {'label': '💎 Rp 1 juta – 2 juta', 'value': 'Rp 1.000.000 – 2.000.000'},
    {'label': '🏆 > Rp 2 juta', 'value': 'lebih dari Rp 2.000.000'},
  ];

  final List<Map<String, String>> _prioritasChoices = [
    {'label': '💰 Sehemat mungkin', 'value': 'penghematan maksimal'},
    {'label': '⚖️ Hemat tapi bergizi', 'value': 'keseimbangan harga dan gizi'},
    {'label': '🥦 Utamakan gizi', 'value': 'kualitas dan kandungan gizi'},
    {'label': '📦 Beli stok banyak', 'value': 'pembelian stok jangka panjang'},
  ];

  // ── Dark mode color helpers ──────────────────────────────────
  static const _blue = Color(0xFF1565C0);

  Color _bg(bool dark) => dark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC);
  Color _cardBg(bool dark) => dark ? const Color(0xFF1E293B) : Colors.white;
  Color _chipBg(bool dark) => dark ? const Color(0xFF1E3A5F) : const Color(0xFFEFF6FF);
  Color _chipBorder(bool dark) => dark ? const Color(0xFF3B82F6).withOpacity(0.4) : _blue.withOpacity(0.3);
  Color _chipText(bool dark) => dark ? const Color(0xFF93C5FD) : _blue;
  Color _textPrimary(bool dark) => dark ? Colors.white : const Color(0xFF1E293B);
  Color _textMuted(bool dark) => dark ? Colors.grey[400]! : Colors.grey[500]!;
  Color _inputBg(bool dark) => dark ? const Color(0xFF1E293B) : Colors.white;
  Color _divider(bool dark) => dark ? Colors.white.withOpacity(0.08) : Colors.black.withOpacity(0.06);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _startWizard());
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  void _addBotMessage(String text, {bool isLoading = false}) {
    setState(() {
      _messages.add(ChatMessage(text: text, isBot: true, isLoading: isLoading));
    });
    _scrollToBottom();
  }

  void _addUserMessage(String text) {
    setState(() {
      _messages.add(ChatMessage(text: text, isBot: false));
    });
    _scrollToBottom();
  }

  void _removeLastMessage() {
    if (_messages.isNotEmpty) setState(() => _messages.removeLast());
  }

  String _timeFormat(DateTime dt) {
    return '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }

  void _startWizard() {
    _addBotMessage(
      'Halo! Saya SIMOPANG AI 👋\n\nSaya akan membantu merekomendasikan belanja pangan yang cerdas sesuai budget Anda.\n\nPertama, untuk jangka waktu berapa Anda ingin merencanakan belanja?',
    );
    setState(() => _step = 1);
  }

  void _handleChoice(String label, String value) {
    final cleanLabel = label.replaceAll(RegExp(r'^[^\w\s]+\s*'), '').trim();
    _addUserMessage(cleanLabel.isNotEmpty ? cleanLabel : label);

    switch (_step) {
      case 1:
        _periode = value;
        _step = 2;
        _addBotMessage('Baik, untuk periode $_periode.\n\nBerapa jumlah anggota keluarga yang perlu dipenuhi kebutuhan pangannya?');
        break;
      case 2:
        _anggota = value;
        _step = 3;
        _addBotMessage('Untuk $_anggota selama $_periode.\n\nBerapa total budget belanja pangan Anda?');
        break;
      case 3:
        _budget = value;
        _step = 4;
        _addBotMessage('Budget $_budget. Bagus!\n\nKomoditas pangan apa saja yang biasanya Anda beli? (Pilih satu atau lebih)');
        _loadKomoditas();
        break;
      case 5:
        _prioritas = value;
        _step = 6;
        _generateRekomendasi();
        break;
    }
    setState(() {});
  }

  void _confirmKomoditas() {
    if (_selectedKomSet.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih minimal 1 komoditas!')),
      );
      return;
    }
    _komoditas = _selectedKomSet.toList();
    final preview = _komoditas.length > 5
        ? '${_komoditas.take(5).join(', ')} dan ${_komoditas.length - 5} lainnya'
        : _komoditas.join(', ');
    _addUserMessage(preview);
    setState(() {
      _step = 5;
      _selectedKomSet = {};
    });
    _addBotMessage('Pilihan komoditas sudah dicatat ✅\n\nTerakhir, apa prioritas utama Anda dalam belanja pangan?');
  }

  Future<void> _loadKomoditas() async {
    setState(() => _loadingKomoditas = true);
    try {
      final res = await _dio.get('/chatai/komoditas');
      final data = res.data;
      setState(() {
        _dbKomoditas = List<String>.from(data['komoditas'] ?? []);
        _loadingKomoditas = false;
      });
    } catch (_) {
      _useFallbackKomoditas();
    }
  }

  void _useFallbackKomoditas() {
    setState(() {
      _dbKomoditas = [
        'Beras', 'Telur', 'Cabai', 'Bawang Merah', 'Bawang Putih',
        'Daging Sapi', 'Daging Ayam', 'Ikan', 'Sayuran', 'Minyak Goreng',
        'Gula', 'Tempe', 'Tahu', 'Kentang', 'Wortel',
      ];
      _loadingKomoditas = false;
    });
  }

  Future<void> _generateRekomendasi() async {
    _addBotMessage('Sedang menganalisis data harga pangan...', isLoading: true);
    setState(() => _isLoadingAI = true);

    try {
      final res = await _dio.post('/chatai/rekomendasi', data: {
        'periode': _periode,
        'anggota': _anggota,
        'budget': _budget,
        'komoditas': _komoditas,
        'prioritas': _prioritas,
      });
      _removeLastMessage();
      _addBotMessage(res.data['reply'] ?? 'Maaf, tidak ada jawaban dari AI.');
    } on DioException catch (e) {
      _removeLastMessage();
      _addBotMessage('⚠️ Gagal mendapatkan rekomendasi: ${e.message}');
    } catch (_) {
      _removeLastMessage();
      _addBotMessage('⚠️ Gagal terhubung ke server.');
    }

    setState(() {
      _isLoadingAI = false;
      _step = 99;
    });
  }

  Future<void> _handleFollowUp(String action, String label) async {
    if (action == 'reset') { _resetWizard(); return; }
    if (action == 'resetStep3') {
      setState(() { _budget = null; _komoditas = []; _prioritas = null; _step = 3; });
      _addBotMessage('Baik, mari pilih budget yang berbeda!\n\nBerapa total budget belanja pangan Anda?');
      return;
    }

    _addUserMessage(label);
    _addBotMessage('Sedang mencari informasi...', isLoading: true);

    try {
      final res = await _dio.post('/chatai/followup', data: {'action': action, 'komoditas': _komoditas});
      _removeLastMessage();
      _addBotMessage(res.data['reply'] ?? 'Tidak ada jawaban.');
    } on DioException catch (_) {
      _removeLastMessage();
      _addBotMessage('⚠️ Gagal terhubung. Periksa koneksi internet Anda.');
    }

    setState(() => _step = 99);
  }

  void _resetWizard() {
    setState(() {
      _messages.clear();
      _step = 1;
      _periode = null;
      _anggota = null;
      _budget = null;
      _komoditas = [];
      _prioritas = null;
      _selectedKomSet = {};
      _isLoadingAI = false;
    });
    _startWizard();
  }

  // ═══════════════════════════════════════════════════════════
  // BUILD
  // ═══════════════════════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    final isDark = context.watch<ThemeProvider>().isDarkMode;

    return Scaffold(
      backgroundColor: _bg(isDark),
      appBar: _buildAppBar(),
      body: Column(
        children: [
          _buildStepIndicator(),
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              itemCount: _messages.length,
              itemBuilder: (_, i) => _buildMessageBubble(_messages[i], isDark),
            ),
          ),
          _buildInputArea(isDark),
        ],
      ),
    );
  }

  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: _blue,
      foregroundColor: Colors.white,
      elevation: 0,
      title: Row(
        children: [
          Container(
            width: 34, height: 34,
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.smart_toy_outlined, size: 18, color: Colors.white),
          ),
          const SizedBox(width: 10),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('SIMOPANG AI',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Colors.white)),
              Row(
                children: [
                  Container(
                    width: 6, height: 6,
                    decoration: const BoxDecoration(color: Color(0xFF4ADE80), shape: BoxShape.circle),
                  ),
                  const SizedBox(width: 4),
                  const Text('Aktif & siap membantu',
                      style: TextStyle(fontSize: 11, color: Colors.white70)),
                ],
              ),
            ],
          ),
        ],
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.refresh_rounded, color: Colors.white),
          onPressed: _resetWizard,
          tooltip: 'Mulai Ulang',
        ),
      ],
    );
  }

  Widget _buildStepIndicator() {
    final steps = ['Waktu', 'Anggota', 'Budget', 'Komoditas', 'Prioritas', 'Hasil'];
    final currentStep = _step > 6 ? 6 : _step;

    return Container(
      color: _blue,
      padding: const EdgeInsets.only(bottom: 12, left: 16, right: 16),
      child: Row(
        children: List.generate(steps.length, (i) {
          final stepNum = i + 1;
          final isDone = currentStep > stepNum;
          final isActive = currentStep == stepNum;

          return Expanded(
            child: Row(
              children: [
                Column(
                  children: [
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      width: 22, height: 22,
                      decoration: BoxDecoration(
                        color: isDone
                            ? const Color(0xFF4ADE80)
                            : isActive
                                ? Colors.white
                                : Colors.white.withOpacity(0.2),
                        shape: BoxShape.circle,
                      ),
                      child: Center(
                        child: isDone
                            ? const Icon(Icons.check, size: 12, color: _blue)
                            : Text('$stepNum',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: isActive ? _blue : Colors.white54,
                                )),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      steps[i],
                      style: TextStyle(
                        fontSize: 8,
                        color: isActive || isDone ? Colors.white : Colors.white38,
                        fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
                      ),
                    ),
                  ],
                ),
                if (i < steps.length - 1)
                  Expanded(
                    child: Container(
                      height: 1.5,
                      margin: const EdgeInsets.only(bottom: 14),
                      color: isDone ? const Color(0xFF4ADE80) : Colors.white.withOpacity(0.2),
                    ),
                  ),
              ],
            ),
          );
        }),
      ),
    );
  }

  Widget _buildMessageBubble(ChatMessage msg, bool isDark) {
    final botBubbleBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final userBubbleBg = _blue;

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: msg.isBot ? MainAxisAlignment.start : MainAxisAlignment.end,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          if (msg.isBot) ...[
            Container(
              width: 28, height: 28,
              decoration: BoxDecoration(
                color: _blue.withOpacity(isDark ? 0.3 : 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(Icons.smart_toy_outlined, size: 14,
                  color: isDark ? const Color(0xFF93C5FD) : _blue),
            ),
            const SizedBox(width: 8),
          ],
          Flexible(
            child: Column(
              crossAxisAlignment: msg.isBot ? CrossAxisAlignment.start : CrossAxisAlignment.end,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    color: msg.isBot ? botBubbleBg : userBubbleBg,
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(16),
                      topRight: const Radius.circular(16),
                      bottomLeft: Radius.circular(msg.isBot ? 4 : 16),
                      bottomRight: Radius.circular(msg.isBot ? 16 : 4),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(isDark ? 0.3 : 0.06),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: msg.isLoading
                      ? _buildLoadingDots(isDark)
                      : Text(
                          msg.text,
                          style: TextStyle(
                            fontSize: 13,
                            color: msg.isBot ? _textPrimary(isDark) : Colors.white,
                            height: 1.5,
                          ),
                        ),
                ),
                const SizedBox(height: 3),
                Text(
                  _timeFormat(msg.time),
                  style: TextStyle(fontSize: 10, color: _textMuted(isDark)),
                ),
              ],
            ),
          ),
          if (!msg.isBot) const SizedBox(width: 8),
        ],
      ),
    );
  }

  Widget _buildLoadingDots(bool isDark) {
    return SizedBox(
      height: 20,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: List.generate(3, (i) {
          return TweenAnimationBuilder<double>(
            tween: Tween(begin: 0.4, end: 1.0),
            duration: Duration(milliseconds: 500 + i * 150),
            builder: (_, val, __) => Container(
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: 7, height: 7,
              decoration: BoxDecoration(
                color: (isDark ? const Color(0xFF93C5FD) : _blue).withOpacity(val),
                shape: BoxShape.circle,
              ),
            ),
          );
        }),
      ),
    );
  }

  Widget _buildInputArea(bool isDark) {
    return Container(
      // FIX: Hilangkan overflow kuning — gunakan constraints + SafeArea tanpa overflow
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.38,
      ),
      decoration: BoxDecoration(
        color: _inputBg(isDark),
        border: Border(
          top: BorderSide(color: _divider(isDark), width: 1),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(isDark ? 0.3 : 0.06),
            blurRadius: 12,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        // FIX: minimum: EdgeInsets.zero agar tidak ada padding bottom berlebih
        minimum: EdgeInsets.zero,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(12),
          child: _buildChoicesForStep(isDark),
        ),
      ),
    );
  }

  Widget _buildChoicesForStep(bool isDark) {
    if (_isLoadingAI) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Center(
          child: Text(
            'AI sedang menganalisis...',
            style: TextStyle(color: _textMuted(isDark), fontSize: 13),
          ),
        ),
      );
    }

    switch (_step) {
      case 1:  return _buildChoiceButtons(_periodeChoices, isDark);
      case 2:  return _buildChoiceButtons(_anggotaChoices, isDark);
      case 3:  return _buildChoiceButtons(_budgetChoices, isDark);
      case 4:  return _buildKomoditasSelector(isDark);
      case 5:  return _buildChoiceButtons(_prioritasChoices, isDark);
      case 99: return _buildFollowUpButtons(isDark);
      default: return const SizedBox.shrink();
    }
  }

  Widget _buildChoiceButtons(List<Map<String, String>> choices, bool isDark) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: choices.map((c) {
        return GestureDetector(
          onTap: () => _handleChoice(c['label']!, c['value']!),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: _chipBg(isDark),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: _chipBorder(isDark)),
            ),
            child: Text(
              c['label']!,
              style: TextStyle(
                fontSize: 13,
                color: _chipText(isDark),
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildKomoditasSelector(bool isDark) {
    if (_loadingKomoditas) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 16),
        child: Center(
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              SizedBox(
                width: 16, height: 16,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: isDark ? const Color(0xFF93C5FD) : _blue,
                ),
              ),
              const SizedBox(width: 8),
              Text('Memuat komoditas...',
                  style: TextStyle(color: _textMuted(isDark), fontSize: 13)),
            ],
          ),
        ),
      );
    }

    const rowCount = 3;
    const rowHeight = 34.0;
    const gridHeight = (rowHeight * rowCount) + (6.0 * (rowCount - 1));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Geser → untuk lihat semua (${_dbKomoditas.length})',
              style: TextStyle(fontSize: 11, color: _textMuted(isDark)),
            ),
            Text(
              '${_selectedKomSet.length} dipilih',
              style: TextStyle(
                fontSize: 11,
                color: isDark ? const Color(0xFF93C5FD) : _blue,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),

        // Grid komoditas — FIX overflow dengan SizedBox tinggi tetap
        SizedBox(
          height: gridHeight,
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: List.generate(rowCount, (row) {
                final rowItems = <Widget>[];
                for (int col = 0; col * rowCount + row < _dbKomoditas.length; col++) {
                  final nama = _dbKomoditas[col * rowCount + row];
                  final isSelected = _selectedKomSet.contains(nama);
                  rowItems.add(
                    Padding(
                      padding: const EdgeInsets.only(right: 8, bottom: 6),
                      child: GestureDetector(
                        onTap: () => setState(() {
                          if (isSelected) _selectedKomSet.remove(nama);
                          else _selectedKomSet.add(nama);
                        }),
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 150),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: isSelected
                                ? _blue
                                : _chipBg(isDark),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: isSelected ? _blue : _chipBorder(isDark),
                            ),
                          ),
                          child: Text(
                            nama,
                            style: TextStyle(
                              fontSize: 12,
                              color: isSelected ? Colors.white : _chipText(isDark),
                              fontWeight: isSelected ? FontWeight.w600 : FontWeight.w400,
                            ),
                          ),
                        ),
                      ),
                    ),
                  );
                }
                return Row(children: rowItems);
              }),
            ),
          ),
        ),

        const SizedBox(height: 8),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: _selectedKomSet.isEmpty ? null : _confirmKomoditas,
            style: ElevatedButton.styleFrom(
              backgroundColor: _blue,
              disabledBackgroundColor: isDark ? Colors.grey[700] : Colors.grey[300],
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              padding: const EdgeInsets.symmetric(vertical: 12),
              elevation: 0,
            ),
            child: Text(
              _selectedKomSet.isEmpty
                  ? 'Pilih minimal 1 komoditas'
                  : '✓ Lanjutkan (${_selectedKomSet.length} dipilih)',
              style: const TextStyle(fontWeight: FontWeight.w600, color: Colors.white),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildFollowUpButtons(bool isDark) {
    final options = [
      {'label': '🔄 Coba Budget Berbeda', 'action': 'resetStep3'},
      {'label': '📊 Komoditas Murah Sekarang?', 'action': 'cheapNow'},
      {'label': '📦 Tips Menyimpan Stok?', 'action': 'storageTips'},
      {'label': '🔁 Mulai Ulang Wizard', 'action': 'reset'},
    ];

    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: options.map((o) {
        return GestureDetector(
          onTap: () => _handleFollowUp(o['action']!, o['label']!),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: _chipBg(isDark),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: _chipBorder(isDark)),
            ),
            child: Text(
              o['label']!,
              style: TextStyle(
                fontSize: 13,
                color: _chipText(isDark),
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        );
      }).toList(),
    );
  }
}