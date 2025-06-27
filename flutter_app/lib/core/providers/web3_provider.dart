import 'dart:js_util' as js_util;
import 'dart:html';
import 'package:flutter/foundation.dart';
import 'package:web3dart/web3dart.dart';
import 'package:http/http.dart' as http;

class Web3Provider extends ChangeNotifier {
  Web3Client? _client;
  bool _isConnected = false;
  bool _isLoading = false;
  bool _isConnecting = false;
  String? _error;
  String? _currentAddress;
  String? _networkName;
  BigInt? _balance;

  Web3Client? get client => _client;
  bool get isConnected => _isConnected;
  bool get isLoading => _isLoading;
  bool get isConnecting => _isConnecting;
  String? get error => _error;
  String? get currentAddress => _currentAddress;
  String? get networkName => _networkName;
  BigInt? get balance => _balance;

  // Initialize Web3
  Future<void> initialize() async {
    try {
      _isLoading = true;
      notifyListeners();

      // Check if MetaMask is available
      if (!await _isMetaMaskAvailable()) {
        throw Exception(
            'MetaMask is not available. Please install MetaMask extension.');
      }

      // Connect to Ethereum network
      _client = Web3Client(
          'https://mainnet.infura.io/v3/YOUR_PROJECT_ID', http.Client());
      _isConnected = true;
      _error = null;
    } catch (e) {
      _error = 'Failed to initialize Web3: $e';
      _isConnected = false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Check if MetaMask is available
  Future<bool> _isMetaMaskAvailable() async {
    try {
      // Check if ethereum object exists in window
      final ethereum = js_util.getProperty(window, 'ethereum');
      return ethereum != null;
    } catch (e) {
      return false;
    }
  }

  // Connect to MetaMask wallet
  Future<void> connectWallet() async {
    try {
      _isConnecting = true;
      notifyListeners();

      // Check if MetaMask is available
      if (!await _isMetaMaskAvailable()) {
        throw Exception(
            'MetaMask is not available. Please install MetaMask extension.');
      }

      // Request account access - this will pop up MetaMask
      final ethereum = js_util.getProperty(window, 'ethereum');
      final accounts = await _requestAccounts(ethereum);

      if (accounts.isNotEmpty) {
        _currentAddress = accounts[0];
        await _updateNetworkInfo(ethereum);
        await _updateBalance();

        _isConnected = true;
        _error = null;
      } else {
        throw Exception('No accounts found. Please unlock MetaMask.');
      }
    } catch (e) {
      _error = 'Failed to connect wallet: $e';
      _isConnected = false;
    } finally {
      _isConnecting = false;
      notifyListeners();
    }
  }

  // Request accounts from MetaMask - this triggers the popup
  Future<List<String>> _requestAccounts(Object ethereum) async {
    try {
      final accounts = await js_util.promiseToFuture(
        js_util.callMethod(ethereum, 'request', [
          js_util.jsify({'method': 'eth_requestAccounts'})
        ]),
      );
      if (accounts is List && accounts.isNotEmpty) {
        return accounts.cast<String>();
      }
      return [];
    } catch (e) {
      throw Exception('Failed to request accounts: $e');
    }
  }

  // Update network information
  Future<void> _updateNetworkInfo(Object ethereum) async {
    try {
      final chainId = await js_util.promiseToFuture(
        js_util.callMethod(ethereum, 'request', [
          js_util.jsify({'method': 'eth_chainId'})
        ]),
      );
      _networkName = _getNetworkName(int.parse(chainId.toString(), radix: 16));
      notifyListeners();
    } catch (e) {
      _networkName = 'Unknown Network';
      notifyListeners();
    }
  }

  // Update wallet balance
  Future<void> _updateBalance() async {
    if (_currentAddress == null || _client == null) return;

    try {
      final address = EthereumAddress.fromHex(_currentAddress!);
      final balance = await _client!.getBalance(address);
      _balance = balance.getInWei;
      notifyListeners();
    } catch (e) {
      print('Failed to get balance: $e');
    }
  }

  // Get network name from chain ID
  String _getNetworkName(int chainId) {
    switch (chainId) {
      case 1:
        return 'Ethereum Mainnet';
      case 3:
        return 'Ropsten Testnet';
      case 4:
        return 'Rinkeby Testnet';
      case 5:
        return 'Goerli Testnet';
      case 42:
        return 'Kovan Testnet';
      case 56:
        return 'Binance Smart Chain';
      case 97:
        return 'Binance Smart Chain Testnet';
      case 137:
        return 'Polygon';
      case 80001:
        return 'Polygon Mumbai Testnet';
      default:
        return 'Unknown Network';
    }
  }

  // Disconnect wallet
  void disconnectWallet() {
    _isConnected = false;
    _currentAddress = null;
    _networkName = null;
    _balance = null;
    _error = null;
    notifyListeners();
  }

  // Sign message
  Future<String?> signMessage(String message) async {
    if (_currentAddress == null) {
      _error = 'Wallet not connected';
      notifyListeners();
      return null;
    }

    try {
      final ethereum = js_util.getProperty(window, 'ethereum');
      final result = await ethereum.callMethod('request', [
        {
          'method': 'personal_sign',
          'params': [message, _currentAddress]
        }
      ]);

      return result.toString();
    } catch (e) {
      _error = 'Failed to sign message: $e';
      notifyListeners();
      return null;
    }
  }

  // Verify signature
  Future<String?> verifySignature(String message, String signature) async {
    try {
      // In a real implementation, this would verify the signature
      // For now, we'll return the current address
      return _currentAddress;
    } catch (e) {
      _error = 'Failed to verify signature: $e';
      notifyListeners();
      return null;
    }
  }

  // Send transaction
  Future<String?> sendTransaction(String toAddress, BigInt value) async {
    if (_currentAddress == null) {
      _error = 'Wallet not connected';
      notifyListeners();
      return null;
    }

    try {
      final ethereum = js_util.getProperty(window, 'ethereum');
      final result = await ethereum.callMethod('request', [
        {
          'method': 'eth_sendTransaction',
          'params': [
            {
              'from': _currentAddress,
              'to': toAddress,
              'value': '0x${value.toRadixString(16)}'
            }
          ]
        }
      ]);

      await _updateBalance();
      return result.toString();
    } catch (e) {
      _error = 'Failed to send transaction: $e';
      notifyListeners();
      return null;
    }
  }

  // Get short address for display
  String getShortAddress() {
    if (_currentAddress == null) return '';
    if (_currentAddress!.length <= 10) return _currentAddress!;
    return '${_currentAddress!.substring(0, 6)}...${_currentAddress!.substring(_currentAddress!.length - 4)}';
  }

  // Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }

  @override
  void dispose() {
    _client?.dispose();
    super.dispose();
  }

  // Use this for Chrome extension MetaMask support
  static Future<String?> connectMetaMask() async {
    try {
      final ethereum = js_util.getProperty(window, 'ethereum');
      if (ethereum == null) {
        throw Exception(
            'MetaMask is not installed or not detected in this browser context.');
      }
      final accounts = await js_util.promiseToFuture(
        js_util.callMethod(ethereum, 'request', [
          js_util.jsify({'method': 'eth_requestAccounts'})
        ]),
      );
      if (accounts is List && accounts.isNotEmpty) {
        return accounts[0] as String;
      }
      return null;
    } catch (e) {
      print('MetaMask connection error: $e');
      return null;
    }
  }
}
