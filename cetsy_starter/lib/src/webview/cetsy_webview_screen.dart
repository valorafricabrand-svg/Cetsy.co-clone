// WebView screen with 4-item bottom navigation.
// Keeps: no AppBar, Safe top margin, pull-to-refresh, tap/transition loader,
// offline banner and Android file uploads.

import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:webview_flutter_wkwebview/webview_flutter_wkwebview.dart';
import '../config.dart';
import '../web/web_iframe_hooks.dart';

class CetsyWebViewScreen extends StatefulWidget {
  const CetsyWebViewScreen({super.key, required this.initialUrl});
  final String initialUrl;

  @override
  State<CetsyWebViewScreen> createState() => _CetsyWebViewScreenState();
}

// Bottom navigation removed: rely on website's own navigation.

class _CetsyWebViewScreenState extends State<CetsyWebViewScreen> {
  WebViewController? _controller;

  // ---- Layout tuning
  static const double _topMargin = 0;

  // ---- Load/progress state
  final ValueNotifier<int> _pageProgress = ValueNotifier<int>(0);
  bool _loadingOverlay = false; // tap/transition spinner

  // ---- Offline + errors
  final ValueNotifier<bool> _isOffline = ValueNotifier<bool>(false);
  final ValueNotifier<String?> _lastError = ValueNotifier<String?>(null);
  late final StreamSubscription<List<ConnectivityResult>> _connSub;
  bool _showInstallHint = false;

  // ---- Pull-to-refresh (top-edge handle)
  double _dragDistance = 0.0;
  double _pullProgress = 0.0; // 0..1 visual bar
  static const double _pullTrigger = 100; // px to trigger reload

  // ---- Base URL scope
  late final Uri _baseUri;

  @override
  void initState() {
    super.initState();

    // Derive the origin (scheme + host + port) from the initial URL.
    // All in-app navigation stays within this origin; outside links open externally.
    final parsedInit = Uri.parse(widget.initialUrl);
    _baseUri = parsedInit.replace(path: '/', query: null, fragment: null);

    _showInstallHint = kIsWeb;

    // Connectivity banner
    _connSub = Connectivity().onConnectivityChanged.listen((results) {
      _isOffline.value = results.contains(ConnectivityResult.none);
    });
    Connectivity().checkConnectivity().then((results) {
      _isOffline.value = results.contains(ConnectivityResult.none);
    }).catchError((_) {});

    // Creation params with iOS inline-media enabled
    PlatformWebViewControllerCreationParams params;
    if (WebViewPlatform.instance is WebKitWebViewPlatform) {
      params = WebKitWebViewControllerCreationParams(
        allowsInlineMediaPlayback: true,
        mediaTypesRequiringUserAction: const <PlaybackMediaTypes>{},
      );
    } else {
      params = const PlatformWebViewControllerCreationParams();
    }

    final controller = WebViewController.fromPlatformCreationParams(params);

    if (kIsWeb) {
      _setLoading(true);
      WebIframeHooks.start(
        onNavStart: () {
          if (!mounted) return;
          _setLoading(true);
        },
        onNavDone: () {
          if (!mounted) return;
          _setLoading(false);
        },
      );
      controller.loadRequest(Uri.parse(widget.initialUrl)).catchError((_) {});
      Future.delayed(const Duration(seconds: 2), () {
        if (mounted && _loadingOverlay) _setLoading(false);
      });
      _controller = controller;
      return;
    }

    controller
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
          onPageFinished: (url) async {
            _setLoading(false);
            await _injectPagePatches();
          },
          onWebResourceError: (err) {
            // Only treat top-level navigation failures as fatal.
            // Subresource failures (ads/CDN pixels/etc.) should not block UI.
            if (err.isForMainFrame != true) return;
            _lastError.value = '${err.errorCode}: ${err.description}';
            _setLoading(false);
          },
          onNavigationRequest: (req) {
            final uri = Uri.parse(req.url);
            final isHttp = uri.scheme == 'http' || uri.scheme == 'https';
            final isAppScheme = <String>{
              'tel',
              'mailto',
              'sms',
              'whatsapp',
              'intent',
              'maps',
              'geo',
              'tg',
              'instagram',
              'twitter',
              'facebook',
              'fb',
              'viber',
              'skype',
            }.contains(uri.scheme);

            if (isAppScheme) {
              launchUrl(uri, mode: LaunchMode.externalApplication);
              return NavigationDecision.prevent;
            }

            // Keep navigation inside the app's origin; open other domains externally
            if (isHttp) {
              // Common downloadable types -> hand off to OS/browser for a native feel
              final p = (uri.path).toLowerCase();
              const dlExts = [
                '.pdf',
                '.doc',
                '.docx',
                '.xls',
                '.xlsx',
                '.ppt',
                '.pptx',
                '.zip',
                '.rar',
                '.7z',
                '.csv',
                '.apk',
                '.aac',
                '.mp3',
                '.m4a',
                '.mp4',
                '.mov',
                '.avi',
                '.webm',
                '.jpg',
                '.jpeg',
                '.png',
                '.gif',
                '.webp'
              ];
              final isDownload = dlExts.any((ext) => p.endsWith(ext));
              if (isDownload) {
                launchUrl(uri, mode: LaunchMode.externalApplication);
                return NavigationDecision.prevent;
              }
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

    // Android extras: debugging, smoother media playback
    if (controller.platform is AndroidWebViewController) {
      AndroidWebViewController.enableDebugging(true);
      final android = controller.platform as AndroidWebViewController;
      // Best-effort: allow inline media playback without user gesture
      android.setMediaPlaybackRequiresUserGesture(false).catchError((_) {});
      // Rely on platform file chooser for best compatibility (camera/gallery)
      // NOTE: Some versions don't support setDownloadListener; omitted intentionally.
    }

    // iOS: enable back/forward swipe gestures
    if (controller.platform is WebKitWebViewController) {
      final ios = controller.platform as WebKitWebViewController;
      // Best-effort: enable swipe back/forward gestures
      ios.setAllowsBackForwardNavigationGestures(true).catchError((_) {});
    }

    // Optional UA override via --dart-define=APP_UA
    if (AppConfig.userAgent.isNotEmpty) {
      controller.setUserAgent(AppConfig.userAgent).catchError((_) {});
    }

    _controller = controller;
  }

  @override
  void dispose() {
    WebIframeHooks.stop();
    _connSub.cancel();
    _pageProgress.dispose();
    _isOffline.dispose();
    _lastError.dispose();
    super.dispose();
  }

  // ---- Utilities

  // Inject small JS patches to improve in-app feel:
  // - Make window.open() and target=_blank open in the same view.
  // - Normalize forms with target=_blank to _self.
  Future<void> _injectPagePatches() async {
    if (_controller == null) return;
    try {
      const script = r"""
        (function(){
          try {
            // Open new windows in the same view
            window.open = function(u){ if(!u) return null; location.href = u; return null; };

            // Normalize anchors that try to break out
            document.addEventListener('click', function(e){
              var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
              if(!a) return;
              var href = a.getAttribute('href');
              if(!href) return;
              var url = a.href;
              var target = (a.getAttribute('target')||'').toLowerCase();
              var rel = (a.getAttribute('rel')||'').toLowerCase();
              if(target === '_blank' || rel.indexOf('noopener') >= 0 || rel.indexOf('noreferrer') >= 0){
                e.preventDefault();
                location.href = url;
              }
            }, true);

            // Normalize forms that try to open in a new window
            try {
              var forms = document.querySelectorAll('form[target="_blank"]');
              for (var i=0;i<forms.length;i++){ forms[i].setAttribute('target','_self'); }
            } catch(_){ }
          } catch(_){ }
        })();
      """;
      await _controller!.runJavaScript(script);
    } catch (_) {}
  }

  void _setLoading(bool v) {
    if (_loadingOverlay == v) return;
    setState(() => _loadingOverlay = v);
  }

  Future<void> _reload() async {
    if (_controller == null) return;
    HapticFeedback.lightImpact();
    _setLoading(true);
    if (kIsWeb) {
      await _controller!.loadRequest(Uri.parse(widget.initialUrl));
      _setLoading(false);
      return;
    }
    await _controller!.reload();
  }

  Future<void> _goBackOrExit() async {
    try {
      if (_controller != null && await _controller!.canGoBack()) {
        await _controller!.goBack();
      } else {
        await SystemNavigator.pop();
      }
    } catch (_) {}
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

  Widget _buildGlobalLoadingBar() {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 120),
      height: _loadingOverlay ? 2 : 0,
      child: _loadingOverlay
          ? const LinearProgressIndicator()
          : const SizedBox.shrink(),
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
              const Expanded(
                  child: Text('You are offline. Pull down to retry.')),
              TextButton(onPressed: _reload, child: const Text('Retry')),
            ],
          ),
        );
      },
    );
  }

  Widget _buildInstallHintBanner() {
    if (!kIsWeb || !_showInstallHint) return const SizedBox.shrink();
    return Container(
      width: double.infinity,
      color: Theme.of(context).colorScheme.primaryContainer,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(
        children: [
          const Icon(Icons.install_mobile, size: 18),
          const SizedBox(width: 8),
          const Expanded(
            child: Text(
              'Install Cetsy from your browser menu for a full-screen app experience.',
            ),
          ),
          TextButton(
            onPressed: () {
              showModalBottomSheet<void>(
                context: context,
                showDragHandle: true,
                builder: (ctx) => Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 20),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: const [
                      Text('Install Cetsy',
                          style: TextStyle(fontWeight: FontWeight.w600)),
                      SizedBox(height: 10),
                      Text(
                          'Chrome/Edge: menu (three dots) -> Install app / Add to Home screen.'),
                      SizedBox(height: 6),
                      Text('Safari on iPhone: Share -> Add to Home Screen.'),
                    ],
                  ),
                ),
              );
            },
            child: const Text('How'),
          ),
          IconButton(
            onPressed: () => setState(() => _showInstallHint = false),
            icon: const Icon(Icons.close),
            tooltip: 'Dismiss',
          ),
        ],
      ),
    );
  }

  // Error overlay with retry
  Widget _buildErrorOverlay() {
    return ValueListenableBuilder<String?>(
      valueListenable: _lastError,
      builder: (_, err, __) {
        if (err == null) return const SizedBox.shrink();
        return Container(
          color: Theme.of(context).colorScheme.surface.withValues(alpha: .98),
          alignment: Alignment.center,
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.cloud_off, size: 42),
                const SizedBox(height: 12),
                Text('Something went wrong',
                    style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                Text(err,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodySmall),
                const SizedBox(height: 16),
                FilledButton.icon(
                    onPressed: _reload,
                    icon: const Icon(Icons.refresh),
                    label: const Text('Retry')),
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
          if ((_pageProgress.value == 0 || _pageProgress.value == 100) &&
              mounted) {
            _setLoading(false);
          }
        });
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) async {
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
              _buildInstallHintBanner(),
              _buildGlobalLoadingBar(),

              // Main area
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.only(top: _topMargin),
                  child: Stack(
                    children: [
                      if (_controller != null)
                        WebViewWidget(controller: _controller!),

                      // Pull-to-refresh handle + top progress strip
                      _buildPullHandle(),
                      _buildTopProgressStrip(),

                      // Tap loader listener (doesn't block touches)
                      if (!kIsWeb) _buildTapLoaderListener(),

                      // Error overlay (if any)
                      _buildErrorOverlay(),

                      // Center loader overlay
                      IgnorePointer(
                        ignoring: true, // visual only
                        child: AnimatedOpacity(
                          duration: const Duration(milliseconds: 150),
                          opacity: _loadingOverlay ? 1 : 0,
                          child: Container(
                            color: Colors.black.withValues(alpha: 0.12),
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
      ),
    );
  }
}
