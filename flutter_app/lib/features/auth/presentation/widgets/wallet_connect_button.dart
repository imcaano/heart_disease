import 'package:flutter/material.dart';
import '../../../../core/providers/web3_provider.dart';
import '../../../../core/theme/app_theme.dart';

class WalletConnectButton extends StatefulWidget {
  final Function(String) onConnected;

  const WalletConnectButton({super.key, required this.onConnected});

  @override
  State<WalletConnectButton> createState() => _WalletConnectButtonState();
}

class _WalletConnectButtonState extends State<WalletConnectButton> {
  bool _isConnecting = false;
  String? _address;

  Future<void> _connectWallet() async {
    setState(() {
      _isConnecting = true;
    });

    final address = await Web3Provider.connectMetaMask();
    if (address != null) {
      setState(() {
        _address = address;
      });
      widget.onConnected(address);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
              'Connected: \\${address.substring(0, 6)}...\\${address.substring(address.length - 4)}'),
          backgroundColor: AppTheme.successColor,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('MetaMask not connected or not installed!'),
          backgroundColor: AppTheme.dangerColor,
        ),
      );
    }

    setState(() {
      _isConnecting = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_address != null) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppTheme.successColor.withOpacity(0.1),
          border: Border.all(color: AppTheme.successColor),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          children: [
            const Icon(Icons.check_circle, color: AppTheme.successColor, size: 20),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Wallet Connected',
                    style: TextStyle(
                      color: AppTheme.successColor,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  Text(
                    _address!,
                    style: TextStyle(
                      color: AppTheme.successColor.withOpacity(0.8),
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
            IconButton(
              icon: const Icon(Icons.refresh),
              onPressed: _connectWallet,
              color: AppTheme.successColor,
            ),
          ],
        ),
      );
    }

    return OutlinedButton.icon(
      onPressed: _isConnecting ? null : _connectWallet,
      icon: _isConnecting
          ? const SizedBox(
              width: 16,
              height: 16,
              child: CircularProgressIndicator(strokeWidth: 2),
            )
          : const Icon(Icons.account_balance_wallet),
      label: Text(_isConnecting ? 'Connecting...' : 'Connect MetaMask'),
      style: OutlinedButton.styleFrom(
        padding: const EdgeInsets.symmetric(vertical: 16),
        side: const BorderSide(color: AppTheme.primaryColor),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }
}
