import 'package:flutter/material.dart';
import 'package:flutter_app/providers/commodity_provider.dart';
import 'package:flutter_app/widgets/loading_widget.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class PredictionScreen extends StatefulWidget {
  const PredictionScreen({super.key});

  @override
  State<PredictionScreen> createState() => _PredictionScreenState();
}

class _PredictionScreenState extends State<PredictionScreen> {
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
    final commodityProvider =
        Provider.of<CommodityProvider>(context, listen: false);
    await commodityProvider.loadCommodities();
  }

  Future<void> _handlePredict() async {
    if (_selectedCommodityId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pilih komoditas terlebih dahulu'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    if (_quantityController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Masukkan jumlah konsumsi'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    final quantity = double.tryParse(_quantityController.text) ?? 0;
    if (quantity <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Jumlah konsumsi harus lebih dari 0'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    final commodityProvider =
        Provider.of<CommodityProvider>(context, listen: false);
    final success =
        await commodityProvider.predictPrice(_selectedCommodityId!, quantity);

    if (!mounted) return;

    if (success) {
      // Show result in dialog
      _showPredictionResult(commodityProvider.predictionResult);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(commodityProvider.errorMessage ?? 'Prediksi gagal'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  void _showPredictionResult(Map<String, dynamic>? result) {
    if (result == null) return;

    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Hasil Prediksi'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildResultItem(
                'Prediksi Harga',
                currencyFormat.format(result['predicted_price']),
              ),
              const SizedBox(height: 12),
              _buildResultItem(
                'Periode',
                result['period'] ?? 'Minggu depan',
              ),
              const SizedBox(height: 12),
              _buildResultItem(
                'Estimasi Total Biaya',
                currencyFormat.format(result['total_cost']),
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF1976D2).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.info_outline,
                      size: 20,
                      color: Color(0xFF1976D2),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Prediksi ini berdasarkan data historis dan metode time series',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Tutup'),
            ),
          ],
        );
      },
    );
  }

  Widget _buildResultItem(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            color: Colors.grey.shade600,
          ),
        ),
        Text(
          value,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 16,
          ),
        ),
      ],
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
      builder: (context, commodityProvider, child) {
        return Scaffold(
          body: Stack(
            children: [
              SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [
                            Color(0xFF1976D2),
                            Color(0xFF64B5F6),
                          ],
                        ),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Simulasi Prediksi Harga',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Lakukan simulasi prediksi harga pangan untuk periode mendatang',
                            style: TextStyle(
                              color: Colors.white.withOpacity(0.9),
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Form
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Parameter Prediksi',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Commodity Selection
                            const Text(
                              'Pilih Komoditas',
                              style: TextStyle(
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Container(
                              padding:
                                  const EdgeInsets.symmetric(horizontal: 12),
                              decoration: BoxDecoration(
                                border: Border.all(color: Colors.grey.shade300),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: DropdownButtonHideUnderline(
                                child: DropdownButton<String>(
                                  value: _selectedCommodityId,
                                  isExpanded: true,
                                  hint: const Text('Pilih komoditas'),
                                  items: commodityProvider.commodities
                                      .map((commodity) {
                                    return DropdownMenuItem(
                                      value: commodity.id,
                                      child: Text(commodity.name),
                                    );
                                  }).toList(),
                                  onChanged: (value) {
                                    setState(() {
                                      _selectedCommodityId = value;
                                    });
                                  },
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Quantity Input
                            const Text(
                              'Jumlah Konsumsi (kg/minggu)',
                              style: TextStyle(
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 8),
                            TextField(
                              controller: _quantityController,
                              keyboardType: TextInputType.number,
                              decoration: const InputDecoration(
                                hintText: 'Contoh: 5',
                                suffixText: 'kg/minggu',
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Information Card
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(16),
                        child: Column(
                          children: [
                            Row(
                              children: [
                                Icon(
                                  Icons.lightbulb_outline,
                                  color: Color(0xFF1976D2),
                                ),
                                SizedBox(width: 8),
                                Text(
                                  'Informasi',
                                  style: TextStyle(
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                            SizedBox(height: 12),
                            Text(
                              'Prediksi menggunakan metode Time Series (ARIMA/SARIMA) berdasarkan data harga 3 bulan terakhir. Hasil prediksi akan menampilkan estimasi harga untuk minggu depan dan total biaya berdasarkan jumlah konsumsi yang dimasukkan.',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Predict Button
                    ElevatedButton(
                      onPressed:
                          commodityProvider.isLoading ? null : _handlePredict,
                      style: ElevatedButton.styleFrom(
                        minimumSize: const Size(double.infinity, 50),
                      ),
                      child: const Text(
                        'Prediksi Sekarang',
                        style: TextStyle(fontSize: 16),
                      ),
                    ),

                    // History of Predictions (optional)
                    if (commodityProvider.predictionResult != null) ...[
                      const SizedBox(height: 30),
                      const Text(
                        'Riwayat Prediksi Terakhir',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Card(
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor:
                                const Color(0xFF1976D2).withOpacity(0.1),
                            child: const Icon(
                              Icons.history,
                              color: Color(0xFF1976D2),
                            ),
                          ),
                          title: Text(
                            DateFormat('dd MMM yyyy').format(DateTime.now()),
                          ),
                          subtitle: Text(
                            '${commodityProvider.selectedCommodity?.name ?? 'Komoditas'} - ${currencyFormat.format(commodityProvider.predictionResult?['predicted_price'])}',
                          ),
                          trailing:
                              const Icon(Icons.arrow_forward_ios, size: 16),
                          onTap: () {
                            _showPredictionResult(
                                commodityProvider.predictionResult);
                          },
                        ),
                      ),
                    ],
                  ],
                ),
              ),

              // Loading Overlay
              if (commodityProvider.isLoading) const LoadingWidget(),
            ],
          ),
        );
      },
    );
  }
}
