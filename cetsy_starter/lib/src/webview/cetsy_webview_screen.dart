// lib/src/webview/cetsy_webview_screen.dart
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

class CetsyWebViewScreen extends StatefulWidget {
  const CetsyWebViewScreen({super.key, required this.initialUrl});
  final String initialUrl;

  @override
  State<CetsyWebViewScreen> createState() => _CetsyWebViewScreenState();
}

class _CetsyWebViewScreenState extends State<CetsyWebViewScreen> {
  WebViewController? _controller; // nullable so we can skip on web
  final ValueNotifier<int> _progress = ValueNotifier<int>(0);
  final ValueNotifier<String> _currentUrl = ValueNotifier<String>('');

  @override
  void initState() {
    super.initState();

    // ✅ On Flutter Web: DO NOT create a WebView controller (avoids UnimplementedError)
    if (kIsWeb) return;

    final params = const PlatformWebViewControllerCreationParams();

    _controller = WebViewController.fromPlatformCreationParams(params)
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.transparent)
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (p) => _progress.value = p,
          onPageFinished: (url) => _currentUrl.value = url,
          onUrlChange: (change) {
            if (change.url != null) _currentUrl.value = change.url!;
          },
          onNavigationRequest: (req) {
            final url = Uri.parse(req.url);
            final isHttp = url.scheme == 'http' || url.scheme == 'https';
            final isAppScheme = {
              'tel',
              'mailto',
              'sms',
              'whatsapp',
              'intent',
              'maps',
              'geo',
            }.contains(url.scheme);

            if (isAppScheme) {
              launchUrl(url, mode: LaunchMode.externalApplication);
              return NavigationDecision.prevent;
            }
            return isHttp ? NavigationDecision.navigate : NavigationDecision.prevent;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.initialUrl));
  }

  Future<void> _reload() async => _controller?.reload();
  Future<void> _goBack() async {
    if (_controller != null && await _controller!.canGoBack()) {
      await _controller!.goBack();
    }
  }

  Future<void> _goForward() async {
    if (_controller != null && await _controller!.canGoForward()) {
      await _controller!.goForward();
    }
  }

  Future<void> _openExternally() async {
    final current = _controller == null ? null : await _controller!.currentUrl();
    final url = current ?? widget.initialUrl;
    await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    // ✅ Web fallback: redirect this tab to https://cetsy.co (no iframe/webview)
    if (kIsWeb) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        launchUrl(Uri.parse(widget.initialUrl), webOnlyWindowName: '_self');
      });
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    // Mobile (Android/iOS) WebView UI
    return PopScope(
      canPop: false,
      onPopInvoked: (didPop) async {
        if (didPop) return;
        if (_controller != null && await _controller!.canGoBack()) {
          await _controller!.goBack();
        } else {
          try {
            await SystemNavigator.pop();
          } catch (_) {}
        }
      },
      child: Scaffold(
        appBar: AppBar(
          titleSpacing: 0,
          title: ValueListenableBuilder<String>(
            valueListenable: _currentUrl,
            builder: (_, url, __) {
              final showHost = Uri.tryParse(url)?.host ?? 'cetsy.co';
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Cetsy'),
                  Text(showHost, style: Theme.of(context).textTheme.bodySmall),
                ],
              );
            },
          ),
          actions: [
            IconButton(
              tooltip: 'Back',
              onPressed: _goBack,
              icon: const Icon(Icons.arrow_back),
            ),
            IconButton(
              tooltip: 'Forward',
              onPressed: _goForward,
              icon: const Icon(Icons.arrow_forward),
            ),
            IconButton(
              tooltip: 'Refresh',
              onPressed: _reload,
              icon: const Icon(Icons.refresh),
            ),
            IconButton(
              tooltip: 'Open in Browser',
              onPressed: _openExternally,
              icon: const Icon(Icons.open_in_new),
            ),
          ],
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(2),
            child: ValueListenableBuilder<int>(
              valueListenable: _progress,
              builder: (_, p, __) => AnimatedOpacity(
                duration: const Duration(milliseconds: 150),
                opacity: (p > 0 && p < 100) ? 1 : 0,
                child: LinearProgressIndicator(value: p == 100 ? null : p / 100),
              ),
            ),
          ),
        ),
        body: SafeArea(
          child: _controller == null
              ? const SizedBox.shrink()
              : WebViewWidget(controller: _controller!),
        ),
      ),
    );
  }
}
