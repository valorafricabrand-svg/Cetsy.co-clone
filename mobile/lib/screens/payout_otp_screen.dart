import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../services/wallet_service.dart';

class PayoutOtpScreen extends StatefulWidget {
  const PayoutOtpScreen({super.key, required this.payoutId});

  final int payoutId;

  static const String route = '/payout-verify';

  @override
  State<PayoutOtpScreen> createState() => _PayoutOtpScreenState();
}

class _PayoutOtpScreenState extends State<PayoutOtpScreen> {
  final _code = TextEditingController();
  bool _verifying = false;
  bool _resending = false;
  bool _cancelling = false;

  @override
  void dispose() {
    _code.dispose();
    super.dispose();
  }

  Future<void> _verify() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please login first')));
      return;
    }
    if (_code.text.trim().isEmpty) return;
    setState(() => _verifying = true);
    try {
      await WalletService.verifyPayoutOtp(token: token, payoutId: widget.payoutId, code: _code.text.trim());
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Verification successful. Payout submitted.')));
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _verifying = false);
    }
  }

  Future<void> _resend() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    setState(() => _resending = true);
    try {
      await WalletService.resendPayoutOtp(token: token, payoutId: widget.payoutId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('A new code has been sent.')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _resending = false);
    }
  }

  Future<void> _cancel() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Cancel payout request?'),
        content: const Text('This payout request will be cancelled.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('No')),
          ElevatedButton(onPressed: () => Navigator.pop(context, true), child: const Text('Yes, cancel')),
        ],
      ),
    );
    if (ok != true) return;
    setState(() => _cancelling = true);
    try {
      await WalletService.cancelPayout(token: token, payoutId: widget.payoutId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Payout request cancelled.')));
      Navigator.pop(context, false);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _cancelling = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Verify Payout')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text('We sent a 6-digit verification code to your email.'),
            const SizedBox(height: 12),
            TextField(
              controller: _code,
              decoration: const InputDecoration(labelText: 'Verification code'),
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _verifying ? null : _verify,
              child: _verifying
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Verify & Submit'),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                TextButton(
                  onPressed: _resending ? null : _resend,
                  child: _resending ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(strokeWidth: 2)) : const Text('Resend code'),
                ),
                TextButton(
                  onPressed: _cancelling ? null : _cancel,
                  child: _cancelling ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(strokeWidth: 2)) : const Text('Cancel request'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

