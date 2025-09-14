class WalletTxn {
  final int id;
  final double credit;
  final double debit;
  final double balance;
  final String description;
  final String method;
  final String status;
  final String createdAt;

  const WalletTxn({
    required this.id,
    required this.credit,
    required this.debit,
    required this.balance,
    required this.description,
    required this.method,
    required this.status,
    required this.createdAt,
  });

  factory WalletTxn.fromJson(Map<String, dynamic> j) => WalletTxn(
        id: (j['id'] as num).toInt(),
        credit: double.tryParse('${j['credit'] ?? 0}') ?? 0,
        debit: double.tryParse('${j['debit'] ?? 0}') ?? 0,
        balance: double.tryParse('${j['balance'] ?? 0}') ?? 0,
        description: (j['description'] ?? '').toString(),
        method: (j['method'] ?? '').toString(),
        status: (j['status'] ?? '').toString(),
        createdAt: (j['created_at'] ?? '').toString(),
      );
}

class WalletPage {
  final List<WalletTxn> items;
  final bool hasNext;
  final int? nextPage;

  const WalletPage({required this.items, required this.hasNext, this.nextPage});

  factory WalletPage.fromPaginatedJson(Map<String, dynamic> json) {
    final list = (json['data'] as List?) ?? const [];
    final items = list.map((e) => WalletTxn.fromJson(e as Map<String, dynamic>)).toList();
    final meta = json['meta'] as Map<String, dynamic>?;
    final current = (meta?['current_page'] as num?)?.toInt();
    final last = (meta?['last_page'] as num?)?.toInt();
    final hasNext = (current != null && last != null) ? current < last : false;
    final next = hasNext && current != null ? current + 1 : null;
    return WalletPage(items: items, hasNext: hasNext, nextPage: next);
  }
}

