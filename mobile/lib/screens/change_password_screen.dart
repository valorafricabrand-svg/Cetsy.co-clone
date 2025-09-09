import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../services/auth_service.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _current = TextEditingController();
  final _next = TextEditingController();
  final _confirm = TextEditingController();
  bool _saving = false;
  bool _ob1 = true, _ob2 = true, _ob3 = true;

  @override
  void dispose() {
    _current.dispose();
    _next.dispose();
    _confirm.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please login first')));
      return;
    }
    setState(() => _saving = true);
    try {
      await AuthService.changePassword(
        token: token,
        currentPassword: _current.text,
        newPassword: _next.text,
        confirmPassword: _confirm.text,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password changed successfully')));
      Navigator.pop(context);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  InputDecoration _dec(String label, {bool toggle = false, void Function()? onToggle}) =>
      InputDecoration(
        labelText: label,
        suffixIcon: toggle ? IconButton(onPressed: onToggle, icon: const Icon(Icons.remove_red_eye_outlined)) : null,
      );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Change Password')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(
                controller: _current,
                obscureText: _ob1,
                decoration: _dec('Current password', toggle: true, onToggle: () => setState(() => _ob1 = !_ob1)),
                validator: (v) => (v == null || v.isEmpty) ? 'Enter current password' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _next,
                obscureText: _ob2,
                decoration: _dec('New password', toggle: true, onToggle: () => setState(() => _ob2 = !_ob2)),
                validator: (v) => (v == null || v.length < 6) ? 'At least 6 characters' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _confirm,
                obscureText: _ob3,
                decoration: _dec('Confirm new password', toggle: true, onToggle: () => setState(() => _ob3 = !_ob3)),
                validator: (v) => (v != _next.text) ? 'Passwords do not match' : null,
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _saving ? null : _submit,
                  child: _saving
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Text('Update Password'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

