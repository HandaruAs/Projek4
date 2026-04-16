import 'package:flutter/material.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class UserPredictionScreen extends StatefulWidget {
  const UserPredictionScreen({super.key});

  @override
  State<UserPredictionScreen> createState() => _UserPredictionScreenState();
}

class _UserPredictionScreenState extends State<UserPredictionScreen> {
  String? _selectedCommodityId;
  final TextEditingController _quantityController = TextEditingController();

  final NumberFormat currencyFormat = NumberFormat.currency(
    locale: 'id',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  @override
  void initState() {
    super.initState();
    _loadCommodities();
  }

  Future<void> _loadCommodities() async {
    final provider =
        Provider.of<CommodityProvider>(context, listen: false);
    await provider.loadCommodities();
  }

  Future<void> _handlePredict() async {
    if (_selectedCommodityId == null) {
      _showSnack('Pilih komoditas terlebih dahulu');
      return;
    }

    if (_quantityController.text.isEmpty) {
      _showSnack('Masukkan jumlah konsumsi');
      return;
    }

    final quantity = double.tryParse(_quantityController.text) ?? 0;

    if (quantity <= 0) {
      _showSnack('Jumlah konsumsi harus lebih dari 0');
      return;
    }

    final provider =
        Provider.of<CommodityProvider>(context, listen: false);

    final success =
        await provider.predictPrice(_selectedCommodityId!, quantity);

    if (!mounted) return;

    if (success) {
      _showPredictionResult(provider.predictionResult);
    } else {
      _showSnack(provider.errorMessage ?? 'Prediksi gagal', isError: true);
    }
  }

  void _showSnack(String msg, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: isError ? Colors.red : Colors.orange,
      ),
    );
  }

  void _showPredictionResult(Map<String, dynamic>? result) {
    if (result == null) return;

    showDialog(
      context: context,
      builder: (_) {
        return AlertDialog(
          title: const Text('Hasil Prediksi'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _item('Prediksi Harga',
                  currencyFormat.format(result['predicted_price'])),
              _item('Periode', result['period'] ?? 'Minggu depan'),
              _item('Total Biaya',
                  currencyFormat.format(result['total_cost'])),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text("Tutup"),
            )
          ],
        );
      },
    );
  }

  Widget _item(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _quantityController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<CommodityProvider>(
      builder: (context, provider, _) {
        return Scaffold(
          body: Stack(
            children: [
              SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [

                    /// HEADER
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF1976D2), Color(0xFF64B5F6)],
                        ),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Text(
                        'Simulasi Prediksi Harga',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),

                    const SizedBox(height: 20),

                    /// FORM
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [

                            const Text('Pilih Komoditas'),
                            const SizedBox(height: 8),

                            DropdownButtonFormField<String>(
                              isExpanded: true,
                              value: _selectedCommodityId,
                              items: provider.commodities.map((c) {
                                return DropdownMenuItem(
                                  value: c.id,
                                  child: Text(c.name),
                                );
                              }).toList(),
                              onChanged: (val) {
                                setState(() {
                                  _selectedCommodityId = val;
                                });
                              },
                              decoration: const InputDecoration(
                                border: OutlineInputBorder(),
                              ),
                            ),

                            const SizedBox(height: 16),

                            const Text('Jumlah (kg/minggu)'),
                            const SizedBox(height: 8),

                            TextField(
                              controller: _quantityController,
                              keyboardType: TextInputType.number,
                              decoration: const InputDecoration(
                                border: OutlineInputBorder(),
                                hintText: 'Contoh: 5',
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 20),

                    ElevatedButton(
                      onPressed:
                          provider.isLoading ? null : _handlePredict,
                      style: ElevatedButton.styleFrom(
                        minimumSize: const Size(double.infinity, 50),
                      ),
                      child: const Text("Prediksi Sekarang"),
                    ),
                  ],
                ),
              ),

              if (provider.isLoading) const LoadingWidget(),
            ],
          ),
        );
      },
    );
  }
}