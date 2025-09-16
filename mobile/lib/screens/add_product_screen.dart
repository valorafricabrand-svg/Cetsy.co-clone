import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:file_picker/file_picker.dart';
import 'package:http/http.dart' as http;
import 'package:provider/provider.dart';
import 'package:mime/mime.dart';
import 'package:http_parser/http_parser.dart';

import '../providers/auth_provider.dart';
import '../config/constants.dart';
import 'manage_listing_screen.dart';

// Add Listing (mobile)
class AddProductScreen extends StatefulWidget {
  const AddProductScreen({super.key});

  @override
  State<AddProductScreen> createState() => _AddProductScreenState();
}

class _AddProductScreenState extends State<AddProductScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _descController = TextEditingController();
  final _priceController = TextEditingController();
  final _discountPctController = TextEditingController();
  final _stockController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _locationController = TextEditingController();
  // Variations at creation
  final _type1Name = TextEditingController();
  final _type1Opts = TextEditingController();
  final _type2Name = TextEditingController();
  final _type2Opts = TextEditingController();

  String _type = 'physical';
  int? _categoryId;
  List<Map<String, dynamic>> _categories = [];

  File? _imageFile;
  File? _digitalFile;
  bool _isLoading = false;

  Future<void> _pickImage() async {
    final picked = await ImagePicker().pickImage(source: ImageSource.gallery);

    if (picked != null) {
      setState(() => _imageFile = File(picked.path));
    }
  }

  Future<void> _pickDigital() async {
    final res = await FilePicker.platform.pickFiles(allowMultiple: false);
    if (res != null && res.files.single.path != null) {
      setState(() => _digitalFile = File(res.files.single.path!));
    }
  }

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    try {
      final uri = Uri.parse("${Constants.baseUrl}/categories/by-type/$_type");
      final resp = await http.get(uri, headers: {'Accept': 'application/json'});
      if (resp.statusCode == 200) {
        final list = (resp.body.isNotEmpty) ? List<Map<String, dynamic>>.from(jsonDecode(resp.body) as List) : <Map<String, dynamic>>[];
        setState(() {
          _categories = list;
          _categoryId = list.isNotEmpty ? (list.first['id'] as int) : null;
        });
      }
    } catch (_) {}
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    final token = Provider.of<AuthProvider>(context, listen: false).token;

    if (token == null) {
      _showDialog("Authentication required.");
      return;
    }

    setState(() => _isLoading = true);

    try {
      final uri = Uri.parse('${Constants.baseUrl}/products');

      final request = http.MultipartRequest('POST', uri)
        ..headers['Authorization'] = 'Bearer $token'
        ..headers['Accept'] = 'application/json'
        ..fields['name'] = _nameController.text.trim()
        ..fields['description'] = _descController.text.trim()
        ..fields['price'] = _priceController.text.trim()
        ..fields['type'] = _type;

      if (_discountPctController.text.trim().isNotEmpty) {
        request.fields['discount_percent'] = _discountPctController.text.trim();
      }
      if (_stockController.text.trim().isNotEmpty) {
        request.fields['stock'] = _stockController.text.trim();
      }
      if (_categoryId != null) request.fields['category_id'] = _categoryId.toString();
      if (_type == 'service') {
        if (_phoneController.text.trim().isNotEmpty) request.fields['phone'] = _phoneController.text.trim();
        if (_emailController.text.trim().isNotEmpty) request.fields['email'] = _emailController.text.trim();
        if (_locationController.text.trim().isNotEmpty) request.fields['location'] = _locationController.text.trim();
      }

      if (_imageFile != null) {
        final mimeType = lookupMimeType(_imageFile!.path) ?? 'image/jpeg';
        final mediaType = MediaType.parse(mimeType);

        request.files.add(await http.MultipartFile.fromPath(
          'image',
          _imageFile!.path,
          contentType: mediaType,
        ));
      }

      final response = await request.send();
      final respBody = await response.stream.bytesToString();
      if (response.statusCode == 201) {
        final decoded = jsonDecode(respBody) as Map<String, dynamic>;
        final product = decoded['product'] as Map<String, dynamic>;
        final id = product['id'] as int;

        // Upload digital file if present
        if (_type == 'digital' && _digitalFile != null) {
          final dfUri = Uri.parse('${Constants.baseUrl}/products/$id/digital-file');
          final dfReq = http.MultipartRequest('POST', dfUri)
            ..headers['Authorization'] = 'Bearer $token'
            ..headers['Accept'] = 'application/json';
          dfReq.files.add(await http.MultipartFile.fromPath('digital_file', _digitalFile!.path));
          await dfReq.send();
        }

        // Activate listing
        final setUri = Uri.parse('${Constants.baseUrl}/products/$id/settings');
        await http.post(setUri,
            headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
            body: {'is_active': '1', 'renewal_type': 'automatic', 'visibility': 'Public'});

        // If variations provided, save types
        try {
          final types = <Map<String,dynamic>>[];
          if (_type1Name.text.trim().isNotEmpty && _type1Opts.text.trim().isNotEmpty) {
            types.add({ 'name': _type1Name.text.trim(), 'options': _type1Opts.text.split(',').map((e)=>{'value': e.trim()}).toList() });
          }
          if (_type2Name.text.trim().isNotEmpty && _type2Opts.text.trim().isNotEmpty) {
            types.add({ 'name': _type2Name.text.trim(), 'options': _type2Opts.text.split(',').map((e)=>{'value': e.trim()}).toList() });
          }
          if (types.isNotEmpty) {
            final uriVar = Uri.parse('${Constants.baseUrl}/products/$id/variations');
            await http.post(uriVar, headers: {
              'Authorization': 'Bearer $token', 'Accept':'application/json', 'Content-Type':'application/json'
            }, body: jsonEncode({'types': types}));
          }
        } catch (_) {}

        if (!mounted) return;
        await _showSuccessAndMaybeManage(context, id);
        if (!mounted) return;
        _formKey.currentState!.reset();
        setState(() { _imageFile = null; _digitalFile = null; });
      } else {
        _showDialog("Failed to create listing.\n\n$respBody");
      }
    } catch (e) {
      _showDialog("Error: $e");
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showDialog(String message) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text("Add Listing"),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("OK"),
          ),
        ],
      ),
    );
  }

  Future<void> _showSuccessAndMaybeManage(BuildContext context, int id) async {
    return showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Listing created'),
        content: const Text('Your listing is active. Do you want to configure variations or shipping now?'),
        actions: [
          TextButton(onPressed: ()=> Navigator.pop(context), child: const Text('Later')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              Navigator.push(context, MaterialPageRoute(builder: (_)=>ManageListingScreen(productId: id)));
            },
            child: const Text('Manage Now'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Add New Listing")),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              DropdownButtonFormField<String>(
                initialValue: _type,
                decoration: const InputDecoration(labelText: 'Listing Type'),
                items: const [
                  DropdownMenuItem(value: 'physical', child: Text('Product')),
                  DropdownMenuItem(value: 'service', child: Text('Service')),
                  DropdownMenuItem(value: 'digital', child: Text('Digital')),
                ],
                onChanged: (v) {
                  if (v == null) return;
                  setState(() { _type = v; _categoryId = null; });
                  _loadCategories();
                },
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<int>(
                initialValue: _categoryId,
                decoration: const InputDecoration(labelText: 'Category'),
                items: _categories.map((c) => DropdownMenuItem<int>(
                  value: c['id'] as int,
                  child: Text(c['name'] as String),
                )).toList(),
                onChanged: (v) => setState(() => _categoryId = v),
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _nameController,
                decoration: const InputDecoration(labelText: "Listing Title"),
                validator: (value) =>
                    value!.isEmpty ? "Please enter a name" : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _descController,
                decoration: const InputDecoration(labelText: "Description"),
                maxLines: 3,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _priceController,
                decoration: const InputDecoration(labelText: "Price (${Constants.currency})"),
                keyboardType: TextInputType.number,
                validator: (value) =>
                    value!.isEmpty ? "Please enter a price" : null,
              ),
              const SizedBox(height: 20),
              const Align(alignment: Alignment.centerLeft, child: Text('Variations (optional)', style: TextStyle(fontWeight: FontWeight.w800))),
              const SizedBox(height: 8),
              Row(children:[
                Expanded(child: TextField(controller: _type1Name, decoration: const InputDecoration(labelText: 'Type 1 (e.g., Size)'))),
                const SizedBox(width: 8),
                Expanded(child: TextField(controller: _type1Opts, decoration: const InputDecoration(labelText: 'Options (comma-separated)'))),
              ]),
              const SizedBox(height: 8),
              Row(children:[
                Expanded(child: TextField(controller: _type2Name, decoration: const InputDecoration(labelText: 'Type 2 (optional)'))),
                const SizedBox(width: 8),
                Expanded(child: TextField(controller: _type2Opts, decoration: const InputDecoration(labelText: 'Options (comma-separated)'))),
              ]),
              const SizedBox(height: 12),
              TextFormField(
                controller: _discountPctController,
                decoration: const InputDecoration(labelText: 'Discount % (optional)'),
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _stockController,
                decoration: const InputDecoration(labelText: 'Stock (leave blank for unlimited)'),
                keyboardType: TextInputType.number,
              ),
              if (_type == 'service') ...[
                const SizedBox(height: 12),
                TextFormField(controller: _phoneController, decoration: const InputDecoration(labelText: 'Phone (optional)')),
                const SizedBox(height: 12),
                TextFormField(controller: _emailController, decoration: const InputDecoration(labelText: 'Email (optional)')),
                const SizedBox(height: 12),
                TextFormField(controller: _locationController, decoration: const InputDecoration(labelText: 'Location (optional)')),
              ],
              const SizedBox(height: 12),
              Row(
                children: [
                  ElevatedButton.icon(
                    onPressed: _pickImage,
                    icon: const Icon(Icons.image),
                    label: const Text("Select Image"),
                  ),
                  const SizedBox(width: 12),
                  if (_imageFile != null)
                    Text(
                      _imageFile!.path.split('/').last,
                      overflow: TextOverflow.ellipsis,
                    ),
                ],
              ),
              if (_type == 'digital') ...[
                const SizedBox(height: 12),
                Row(children: [
                  OutlinedButton.icon(onPressed: _pickDigital, icon: const Icon(Icons.attach_file), label: const Text('Attach Digital File')),
                  const SizedBox(width: 12),
                  Expanded(child: Text(_digitalFile?.path.split('/').last ?? '')),
                ]),
              ],
              const SizedBox(height: 20),
              _isLoading
                  ? const CircularProgressIndicator()
                  : ElevatedButton(
                      onPressed: _submitForm,
                      child: const Text("Publish Listing"),
                    ),
            ],
          ),
        ),
      ),
    );
  }
}







