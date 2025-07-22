import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../../core/routes/app_router.dart';
import '../../../../core/providers/auth_provider.dart';
import '../../../../core/theme/app_theme.dart';
import 'dart:math';

class SignupPage extends StatefulWidget {
  const SignupPage({super.key});

  @override
  State<SignupPage> createState() => _SignupPageState();
}

class _SignupPageState extends State<SignupPage> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  @override
  void initState() {
    super.initState();
    // Initialize Web3 after the widget is built to avoid setState during build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      // _initializeWeb3(); // Removed
    });
  }

  // Future<void> _initializeWeb3() async { // Removed
  //   try { // Removed
  //     final web3Provider = context.read<Web3Provider>(); // Removed
  //     await web3Provider.initialize(); // Removed
  //   } catch (e) { // Removed
  //     // Handle initialization error silently // Removed
  //     print('Web3 initialization error: $e'); // Removed
  //   } // Removed
  // } // Removed

  @override
  void dispose() {
    _usernameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  // Future<void> _connectWallet() async { // Removed
  //   try { // Removed
  //     setState(() { // Removed
  //       _isLoading = true; // Removed
  //     }); // Removed
  //     final wcClient = WalletConnectV2( // Removed
  //       projectId: 'YOUR_PROJECT_ID', // Removed
  //       relayUrl: 'wss://relay.walletconnect.com', // Removed
  //       metadata: PairingMetadata( // Removed
  //         name: 'Heart Disease App', // Removed
  //         description: 'Login with MetaMask', // Removed
  //         url: 'https://myapp.com', // Removed
  //         icons: ['https://myapp.com/icon.png'], // Removed
  //       ), // Removed
  //     ); // Removed
  //     final session = await wcClient.connect( // Removed
  //       requiredNamespaces: { // Removed
  //         'eip155': RequiredNamespace( // Removed
  //           chains: ['eip155:1'], // Removed
  //           methods: [ // Removed
  //             'eth_sendTransaction', // Removed
  //             'personal_sign', // Removed
  //             'eth_signTypedData' // Removed
  //           ], // Removed
  //           events: ['accountsChanged', 'chainChanged'], // Removed
  //         ), // Removed
  //       }, // Removed
  //       onDisplayUri: (uri) async { // Removed
  //         await launchUrl(Uri.parse(uri), mode: LaunchMode.externalApplication); // Removed
  //       }, // Removed
  //     ); // Removed
  //     final walletAddress = session.accounts.first; // Removed
  //     setState(() { // Removed
  //       _walletAddressController.text = walletAddress; // Removed
  //     }); // Removed
  //     ScaffoldMessenger.of(context).showSnackBar( // Removed
  //       SnackBar( // Removed
  //           content: Text('MetaMask connected: $walletAddress'), // Removed
  //           backgroundColor: AppTheme.successColor), // Removed
  //     ); // Removed
  //   } catch (e) { // Removed
  //     ScaffoldMessenger.of(context).showSnackBar( // Removed
  //       SnackBar( // Removed
  //           content: Text('Failed to connect wallet: $e'), // Removed
  //           backgroundColor: AppTheme.dangerColor), // Removed
  //     ); // Removed
  //   } finally { // Removed
  //     setState(() { // Removed
  //       _isLoading = false; // Removed
  //     }); // Removed
  //   } // Removed
  // } // Removed

  // Remove all code that references Web3Provider, WalletConnectButton, or WalletConnectV2 for now.
  // TODO: Implement WalletConnect logic using wallet_connect_v2 package here.
  // For now, use a placeholder function for _connectWallet.
  Future<void> _connectWallet() async {
    // This function is no longer needed as wallet connection is removed.
    // Keeping it here for now, but it will be removed in a subsequent edit.
  }

  String _generateRandomWalletAddress() {
    final rand = Random.secure();
    final bytes = List<int>.generate(20, (_) => rand.nextInt(256));
    return '0x' + bytes.map((b) => b.toRadixString(16).padLeft(2, '0')).join();
  }

  Future<void> _signup() async {
    if (!_formKey.currentState!.validate()) return;

    if (_passwordController.text != _confirmPasswordController.text) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Passwords do not match'),
          backgroundColor: AppTheme.dangerColor,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final authProvider = context.read<AuthProvider>();
      final success = await authProvider.signup(
        _usernameController.text.trim(),
        _emailController.text.trim(),
        _passwordController.text,
      );

      if (success && mounted) {
        // Pass a message to the login page and navigate
        Navigator.of(context).pushReplacementNamed(
          AppRouter.login,
          arguments: 'Account created successfully! Please login.',
        );
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content:
                Text(authProvider.error ?? 'Signup failed. Please try again.'),
            backgroundColor: AppTheme.dangerColor,
            duration: const Duration(seconds: 5),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Signup error: $e'),
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
      body: Stack(
        children: [
          // Animated gradient background (copied from login)
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  Color.lerp(
                      AppTheme.primaryColor, AppTheme.primaryLight, 0.7)!,
                  Color.lerp(
                      AppTheme.primaryColor, AppTheme.successColor, 0.3)!,
                ],
              ),
            ),
          ),
          Center(
                child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
              child: Container(
                constraints: const BoxConstraints(maxWidth: 400),
                padding: const EdgeInsets.all(32),
                        decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.93),
                  borderRadius: BorderRadius.circular(28),
                          boxShadow: [
                            BoxShadow(
                      color: Colors.black.withOpacity(0.08),
                      blurRadius: 32,
                      offset: const Offset(0, 12),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 72,
                      height: 72,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: LinearGradient(
                          colors: [
                            AppTheme.primaryColor,
                            AppTheme.successColor
                          ],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: AppTheme.primaryColor.withOpacity(0.18),
                            blurRadius: 16,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: const Icon(Icons.favorite,
                          color: Colors.white, size: 38),
                    ),
                    const SizedBox(height: 18),
                      Text(
                        'Create Account',
                      style:
                          Theme.of(context).textTheme.headlineSmall?.copyWith(
                                color: AppTheme.primaryColor,
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                    const SizedBox(height: 6),
                      Text(
                        'Join HeartGuard AI today',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppTheme.secondaryColor,
                          ),
                    ),
                    const SizedBox(height: 32),
                    Form(
                            key: _formKey,
                            child: Column(
                              children: [
                                TextFormField(
                                  controller: _usernameController,
                            decoration: InputDecoration(
                                    labelText: 'Username',
                              prefixIcon: const Icon(Icons.person_outline),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                              filled: true,
                              fillColor: Colors.grey[100],
                            ),
                            validator: (value) => value == null || value.isEmpty
                                ? 'Please enter your username'
                                : null,
                          ),
                          const SizedBox(height: 18),
                                TextFormField(
                                  controller: _emailController,
                                  keyboardType: TextInputType.emailAddress,
                            decoration: InputDecoration(
                                    labelText: 'Email',
                              prefixIcon: const Icon(Icons.email_outlined),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                              filled: true,
                              fillColor: Colors.grey[100],
                            ),
                            validator: (value) => value == null || value.isEmpty
                                ? 'Please enter your email'
                                : null,
                          ),
                          const SizedBox(height: 18),
                                TextFormField(
                                  controller: _passwordController,
                                  obscureText: _obscurePassword,
                                  decoration: InputDecoration(
                                    labelText: 'Password',
                              prefixIcon: const Icon(Icons.lock_outline),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                              filled: true,
                              fillColor: Colors.grey[100],
                                    suffixIcon: IconButton(
                                icon: Icon(_obscurePassword
                                            ? Icons.visibility
                                    : Icons.visibility_off),
                                      onPressed: () {
                                        setState(() {
                                          _obscurePassword = !_obscurePassword;
                                        });
                                      },
                                    ),
                            ),
                            validator: (value) => value == null || value.isEmpty
                                ? 'Please enter your password'
                                : null,
                          ),
                          const SizedBox(height: 18),
                                TextFormField(
                                  controller: _confirmPasswordController,
                                  obscureText: _obscureConfirmPassword,
                                  decoration: InputDecoration(
                                    labelText: 'Confirm Password',
                              prefixIcon: const Icon(Icons.lock_outline),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                              filled: true,
                              fillColor: Colors.grey[100],
                                    suffixIcon: IconButton(
                                icon: Icon(_obscureConfirmPassword
                                            ? Icons.visibility
                                    : Icons.visibility_off),
                                      onPressed: () {
                                        setState(() {
                                          _obscureConfirmPassword =
                                              !_obscureConfirmPassword;
                                        });
                                      },
                                    ),
                            ),
                            validator: (value) => value == null || value.isEmpty
                                ? 'Please confirm your password'
                                : null,
                          ),
                          const SizedBox(height: 28),
                          SizedBox(
                            width: double.infinity,
                            height: 52,
                            child: ElevatedButton(
                                  onPressed: _isLoading ? null : _signup,
                                  style: ElevatedButton.styleFrom(
                                backgroundColor: AppTheme.primaryColor,
                                    shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                    ),
                                elevation: 4,
                                  ),
                                  child: _isLoading
                                  ? const CircularProgressIndicator(
                                      color: Colors.white)
                                      : const Text(
                                          'Create Account',
                                          style: TextStyle(
                                          fontSize: 18,
                                          fontWeight: FontWeight.bold),
                                          ),
                                        ),
                                ),
                          const SizedBox(height: 18),
                          SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Text(
                                      "Already have an account? ",
                                  style: Theme.of(context).textTheme.bodyMedium,
                                    ),
                                    TextButton(
                                      onPressed: () {
                                        AppRouter.navigateToAndClear(
                                            AppRouter.login);
                                      },
                                      child: const Text('Sign In'),
                                ),
                              ],
                            ),
                          ),
                        ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
        ],
          ),
    );
  }
}
