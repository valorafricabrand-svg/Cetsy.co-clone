// ignore_for_file: use_build_context_synchronously
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../providers/currency_provider.dart';
import '../providers/auth_provider.dart';
import '../services/user_service.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  final _currencies = const ['USD','EUR','GBP','KES','NGN','JPY','INR','AUD','CAD'];
  String? _selected;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final code = context.read<CurrencyProvider>().code;
    _selected = code;
  }

  Future<void> _save() async {
    if (_selected == null) return;
    setState(() => _saving = true);
    try {
      final auth = context.read<AuthProvider>();
      final token = auth.token;
      await context.read<CurrencyProvider>().setCode(
        _selected!,
        token: token,
        updateServer: token != null,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Currency updated')),
      );
      Navigator.pop(context);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  // (unused helper removed)

  Future<void> _contactSupport() async {
    final uri = Uri.parse('mailto:support@cetsy.co?subject=Seller%20Upgrade%20Assistance');
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Could not open mail app. Email support@cetsy.co')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final code = context.watch<CurrencyProvider>().code;
    return Scaffold(
      appBar: AppBar(title: const Text('Settings')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Currency', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  DropdownButtonFormField<String>(
                    initialValue: _selected ?? code,
                    items: _currencies
                        .map((c) => DropdownMenuItem(value: c, child: Text(c)))
                        .toList(),
                    onChanged: (v) => setState(() => _selected = v),
                    decoration: const InputDecoration(labelText: 'Select currency'),
                  ),
                  const SizedBox(height: 12),
                  ElevatedButton.icon(
                    onPressed: _saving ? null : _save,
                    icon: _saving
                        ? const SizedBox(
                            width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.save),
                    label: const Text('Save'),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Seller Mode', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  Text('Upgrade your account to start selling on Cetsy.',
                      style: TextStyle(color: Colors.black.withValues(alpha: .7))),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: _saving ? null : _contactSupport,
                          icon: const Icon(Icons.support_agent),
                          label: const Text('Contact Support'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () async {
                            final auth = context.read<AuthProvider>();
                            final token = auth.token;
                            if (token == null) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Please login first')),
                              );
                              return;
                            }
                            setState(() => _saving = true);
                            final nav = Navigator.of(context);
                            final messenger = ScaffoldMessenger.of(context);
                            final authProv = context.read<AuthProvider>();
                            try {
                              final upgraded = await UserService.upgradeToSeller(token);
                              if (!mounted) return;
                              await authProv.login(token, upgraded);
                              if (!mounted) return;
                              messenger.showSnackBar(
                                const SnackBar(content: Text('Seller mode enabled')),
                              );
                              if (!mounted) return;
                              await nav.pushNamed('/add-listing');
                            } catch (e) {
                              if (!mounted) return;
                              showDialog(
                                context: context,
                                builder: (_) => AlertDialog(
                                  title: const Text('Upgrade failed'),
                                  content: Text('We could not enable seller mode. Error: $e'),
                                  actions: [
                                    TextButton(onPressed: () => Navigator.pop(context), child: const Text('Close')),
                                    TextButton(onPressed: () { Navigator.pop(context); _contactSupport(); }, child: const Text('Contact Support')),
                                  ],
                                ),
                              );
                            } finally { if (mounted) setState(() => _saving = false); }
                          },
                          icon: _saving ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.storefront),
                          label: const Text('Upgrade to Seller'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
