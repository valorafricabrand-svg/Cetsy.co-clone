import 'dart:convert';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../models/product.dart';
import '../services/product_service.dart';
import '../services/variation_service.dart';
import '../services/shipping_service.dart';
import '../services/meta_service.dart';
import '../config/constants.dart';

class ManageListingScreen extends StatefulWidget {
  final int productId;
  const ManageListingScreen({super.key, required this.productId});

  @override
  State<ManageListingScreen> createState() => _ManageListingScreenState();
}

class _ManageListingScreenState extends State<ManageListingScreen> with SingleTickerProviderStateMixin {
  late final TabController _tabController;
  Product? _product;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadProduct();
  }

  Future<void> _loadProduct() async {
    try {
      final p = await ProductService.getProduct(widget.productId);
      if (mounted) setState(() { _product = p; _loading = false; });
    } catch (_) { if (mounted) setState(() => _loading = false); }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Manage Listing'),
        bottom: const TabBar(tabs: [
          Tab(text: 'Variations'),
          Tab(text: 'Shipping'),
          Tab(text: 'Media'),
        ]),
      ),
      body: _loading || _product == null
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabController,
              children: [
                _VariationsTab(productId: widget.productId, onSaved: _loadProduct),
                _ShippingTab(productId: widget.productId),
                _MediaTab(productId: widget.productId),
              ],
            ),
    );
  }
}

class _VariationsTab extends StatefulWidget {
  final int productId;
  final Future<void> Function() onSaved;
  const _VariationsTab({required this.productId, required this.onSaved});

  @override
  State<_VariationsTab> createState() => _VariationsTabState();
}

class _VariationsTabState extends State<_VariationsTab> {
  final _type1Name = TextEditingController();
  final _type1Opts = TextEditingController();
  final _type2Name = TextEditingController();
  final _type2Opts = TextEditingController();
  bool _savingTypes = false;

  List<_VariantRow> _rows = [];
  bool _building = false;

  @override
  void dispose() {
    _type1Name.dispose(); _type1Opts.dispose();
    _type2Name.dispose(); _type2Opts.dispose();
    super.dispose();
  }

  Future<void> _saveTypes() async {
    final token = context.read<AuthProvider>().token; if (token == null) return;
    final types = <Map<String,dynamic>>[];
    if (_type1Name.text.trim().isNotEmpty && _type1Opts.text.trim().isNotEmpty) {
      types.add({ 'name': _type1Name.text.trim(), 'options': _type1Opts.text.split(',').map((e)=>{'value': e.trim()}).toList() });
    }
    if (_type2Name.text.trim().isNotEmpty && _type2Opts.text.trim().isNotEmpty) {
      types.add({ 'name': _type2Name.text.trim(), 'options': _type2Opts.text.split(',').map((e)=>{'value': e.trim()}).toList() });
    }
    if (types.isEmpty) return;
    setState(() => _savingTypes = true);
    try {
      await VariationService.saveTypes(token: token, productId: widget.productId, types: types);
      await widget.onSaved();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Variation types saved')));
      await _buildGeneratedRows();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally { if (mounted) setState(() => _savingTypes = false); }
  }

  Future<void> _buildGeneratedRows() async {
    setState(() { _building = true; _rows = []; });
    try {
      final p = await ProductService.getProduct(widget.productId);
      // Build combinations of option ids
      final typeOptions = p.variationTypes.map((t)=> t.options.map((o)=> {'id': o.id, 'value': o.value}).toList()).toList();
      List<List<Map<String,dynamic>>> combos = [[]];
      for (final opts in typeOptions) {
        combos = [for (final c in combos) for (final o in opts) [...c, o]];
      }
      final rows = <_VariantRow>[];
      for (final combo in combos) {
        final label = combo.map((e)=> e['value'] as String).join(' / ');
        final ids = combo.map((e)=> e['id'] as int).toList();
        rows.add(_VariantRow(label: label, optionIds: ids));
      }
      if (mounted) setState(() => _rows = rows);
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Build error: $e')));
    } finally {
      if (mounted) setState(() => _building = false);
    }
  }

  Future<void> _saveVariants() async {
    final token = context.read<AuthProvider>().token; if (token == null) return;
    final payload = _rows.map((r)=>{
      'price': double.tryParse(r.price.text.trim()) ?? 0.0,
      'stock': r.stock.text.trim().isEmpty ? null : int.tryParse(r.stock.text.trim()),
      'option_ids': r.optionIds,
    }).toList();
    try {
      await VariationService.saveVariants(token: token, productId: widget.productId, variants: payload);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Variants saved')));
    } catch (e) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'))); }
  }

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        const Text('Variation Types', style: TextStyle(fontWeight: FontWeight.w800)),
        const SizedBox(height: 8),
        Row(children:[
          Expanded(child: TextField(controller: _type1Name, decoration: const InputDecoration(labelText: 'Type 1 (e.g., Size)'))),
          const SizedBox(width: 8),
          Expanded(child: TextField(controller: _type1Opts, decoration: const InputDecoration(labelText: 'Options (comma-separated)'))),
        ]),
        const SizedBox(height: 12),
        Row(children:[
          Expanded(child: TextField(controller: _type2Name, decoration: const InputDecoration(labelText: 'Type 2 (optional)'))),
          const SizedBox(width: 8),
          Expanded(child: TextField(controller: _type2Opts, decoration: const InputDecoration(labelText: 'Options (comma-separated)'))),
        ]),
        const SizedBox(height: 12),
        Align(
          alignment: Alignment.centerRight,
          child: ElevatedButton.icon(onPressed: _savingTypes?null:_saveTypes, icon: const Icon(Icons.save), label: Text(_savingTypes? 'Saving...' : 'Save Types')),
        ),
        const Divider(height: 32),
        Row(children:[
          const Expanded(child: Text('Generated Variants', style: TextStyle(fontWeight: FontWeight.w800))),
          TextButton.icon(onPressed: _buildGeneratedRows, icon: const Icon(Icons.auto_awesome), label: const Text('Generate')),
        ]),
        if (_building) const Padding(padding: EdgeInsets.all(8), child: LinearProgressIndicator()),
        for (final r in _rows) Padding(
          padding: const EdgeInsets.symmetric(vertical: 6),
          child: Row(children:[
            Expanded(flex:2, child: Text(r.label, maxLines: 1, overflow: TextOverflow.ellipsis)),
            const SizedBox(width: 8),
            SizedBox(width: 100, child: TextField(controller: r.price, decoration: const InputDecoration(labelText: 'Price'), keyboardType: TextInputType.number)),
            const SizedBox(width: 8),
            SizedBox(width: 90, child: TextField(controller: r.stock, decoration: const InputDecoration(labelText: 'Stock'), keyboardType: TextInputType.number)),
          ]),
        ),
        const SizedBox(height: 12),
        Align(alignment: Alignment.centerRight, child: ElevatedButton.icon(onPressed: _saveVariants, icon: const Icon(Icons.save_alt), label: const Text('Save Variants'))),
      ],
    );
  }
}

class _VariantRow {
  final String label;
  final List<int> optionIds;
  final TextEditingController price = TextEditingController();
  final TextEditingController stock = TextEditingController();
  _VariantRow({required this.label, required this.optionIds});
}

class _ShippingTab extends StatefulWidget {
  final int productId;
  const _ShippingTab({required this.productId});

  @override
  State<_ShippingTab> createState() => _ShippingTabState();
}

class _ShippingTabState extends State<_ShippingTab> {
  final _profileName = TextEditingController(text: 'Standard shipping');
  final _originPostal = TextEditingController();
  final _daysMin = TextEditingController();
  final _daysMax = TextEditingController();
  bool _saving = false;
  int? _countryId;
  List<Map<String,dynamic>> _countries = [];
  final List<_RuleEditor> _rules = [ _RuleEditor() ];

  @override
  void initState() {
    super.initState();
    _loadCountries();
  }

  Future<void> _loadCountries() async {
    try {
      final list = await MetaService.fetchCountries();
      if (mounted) setState(() { _countries = list; _countryId = list.isNotEmpty ? list.first['id'] as int : null; });
    } catch (_) {}
  }

  Future<void> _save() async {
    final token = context.read<AuthProvider>().token; if (token == null) return;
    if (_countryId == null || _originPostal.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Country and origin postal code are required')));
      return;
    }
    setState(() => _saving = true);
    try {
      final jsonRules = jsonEncode(_rules.map((r) => r.toJson()).toList());
      await ShippingService.saveProfile(
        token: token,
        productId: widget.productId,
        profileName: _profileName.text.trim().isEmpty ? 'Standard shipping' : _profileName.text.trim(),
        countryId: _countryId!,
        originPostal: _originPostal.text.trim(),
        processingTimeId: 'custom',
        processingMin: _daysMin.text.trim().isEmpty ? null : int.tryParse(_daysMin.text.trim()),
        processingMax: _daysMax.text.trim().isEmpty ? null : int.tryParse(_daysMax.text.trim()),
        shippingRulesJson: jsonRules,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Shipping saved')));
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally { if (mounted) setState(() => _saving = false); }
  }

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        TextField(controller: _profileName, decoration: const InputDecoration(labelText: 'Profile Name')),
        const SizedBox(height: 12),
        DropdownButtonFormField<int>(
          value: _countryId,
          decoration: const InputDecoration(labelText: 'Origin Country'),
          items: _countries.map((c) => DropdownMenuItem<int>(value: c['id'] as int, child: Text(c['name'] as String))).toList(),
          onChanged: (v) => setState(() => _countryId = v),
        ),
        const SizedBox(height: 12),
        TextField(controller: _originPostal, decoration: const InputDecoration(labelText: 'Origin Postal Code')),
        const Divider(height: 32),
        Row(children:[
          const Expanded(child: Text('Shipping Rules', style: TextStyle(fontWeight: FontWeight.w800))),
          TextButton.icon(onPressed: ()=> setState(()=> _rules.add(_RuleEditor())), icon: const Icon(Icons.add), label: const Text('Add Rule')),
        ]),
        const SizedBox(height: 8),
        for (int i=0; i<_rules.length; i++) _rules[i].build(context, _countries, (){ setState(()=> _rules.removeAt(i)); }),
        const SizedBox(height: 12),
        Row(children: [
          Expanded(child: TextField(controller: _daysMin, decoration: const InputDecoration(labelText: 'Days Min'), keyboardType: TextInputType.number)),
          const SizedBox(width: 12),
          Expanded(child: TextField(controller: _daysMax, decoration: const InputDecoration(labelText: 'Days Max'), keyboardType: TextInputType.number)),
        ]),
        const SizedBox(height: 16),
        ElevatedButton.icon(onPressed: _saving?null:_save, icon: const Icon(Icons.save), label: Text(_saving?'Saving...':'Save Shipping')),
      ],
    );
  }
}

class _RuleEditor {
  String locationType = 'everywhere_else';
  int? countryId;
  final TextEditingController service = TextEditingController(text: 'Standard');
  bool free = true;
  final TextEditingController base = TextEditingController();
  final TextEditingController add = TextEditingController();

  Map<String,dynamic> toJson() => {
    'location_type': locationType,
    'country_id': locationType == 'country' ? countryId : null,
    'service': service.text.trim().isEmpty ? 'Standard' : service.text.trim(),
    'charge_type': free ? 'free' : 'fixed',
    'price_one': free ? 0 : (double.tryParse(base.text.trim()) ?? 0),
    'price_additional': free ? 0 : (double.tryParse(add.text.trim()) ?? 0),
  };

  Widget build(BuildContext context, List<Map<String,dynamic>> countries, VoidCallback onRemove) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 6),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Row(children: [
            Expanded(
              child: DropdownButtonFormField<String>(
                value: locationType,
                decoration: const InputDecoration(labelText: 'Location Type'),
                items: const [
                  DropdownMenuItem(value: 'everywhere_else', child: Text('Everywhere Else')),
                  DropdownMenuItem(value: 'country', child: Text('Specific Country')),
                ],
                onChanged: (v){ if (v!=null) locationType = v; },
              ),
            ),
            const SizedBox(width: 8),
            IconButton(onPressed: onRemove, icon: const Icon(Icons.delete_outline)),
          ]),
          if (locationType == 'country') ...[
            const SizedBox(height: 8),
            DropdownButtonFormField<int>(
              value: countryId,
              decoration: const InputDecoration(labelText: 'Destination Country'),
              items: countries.map((c) => DropdownMenuItem<int>(value: c['id'] as int, child: Text(c['name'] as String))).toList(),
              onChanged: (v) => countryId = v,
            ),
          ],
          const SizedBox(height: 8),
          TextField(controller: service, decoration: const InputDecoration(labelText: 'Service Name')),
          const SizedBox(height: 8),
          SwitchListTile(value: free, onChanged: (v){ free = v; }, title: const Text('Free Shipping')),
          if (!free) Row(children: [
            Expanded(child: TextField(controller: base, decoration: const InputDecoration(labelText: 'Base Rate'), keyboardType: TextInputType.number)),
            const SizedBox(width: 12),
            Expanded(child: TextField(controller: add, decoration: const InputDecoration(labelText: 'Additional Item Rate'), keyboardType: TextInputType.number)),
          ]),
        ]),
      ),
    );
  }
}

class _MediaTab extends StatefulWidget {
  final int productId;
  const _MediaTab({required this.productId});

  @override
  State<_MediaTab> createState() => _MediaTabState();
}

class _MediaTabState extends State<_MediaTab> {
  bool _uploading = false;
  List<String> _media = [];
  List<int> _mediaIds = [];
  bool _reorderMode = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final p = await ProductService.getProduct(widget.productId);
      setState(() { _media = p.media; _mediaIds = p.mediaIds; });
    } catch (_) {}
  }

  String _fullUrl(String path) {
    if (path.startsWith('http')) return path;
    var root = Constants.baseUrl.replaceFirst(RegExp(r'/api/?$'), '');
    if (root.endsWith('/')) root = root.substring(0, root.length - 1);
    return '$root/storage/$path';
  }

  Future<void> _pickAndUpload() async {
    final token = context.read<AuthProvider>().token; if (token == null) return;
    final picker = ImagePicker();
    final files = await picker.pickMultiImage();
    if (files.isEmpty) return;
    setState(() => _uploading = true);
    try {
      for (final x in files) {
        final uri = Uri.parse("${Constants.baseUrl}/products/${widget.productId}/media");
        final req = http.MultipartRequest('POST', uri)
          ..headers['Authorization'] = 'Bearer $token'
          ..headers['Accept'] = 'application/json';
        req.files.add(await http.MultipartFile.fromPath('image', x.path));
        await req.send();
      }
      await _load();
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Images uploaded')));
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Upload error: $e')));
    } finally { if (mounted) setState(() => _uploading = false); }
  }

  Future<void> _deleteAt(int index) async {
    final token = context.read<AuthProvider>().token; if (token == null) return;
    final id = _mediaIds[index];
    final uri = Uri.parse("${Constants.baseUrl}/products/${widget.productId}/media/$id");
    try {
      final res = await http.delete(uri, headers: {'Authorization': 'Bearer $token', 'Accept':'application/json'});
      if (res.statusCode >= 400) throw Exception(res.body);
      await _load();
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Image deleted')));
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Delete error: $e')));
    }
  }

  Future<void> _saveOrder() async {
    final token = context.read<AuthProvider>().token; if (token == null) return;
    final uri = Uri.parse("${Constants.baseUrl}/products/${widget.productId}/media/reorder");
    try {
      final res = await http.post(uri,
        headers: {'Authorization':'Bearer $token','Accept':'application/json','Content-Type':'application/json'},
        body: jsonEncode({'order': _mediaIds}),
      );
      if (res.statusCode >= 400) throw Exception(res.body);
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Order saved')));
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Reorder error: $e')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      Padding(
        padding: const EdgeInsets.all(12),
        child: Row(children: [
          ElevatedButton.icon(onPressed: _uploading?null:_pickAndUpload, icon: const Icon(Icons.add_photo_alternate), label: const Text('Add Images')),
          const SizedBox(width: 12),
          if (_uploading) const Expanded(child: LinearProgressIndicator()),
          const Spacer(),
          TextButton.icon(
            onPressed: () async {
              setState(() => _reorderMode = !_reorderMode);
              if (!_reorderMode) await _saveOrder();
            },
            icon: Icon(_reorderMode ? Icons.check : Icons.reorder),
            label: Text(_reorderMode ? 'Done' : 'Reorder'),
          )
        ]),
      ),
      Expanded(
        child: _reorderMode
            ? ReorderableListView.builder(
                padding: const EdgeInsets.all(12),
                itemCount: _media.length,
                onReorder: (oldIndex, newIndex) {
                  setState(() {
                    if (newIndex > oldIndex) newIndex -= 1;
                    final path = _media.removeAt(oldIndex);
                    final mid  = _mediaIds.removeAt(oldIndex);
                    _media.insert(newIndex, path);
                    _mediaIds.insert(newIndex, mid);
                  });
                },
                itemBuilder: (_, i) {
                  final url = _fullUrl(_media[i]);
                  return ListTile(
                    key: ValueKey(_mediaIds[i]),
                    leading: ClipRRect(borderRadius: BorderRadius.circular(6), child: Image.network(url, width: 56, height: 56, fit: BoxFit.cover)),
                    title: Text(_media[i].split('/').last, overflow: TextOverflow.ellipsis),
                    trailing: const Icon(Icons.drag_handle),
                  );
                },
              )
            : GridView.builder(
                padding: const EdgeInsets.all(12),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 3, crossAxisSpacing: 8, mainAxisSpacing: 8),
                itemCount: _media.length,
                itemBuilder: (_, i) {
                  final url = _fullUrl(_media[i]);
                  return Stack(children: [
                    Positioned.fill(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.network(url, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const ColoredBox(color: Colors.black12)),
                      ),
                    ),
                    Positioned(
                      top: 4, right: 4,
                      child: CircleAvatar(
                        radius: 16,
                        backgroundColor: Colors.black54,
                        child: IconButton(
                          padding: EdgeInsets.zero,
                          onPressed: () => _deleteAt(i),
                          icon: const Icon(Icons.delete, size: 16, color: Colors.white),
                        ),
                      ),
                    ),
                  ]);
                },
              ),
      ),
    ]);
  }
}

