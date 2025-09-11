import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../providers/auth_provider.dart';
import '../services/wallet_service.dart';

class PaypalCheckoutScreen extends StatefulWidget {
  final double amountUsd;
  const PaypalCheckoutScreen({super.key, required this.amountUsd});

  @override
  State<PaypalCheckoutScreen> createState() => _PaypalCheckoutScreenState();
}

class _PaypalCheckoutScreenState extends State<PaypalCheckoutScreen> {
  late final WebViewController _controller;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..addJavaScriptChannel(
        'PayPalChannel',
        onMessageReceived: (msg) async {
          // Expect a JSON string with { status: 'approved', value: '10.00' }
          try {
            final data = jsonDecode(msg.message);
            final status = data['status'];
            final value = double.tryParse('${data['value']}') ?? widget.amountUsd;
            if (status == 'approved') {
              final token = context.read<AuthProvider>().token;
              if (token != null) {
                await WalletService.paypalDeposit(token: token, amount: value);
              }
              if (!mounted) return;
              Navigator.pop(context, true);
            } else {
              if (!mounted) return;
              Navigator.pop(context, false);
            }
          } catch (_) {
            if (!mounted) return;
            Navigator.pop(context, false);
          }
        },
      )
      ..setNavigationDelegate(NavigationDelegate(onPageFinished: (_) {
        if (mounted) setState(() => _loading = false);
      }));

    _load();
  }

  Future<void> _load() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    try {
      final cfg = await WalletService.summary(token); // fallback if config endpoint fails
      String clientId = '';
      try {
        final urlCfg = await _getClientId(token);
        clientId = urlCfg;
      } catch (_) {
        clientId = (cfg['paypal_client_id'] ?? '') as String; // in case you expose it in summary
      }
    
      final html = _buildHtml(clientId, widget.amountUsd);
      await _controller.loadHtmlString(html);
    } catch (_) {}
  }

  Future<String> _getClientId(String token) async {
    // use wallet paypal config endpoint
    final cfg = await WalletService.getPaypalConfig(token);
    return (cfg['client_id'] ?? '') as String;
  }

  String _buildHtml(String clientId, double amount) {
    final amt = amount.toStringAsFixed(2);
    return '''
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://www.paypal.com/sdk/js?client-id=$clientId&currency=USD"></script>
    <style> body { font-family: sans-serif; padding: 16px; } </style>
  </head>
  <body>
    <div id="paypal-button-container"></div>
    <script>
      paypal.Buttons({
        createOrder: function(data, actions) {
          return actions.order.create({
            purchase_units: [{ amount: { value: '$amt' } }]
          });
        },
        onApprove: function(data, actions) {
          return actions.order.capture().then(function(details) {
            try {
              const value = details.purchase_units[0].amount.value;
              PayPalChannel.postMessage(JSON.stringify({ status: 'approved', value: value }));
            } catch (e) {
              PayPalChannel.postMessage(JSON.stringify({ status: 'approved', value: '$amt' }));
            }
          });
        },
        onCancel: function (data) {
          PayPalChannel.postMessage(JSON.stringify({ status: 'cancelled' }));
        },
        onError: function (err) {
          PayPalChannel.postMessage(JSON.stringify({ status: 'error' }));
        }
      }).render('#paypal-button-container');
    </script>
  </body>
  </html>
''';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('PayPal Checkout')),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_loading) const LinearProgressIndicator(),
        ],
      ),
    );
  }
}


