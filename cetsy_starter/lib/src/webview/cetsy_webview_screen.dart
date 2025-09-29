// WebView screen with 4-item bottom navigation.
// Keeps: no AppBar, Safe top margin, pull-to-refresh, tap/transition loader,
// offline banner, Android file uploads, and web redirect to https://cetsy.co.

import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:share_plus/share_plus.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';

class CetsyWebViewScreen extends StatefulWidget {
  const CetsyWebViewScreen({super.key, required this.initialUrl});
  final String initialUrl;

  @override
  State<CetsyWebViewScreen> createState() => _CetsyWebViewScreenState();
}

// Bottom navigation removed: rely on website's own navigation.

class _CetsyWebViewScreenState extends State<CetsyWebViewScreen> {
  WebViewController? _controller; // null on web (we redirect instead)

  // ---- Layout tuning
  static const double _topMargin = 0;

  // ---- Load/progress state
  final ValueNotifier<int> _pageProgress = ValueNotifier<int>(0);
  bool _loadingOverlay = false; // tap/transition spinner

  // ---- Offline + errors
  final ValueNotifier<bool> _isOffline = ValueNotifier<bool>(false);
  final ValueNotifier<String?> _lastError = ValueNotifier<String?>(null);
  late final StreamSubscription<dynamic> _connSub; // supports Result or List<Result>

  // ---- Pull-to-refresh (top-edge handle)
  double _dragDistance = 0.0;
  double _pullProgress = 0.0; // 0..1 visual bar
  static const double _pullTrigger = 100; // px to trigger reload

  // ---- Base URL scope and share anchor
  late final Uri _baseUri;
  final GlobalKey _shareAnchorKey = GlobalKey();

  @override
  void initState() {
    super.initState();

    // Derive the origin (scheme + host + port) from the initial URL.
    // All in-app navigation stays within this origin; outside links open externally.
    final parsedInit = Uri.parse(widget.initialUrl);
    _baseUri = parsedInit.replace(path: '/', query: null, fragment: null);

    // Connectivity banner (compatible with old/new connectivity_plus)
    _connSub = Connectivity().onConnectivityChanged.listen((event) {
      bool offline = false;
      if (event is ConnectivityResult) {
        offline = event == ConnectivityResult.none;
      } else if (event is List<ConnectivityResult>) {
        offline = event.contains(ConnectivityResult.none);
      }
      _isOffline.value = offline;
    });

    // ✅ On Flutter Web: DO NOT instantiate a WebView. Redirect the tab instead.
    if (kIsWeb) return;

    // Generic creation params (portable across plugin versions)
    const params = PlatformWebViewControllerCreationParams();

    final controller = WebViewController.fromPlatformCreationParams(params)
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.transparent)
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (p) {
            _pageProgress.value = p;
            if (p > 0 && p < 100) {
              _setLoading(true);
            } else if (p == 100) {
              _setLoading(false);
            }
          },
          onPageStarted: (_) {
            _lastError.value = null;
            _setLoading(true);
          },
          onPageFinished: (url) {
            _setLoading(false);
          },
          onWebResourceError: (err) {
            _lastError.value = '${err.errorCode}: ${err.description}';
            _setLoading(false);
          },
          onNavigationRequest: (req) {
            final uri = Uri.parse(req.url);
            final isHttp = uri.scheme == 'http' || uri.scheme == 'https';
            final isAppScheme = <String>{
              'tel', 'mailto', 'sms', 'whatsapp', 'intent', 'maps', 'geo',
            }.contains(uri.scheme);

            if (isAppScheme) {
              launchUrl(uri, mode: LaunchMode.externalApplication);
              return NavigationDecision.prevent;
            }

            // Keep navigation inside the app's origin; open other domains externally
            if (isHttp) {
              if (uri.host == _baseUri.host) {
                return NavigationDecision.navigate;
              } else {
                launchUrl(uri, mode: LaunchMode.externalApplication);
                return NavigationDecision.prevent;
              }
            }

            return NavigationDecision.prevent;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.initialUrl));

    // Android extras: debugging, basic file uploads (no allowMultiple for older APIs)
    if (controller.platform is AndroidWebViewController) {
      AndroidWebViewController.enableDebugging(true);
      final android = controller.platform as AndroidWebViewController;

      android.setOnShowFileSelector((params) async {
        final result = await FilePicker.platform.pickFiles(type: FileType.any);
        if (result == null) return <String>[];
        return result.paths.whereType<String>().toList();
      });

      // NOTE: Some versions don't support setDownloadListener; omitted intentionally.
    }

    _controller = controller;
  }

  @override
  void dispose() {
    _connSub.cancel();
    _pageProgress.dispose();
    _isOffline.dispose();
    _lastError.dispose();
    super.dispose();
  }

  // ---- Utilities

  void _setLoading(bool v) {
    if (_loadingOverlay == v) return;
    setState(() => _loadingOverlay = v);
  }

  Future<void> _reload() async {
    if (_controller == null) return;
    HapticFeedback.lightImpact();
    _setLoading(true);
    await _controller!.reload();
  }

  Future<void> _shareCurrentPage() async {
    try {
      String? url = widget.initialUrl;
      if (_controller != null) {
        url = await _controller!.currentUrl();
      }
      url ??= widget.initialUrl;

      final box = _shareAnchorKey.currentContext?.findRenderObject() as RenderBox?;
      final origin = box != null ? box.localToGlobal(Offset.zero) & box.size : null;
      await Share.share(
        url,
        subject: 'Check this out on Cetsy',
        sharePositionOrigin: origin,
      );
    } catch (_) {}
  }

  Future<void> _goBackOrExit() async {
    if (_controller != null && await _controller!.canGoBack()) {
      await _controller!.goBack();
    } else {
      try {
        await SystemNavigator.pop();
      } catch (_) {}
    }
  }

  // ---- Pull-to-refresh handle at the very top of the web content
  Widget _buildPullHandle() {
    return Positioned(
      top: 0,
      left: 0,
      right: 0,
      height: 24,
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onVerticalDragStart: (_) {
          _dragDistance = 0;
          setState(() => _pullProgress = 0);
        },
        onVerticalDragUpdate: (details) {
          final dy = details.delta.dy;
          if (dy > 0) {
            _dragDistance += dy;
            final p = (_dragDistance / _pullTrigger).clamp(0.0, 1.0);
            setState(() => _pullProgress = p);
          }
        },
        onVerticalDragEnd: (_) async {
          final shouldRefresh = _dragDistance >= _pullTrigger;
          _dragDistance = 0;
          setState(() => _pullProgress = 0);
          if (shouldRefresh) {
            await _reload();
          }
        },
      ),
    );
  }

  // Thin progress strip at the top (uses either pull-progress or page-load progress)
  Widget _buildTopProgressStrip() {
    return Positioned(
      top: 0,
      left: 0,
      right: 0,
      height: 2,
      child: ValueListenableBuilder<int>(
        valueListenable: _pageProgress,
        builder: (_, p, __) {
          final isLoading = p > 0 && p < 100;
          final showPull = _pullProgress > 0;
          final value = showPull ? _pullProgress : (isLoading ? p / 100 : null);
          return AnimatedOpacity(
            duration: const Duration(milliseconds: 120),
            opacity: (showPull || isLoading) ? 1 : 0,
            child: LinearProgressIndicator(value: value),
          );
        },
      ),
    );
  }

  // Offline banner (under the progress strip)
  Widget _buildOfflineBanner() {
    return ValueListenableBuilder<bool>(
      valueListenable: _isOffline,
      builder: (_, offline, __) {
        if (!offline) return const SizedBox.shrink();
        return Container(
          width: double.infinity,
          color: Colors.amber,
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          child: Row(
            children: [
              const Icon(Icons.wifi_off, size: 18),
              const SizedBox(width: 8),
              const Expanded(child: Text('You are offline. Pull down to retry.')),
              TextButton(onPressed: _reload, child: const Text('Retry')),
            ],
          ),
        );
      },
    );
  }

  // Error overlay with retry
  Widget _buildErrorOverlay() {
    return ValueListenableBuilder<String?>(
      valueListenable: _lastError,
      builder: (_, err, __) {
        if (err == null) return const SizedBox.shrink();
        return Container(
          color: Theme.of(context).colorScheme.surface.withOpacity(.98),
          alignment: Alignment.center,
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.cloud_off, size: 42),
                const SizedBox(height: 12),
                Text('Something went wrong', style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                Text(err, textAlign: TextAlign.center, style: Theme.of(context).textTheme.bodySmall),
                const SizedBox(height: 16),
                FilledButton.icon(onPressed: _reload, icon: const Icon(Icons.refresh), label: const Text('Retry')),
              ],
            ),
          ),
        );
      },
    );
  }

  // Tap listener overlay: shows a quick loader flash and lets touch pass through.
  Widget _buildTapLoaderListener() {
    return Listener(
      behavior: HitTestBehavior.translucent, // does NOT block the webview
      onPointerDown: (_) {
        // flash the loader briefly on any tap; if a real navigation starts,
        // onProgress/onPageStarted will keep it visible until finished.
        _setLoading(true);
        Future.delayed(const Duration(milliseconds: 300), () {
          if ((_pageProgress.value == 0 || _pageProgress.value == 100) && mounted) {
            _setLoading(false);
          }
        });
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    // ✅ Web fallback: redirect this tab to cetsy.co (no iframe/webview)
    if (kIsWeb) {
      final uri = Uri.parse(widget.initialUrl);
      if (kReleaseMode) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          launchUrl(uri, webOnlyWindowName: '_self');
        });
        return const Scaffold(body: Center(child: CircularProgressIndicator()));
      } else {
        // In debug/profile, don't replace the inspected tab to avoid DWDS detaching.
        return Scaffold(
          body: Center(
            child: FilledButton(
              onPressed: () => launchUrl(uri, webOnlyWindowName: '_blank'),
              child: const Text('Open Cetsy'),
            ),
          ),
        );
      }
    }

    return PopScope(
      canPop: false,
      onPopInvoked: (didPop) async {
        if (didPop) return;
        await _goBackOrExit();
      },
      child: Scaffold(
        extendBody: true,
        body: SafeArea(
          top: true,
          bottom: true, // make room for bottom nav safely
          child: Column(
            children: [
              // Optional spacer for a "good top margin" above your content
              const SizedBox(height: _topMargin),

              // Offline banner (appears under the margin)
              _buildOfflineBanner(),

              // Main area
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.only(top: _topMargin),
                  child: Stack(
                    children: [
                      if (_controller != null) WebViewWidget(controller: _controller!),

                      // Pull-to-refresh handle + top progress strip
                      _buildPullHandle(),
                      _buildTopProgressStrip(),

                      // Tap loader listener (doesn't block touches)
                      _buildTapLoaderListener(),

                      // Error overlay (if any)
                      _buildErrorOverlay(),

                      // Center loader overlay
                      IgnorePointer(
                        ignoring: true, // visual only
                        child: AnimatedOpacity(
                          duration: const Duration(milliseconds: 150),
                          opacity: _loadingOverlay ? 1 : 0,
                          child: Container(
                            color: Colors.black.withOpacity(0.12),
                            alignment: Alignment.center,
                            child: const SizedBox(
                              width: 36,
                              height: 36,
                              child: CircularProgressIndicator(strokeWidth: 3),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),

        // Share current page
        floatingActionButton: FloatingActionButton.small(
          key: _shareAnchorKey,
          onPressed: _shareCurrentPage,
          tooltip: 'Share',
          heroTag: 'share_fab',
          child: const Icon(Icons.share),
        ),
      ),
    );
  }
}
