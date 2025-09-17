import 'package:flutter/widgets.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../providers/currency_provider.dart';

extension BuildContextMoney on BuildContext {
  /// Formats a USD amount into the currently selected currency
  /// using the symbol (or code) and localized number formatting.
  String money(num usd, {bool withCode = false}) {
    final c = watch<CurrencyProvider>();
    final converted = c.convert(usd.toDouble());
    final unit = withCode ? c.code : c.symbol;
    return '$unit ${NumberFormat.decimalPattern().format(converted)}';
  }
}

