// lib/screens/profile_screen.dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../models/user.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  static const Color cetsyGreen = Color(0xFF198754);

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Profile'),
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFEFF7F3), Color(0xFFEAF5F0)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: auth.isAuthenticated && user != null
            ? _AuthenticatedView(user: user, onLogout: auth.logout)
            : _GuestView(),
      ),
    );
  }
}

class _AuthenticatedView extends StatelessWidget {
  final User user;
  final Future<void> Function() onLogout;
  const _AuthenticatedView({required this.user, required this.onLogout});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header with user info
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            boxShadow: const [
              BoxShadow(
                blurRadius: 24,
                color: Color(0x14000000),
                offset: Offset(0, 12),
              ),
            ],
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              CircleAvatar(
                radius: 30,
                backgroundColor: ProfileScreen.cetsyGreen.withOpacity(.12),
                child: const Icon(Icons.person, size: 34, color: ProfileScreen.cetsyGreen),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(user.name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                    const SizedBox(height: 4),
                    Text(user.email, style: const TextStyle(color: Colors.black54)),
                    if (user.phone != null && user.phone!.isNotEmpty)
                      Text(user.phone!, style: const TextStyle(color: Colors.black54)),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        Card(
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.verified_user_outlined),
                title: const Text('Account Type'),
                subtitle: Text(user.userType),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.password_outlined),
                title: const Text('Change Password'),
                onTap: () => Navigator.pushNamed(context, '/change-password'),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.alternate_email_outlined),
                title: const Text('Change Email'),
                onTap: () => Navigator.pushNamed(context, '/change-email'),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.check_circle_outline),
                title: const Text('Status'),
                subtitle: Text(user.isActive ? 'Active' : 'Inactive'),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.badge_outlined),
                title: const Text('User ID'),
                subtitle: Text('#${user.id}'),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        Card(
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.edit_outlined),
                title: const Text('Edit Profile'),
                onTap: () => Navigator.pushNamed(context, '/edit-profile'),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.receipt_long_outlined),
                title: const Text('My Orders'),
                subtitle: const Text('View your recent purchases'),
                onTap: () => Navigator.pushNamed(context, '/orders'),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.logout),
                title: const Text('Logout'),
                onTap: () async {
                  await onLogout();
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Logged out')),
                    );
                  }
                },
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _GuestView extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            boxShadow: const [
              BoxShadow(
                blurRadius: 24,
                color: Color(0x14000000),
                offset: Offset(0, 12),
              ),
            ],
          ),
          child: Row(
            children: [
              CircleAvatar(
                radius: 28,
                backgroundColor: ProfileScreen.cetsyGreen.withOpacity(.12),
                child: const Icon(Icons.person, size: 32, color: ProfileScreen.cetsyGreen),
              ),
              const SizedBox(width: 14),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Welcome to Cetsy', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
                    SizedBox(height: 4),
                    Text('Sign in to sync your cart, orders, and more.', style: TextStyle(color: Colors.black54)),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        Card(
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.login),
                title: const Text('Login / Register'),
                subtitle: const Text('Access your account and orders'),
                onTap: () => Navigator.pushNamed(context, '/register'),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.lock_reset),
                title: const Text('Forgot Password'),
                subtitle: const Text('Reset your account password'),
                onTap: () => Navigator.pushNamed(context, '/forgot-password'),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.privacy_tip_outlined),
                title: const Text('Privacy & Terms'),
                onTap: () {},
              ),
            ],
          ),
        ),
      ],
    );
  }
}
