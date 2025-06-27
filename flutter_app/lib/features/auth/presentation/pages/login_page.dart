import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../../core/routes/app_router.dart';
import '../../../../core/providers/auth_provider.dart';
import '../../../../core/providers/web3_provider.dart';
import '../../../../core/theme/app_theme.dart';
import '../widgets/wallet_connect_button.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _walletAddressController = TextEditingController();

  bool _isLoading = false;
  bool _obscurePassword = true;

  @override
  void initState() {
    super.initState();
    _initializeWeb3();
  }

  Future<void> _initializeWeb3() async {
    final web3Provider = context.read<Web3Provider>();
    await web3Provider.initialize();
  }

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    _walletAddressController.dispose();
    super.dispose();
  }

  Future<void> _connectWallet() async {
    final web3Provider = context.read<Web3Provider>();

    try {
      setState(() {
        _isLoading = true;
      });

      await web3Provider.initialize();
      await web3Provider.connectWallet();

      if (web3Provider.isConnected && web3Provider.currentAddress != null) {
        setState(() {
          _walletAddressController.text = web3Provider.currentAddress!;
        });

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content:
                Text('MetaMask connected: ${web3Provider.getShortAddress()}'),
            backgroundColor: AppTheme.successColor,
            duration: const Duration(seconds: 3),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(web3Provider.error ?? 'Failed to connect MetaMask'),
            backgroundColor: AppTheme.dangerColor,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Failed to connect wallet: $e'),
          backgroundColor: AppTheme.dangerColor,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _login() async {
    if (_walletAddressController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please connect your MetaMask wallet.'),
          backgroundColor: AppTheme.dangerColor,
        ),
      );
      return;
    }
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final authProvider = context.read<AuthProvider>();
      final success = await authProvider.login(
        _usernameController.text.trim(),
        _passwordController.text,
        _walletAddressController.text.trim(),
      );

      if (success && mounted) {
        print('User role: \\${authProvider.currentUser?.role}');
        print('Is admin: \\${authProvider.currentUser?.isAdmin}');
        if (authProvider.currentUser?.isAdmin == true) {
          AppRouter.navigateToAndClear(AppRouter.adminDashboard);
        } else {
          AppRouter.navigateToAndClear(AppRouter.dashboard);
        }
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(authProvider.error ??
                'Login failed. Please check your credentials.'),
            backgroundColor: AppTheme.dangerColor,
            duration: const Duration(seconds: 5),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Login error: $e'),
            backgroundColor: AppTheme.dangerColor,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              AppTheme.primaryColor.withOpacity(0.1),
              AppTheme.primaryLight.withOpacity(0.05),
            ],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Logo and Title
                  Container(
                    width: 100,
                    height: 100,
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor,
                      borderRadius: BorderRadius.circular(25),
                      boxShadow: [
                        BoxShadow(
                          color: AppTheme.primaryColor.withOpacity(0.3),
                          blurRadius: 20,
                          offset: const Offset(0, 10),
                        ),
                      ],
                    ),
                    child: const Icon(
                      Icons.favorite,
                      size: 50,
                      color: Colors.white,
                    ),
                  ),

                  const SizedBox(height: 24),

                  Text(
                    'Welcome Back',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: AppTheme.darkColor,
                        ),
                  ),

                  const SizedBox(height: 8),

                  Text(
                    'Sign in to your account',
                    style: Theme.of(
                      context,
                    ).textTheme.bodyLarge?.copyWith(color: AppTheme.gray600),
                  ),

                  const SizedBox(height: 40),

                  // Login Form
                  Card(
                    elevation: 8,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            // Username Field
                            TextFormField(
                              controller: _usernameController,
                              decoration: const InputDecoration(
                                labelText: 'Username',
                                prefixIcon: Icon(Icons.person),
                                border: OutlineInputBorder(),
                              ),
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'Please enter your username';
                                }
                                return null;
                              },
                            ),

                            const SizedBox(height: 16),

                            // Password Field
                            TextFormField(
                              controller: _passwordController,
                              obscureText: _obscurePassword,
                              decoration: InputDecoration(
                                labelText: 'Password',
                                prefixIcon: const Icon(Icons.lock),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _obscurePassword
                                        ? Icons.visibility
                                        : Icons.visibility_off,
                                  ),
                                  onPressed: () {
                                    setState(() {
                                      _obscurePassword = !_obscurePassword;
                                    });
                                  },
                                ),
                                border: const OutlineInputBorder(),
                              ),
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'Please enter your password';
                                }
                                if (value.length < 6) {
                                  return 'Password must be at least 6 characters';
                                }
                                return null;
                              },
                            ),

                            const SizedBox(height: 16),

                            // Wallet Address Field
                            TextFormField(
                              controller: _walletAddressController,
                              readOnly: true,
                              decoration: InputDecoration(
                                labelText: 'Wallet Address',
                                prefixIcon: const Icon(
                                  Icons.account_balance_wallet,
                                ),
                                suffixIcon: IconButton(
                                  icon: const Icon(Icons.qr_code_scanner),
                                  onPressed: _connectWallet,
                                ),
                                border: const OutlineInputBorder(),
                              ),
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'Please connect your MetaMask wallet';
                                }
                                if (!value.startsWith('0x') ||
                                    value.length != 42) {
                                  return 'Please connect a valid Ethereum address';
                                }
                                return null;
                              },
                            ),

                            const SizedBox(height: 24),

                            // Login Button
                            ElevatedButton(
                              onPressed: _isLoading ? null : _login,
                              style: ElevatedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(
                                  vertical: 16,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                              child: _isLoading
                                  ? const SizedBox(
                                      height: 20,
                                      width: 20,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        valueColor:
                                            AlwaysStoppedAnimation<Color>(
                                          Colors.white,
                                        ),
                                      ),
                                    )
                                  : const Text(
                                      'Sign In',
                                      style: TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                            ),

                            const SizedBox(height: 16),

                            // Connect Wallet Button
                            WalletConnectButton(
                              onConnected: (address) {
                                setState(() {
                                  _walletAddressController.text = address;
                                });
                              },
                            ),

                            const SizedBox(height: 24),

                            // Sign Up Link
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(
                                  "Don't have an account? ",
                                  style: Theme.of(context).textTheme.bodyMedium,
                                ),
                                TextButton(
                                  onPressed: () {
                                    AppRouter.navigateTo(AppRouter.signup);
                                  },
                                  child: const Text('Sign Up'),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
