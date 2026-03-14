import 'package:flutter/material.dart';
import 'package:flutter_app/models/commodity_model.dart';
import 'package:intl/intl.dart';

class CommodityCard extends StatelessWidget {
  final CommodityModel commodity;

  const CommodityCard({
    super.key,
    required this.commodity,
  });

  @override
  Widget build(BuildContext context) {
    final currencyFormat = NumberFormat.currency(
      locale: 'id',
      symbol: 'Rp ',
      decimalDigits: 0,
    );

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () {
          // Navigate to detail handled by parent
        },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Icon/Image
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  color: const Color(0xFF1976D2).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  _getCommodityIcon(commodity.name),
                  color: const Color(0xFF1976D2),
                  size: 28,
                ),
              ),
              const SizedBox(width: 16),

              // Commodity Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      commodity.name,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Harga terbaru',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),

              // Price and Change
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    currencyFormat.format(commodity.currentPrice),
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1976D2),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: commodity.isIncreasing
                          ? Colors.green.shade50
                          : Colors.red.shade50,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          commodity.isIncreasing
                              ? Icons.arrow_upward
                              : Icons.arrow_downward,
                          size: 12,
                          color: commodity.isIncreasing
                              ? Colors.green.shade600
                              : Colors.red.shade600,
                        ),
                        const SizedBox(width: 2),
                        Text(
                          commodity.changePercentage,
                          style: TextStyle(
                            fontSize: 11,
                            color: commodity.isIncreasing
                                ? Colors.green.shade600
                                : Colors.red.shade600,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  IconData _getCommodityIcon(String commodityName) {
    final name = commodityName.toLowerCase();

    if (name.contains('beras')) {
      return Icons.grass;
    } else if (name.contains('bawang')) {
      return Icons.circle;
    } else if (name.contains('cabai') || name.contains('cabe')) {
      return Icons.eco;
    } else if (name.contains('telur')) {
      return Icons.circle;
    } else if (name.contains('daging')) {
      return Icons.restaurant;
    } else if (name.contains('minyak')) {
      return Icons.opacity;
    } else if (name.contains('gula')) {
      return Icons.square;
    } else {
      return Icons.shopping_basket;
    }
  }
}
