@extends('layouts.app')

@section('content')
<div class="content">
    <div class="row gx-3 mt-3 pb-5">
        <div class="col-12 col-xxl-9">
            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- BEGIN: Product Information -->
                <div class="card p-4 mb-4">
                    <div class="border rounded-3 p-4">
                        <div class="d-flex align-items-center border-bottom pb-3">
                            <i class="bi bi-chevron-down me-2"></i>
                            <h5 class="mb-0">Listing details</h5>
                        </div>
                        <div class="mt-4">
                            <!-- Name -->
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Product Name</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                        <div class="text-muted small mt-2">
                                            Include min. 40 characters to make it more attractive and easy for buyers to find.
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" placeholder="Product name" required>
                                    @error('name')
                                        <span class="text-danger">*{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Category</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <select name="category_id" id="category" class="form-select" required>
                                        <option value="">— Select Category —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id)==$cat->id)>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Product Type -->
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Product Type</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <select name="product_type" id="product_type" class="form-select" required>
                                        <option value="">— Select Type —</option>
                                        <option value="physical" @selected(old('product_type', $product->product_type)=='physical')>Physical</option>
                                        <option value="digital" @selected(old('product_type', $product->product_type)=='digital')>Digital</option>
                                    </select>
                                    @error('product_type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Renewal options -->
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Renewal options</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                        <div class="text-muted small mt-2">
                                            Automatic: This listing will renew as it expires (recommended).<br>
                                            Manual: I'll renew expired listings myself.
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 d-flex align-items-center gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="renewal_option" id="renewal_automatic" value="0" {{ old('renewal_option', $product->renewal_option) == '0' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="renewal_automatic">Automatic</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="renewal_option" id="renewal_manual" value="1" {{ old('renewal_option', $product->renewal_option) == '1' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="renewal_manual">Manual</label>
                                    </div>
                                    @error('renewal_option')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Listing Fee Renewal -->
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Listing Fee Renewal</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <select name="listTypeFee_id" id="listTypeFee_id" class="form-select" required>
                                        <option value="">--choose--</option>
                                        @foreach ($category_listFee_types as $listType)
                                            <option value="{{ $listType->id }}" {{ old('listTypeFee_id', $product->listTypeFee_id) == $listType->id ? 'selected' : '' }}>{{ $listType->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('listTypeFee_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Condition -->
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Condition</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <select name="condition" id="condition" class="form-select" required>
                                        <option value="">— Select Condition —</option>
                                        <option value="new" @selected(old('condition', $product->condition)=='new')>New</option>
                                        <option value="refurbished" @selected(old('condition', $product->condition)=='refurbished')>Refurbished</option>
                                        <option value="used" @selected(old('condition', $product->condition)=='used')>Used</option>
                                    </select>
                                    @error('condition')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Description</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                        <div class="text-muted small mt-2">
                                            Provide a detailed description of your product.
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <textarea name="description" id="description" class="form-control" rows="6">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BEGIN: Pricing & Inventory -->
                <div class="card p-4 mb-4">
                    <div class="border rounded-3 p-4">
                        <div class="d-flex align-items-center border-bottom pb-3">
                            <i class="bi bi-chevron-down me-2"></i>
                            <h5 class="mb-0">Pricing & Inventory</h5>
                        </div>
                        <div class="mt-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="price" class="form-label">Price (KES)</label>
                                    <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" class="form-control" min="0" step="0.01" required>
                                    @error('price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="discount_price" class="form-label">Discount Price (KES)</label>
                                    <input type="number" id="discount_price" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" class="form-control" min="0" step="0.01">
                                    @error('discount_price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" class="form-control" min="0" step="1" required>
                                    @error('stock')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="low_stock" class="form-label">Low Stock Alert</label>
                                    <input type="number" id="low_stock" name="low_stock" value="{{ old('low_stock', $product->low_stock) }}" class="form-control" min="0" required>
                                    @error('low_stock')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BEGIN: Variations -->
                <div class="card p-4 mb-4">
                    <div class="border rounded-3 p-4">
                        <div class="d-flex align-items-center border-bottom pb-3 justify-content-between">
                            <h5 class="mb-0">
                                <i class="bi bi-chevron-down me-2"></i> Variation
                            </h5>
                        </div>
                        <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                            <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                <div class="text-left">
                                    <div class="d-flex align-items-center">
                                        <div class="fw-medium">Variation One</div>
                                        <div class="badge bg-light text-dark ms-2">Optional</div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <input type="text" name="variation_one_name" class="form-control mb-3" placeholder="Variation Name example; Color" value="{{ old('variation_one_name', $product->variation_one_name) }}">
                                @error('variation_one_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div id="variation-one-options-list">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="variationOneOptionInput" placeholder="Name Of option example;Red">
                                        <input type="text" class="form-control" id="variationOneOptionPriceInput" placeholder="Price">
                                        <button class="btn btn-primary" type="button" onclick="addVariationOneOption()">Add</button>
                                    </div>
                                </div>
                                <div id="variation-one-options-container">
                                    @if($product->variation_one_options)
                                        @foreach(json_decode($product->variation_one_options) as $option)
                                            <div class="input-group mb-2">
                                                <input type="text" name="variationOneOptions[][title]" class="form-control" value="{{ $option->title }}" readonly>
                                                <input type="text" name="variationOneOptions[][price]" class="form-control" value="{{ $option->price }}" readonly>
                                                <button class="btn btn-danger" type="button" onclick="this.parentNode.remove()">Remove</button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                            <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                <div class="text-left">
                                    <div class="d-flex align-items-center">
                                        <div class="fw-medium">Variation Two</div>
                                        <div class="badge bg-light text-dark ms-2">Optional</div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <input type="text" name="variation_two_name" class="form-control mb-3" placeholder="Variation Name" value="{{ old('variation_two_name', $product->variation_two_name) }}">
                                @error('variation_two_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div id="variation-two-options-list">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="variationTwoOptionInput" placeholder="Name Of option">
                                        <input type="text" class="form-control" id="variationTwoOptionPriceInput" placeholder="Price">
                                        <button class="btn btn-primary" type="button" onclick="addVariationTwoOption()">Add</button>
                                    </div>
                                </div>
                                <div id="variation-two-options-container">
                                    @if($product->variation_two_options)
                                        @foreach(json_decode($product->variation_two_options) as $option)
                                            <div class="input-group mb-2">
                                                <input type="text" name="variationTwoOptions[][title]" class="form-control" value="{{ $option->title }}" readonly>
                                                <input type="text" name="variationTwoOptions[][price]" class="form-control" value="{{ $option->price }}" readonly>
                                                <button class="btn btn-danger" type="button" onclick="this.parentNode.remove()">Remove</button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BEGIN: Digital Fields -->
                <div class="card p-4 mb-4 collapse" id="digital-fields">
                    <div class="border rounded-3 p-4">
                        <div class="d-flex align-items-center border-bottom pb-3">
                            <i class="bi bi-chevron-down me-2"></i>
                            <h5 class="mb-0">Digital Product Details</h5>
                        </div>
                        <div class="mt-4">
                            <div class="mb-3">
                                <label for="download_file" class="form-label">Downloadable File</label>
                                <input type="file" id="download_file" name="download_file" class="form-control" accept=".zip,.pdf,.mp3,.mp4">
                                @if($product->download_file)
                                    <div class="form-text">Current file: {{ basename($product->download_file) }}</div>
                                @endif
                                @error('download_file')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="download_limit" class="form-label">Download Limit</label>
                                <input type="number" id="download_limit" name="download_limit" class="form-control" min="1" value="{{ old('download_limit', $product->download_limit) }}">
                                <div class="form-text">Max times buyer can download.</div>
                                @error('download_limit')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="access_expiry" class="form-label">Access Expiry (days)</label>
                                <input type="number" id="access_expiry" name="access_expiry" class="form-control" min="1" value="{{ old('access_expiry', $product->access_expiry) }}">
                                <div class="form-text">Days link remains active.</div>
                                @error('access_expiry')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BEGIN: Shipping -->
                <div class="card p-4 mb-4">
                    <div class="border rounded-3 p-4">
                        <div class="d-flex align-items-center border-bottom pb-3">
                            <i class="bi bi-chevron-down me-2"></i>
                            <h5 class="mb-0">Shipping</h5>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                <div>
                                    <span>Set clear and realistic shipping expectations for shoppers by providing accurate processing time.</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Shipping options</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 border border-2 border-dashed rounded-3 p-3">
                                    <div class="text-center">
                                        Fill out your shipping options for this listing. You can keep these options specific to this listing, or save them as a shipping profile to apply them to future listings.
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Country of origin</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <select name="origin_id" class="form-select">
                                        <option value="">Select country</option>
                                        @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('origin_id', $product->origin_id) == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('origin_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Origin postal code</div>
                                            <div class="badge bg-light text-dark ms-2">Optional</div>
                                        </div>
                                        <div class="text-muted small mt-2">
                                            Where do you ship packages from?
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="text" name="origin_postal_code" class="form-control" placeholder="Postal Code" value="{{ old('origin_postal_code', $product->origin_postal_code) }}">
                                    @error('origin_postal_code')
                                    <span class="text-danger">*{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Processing time</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <select name="processing_time_id" class="form-select">
                                        <option selected value="">Select your processing Time</option>
                                        @foreach ($processing_times as $processingtime)
                                        <option value="{{ $processingtime->id }}" {{ old('processing_time_id', $product->processing_time_id) == $processingtime->id ? 'selected' : '' }}>
                                            {{ $processingtime->start_day }} - {{ $processingtime->end_day }} days
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('processing_time_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BEGIN: Standard Shipping -->
                <div class="card p-4 mb-4">
                    <div class="border rounded-3 p-4">
                        <div class="d-flex align-items-center border-bottom pb-3">
                            <i class="bi bi-chevron-down me-2"></i>
                            <h5 class="mb-0">Standard Shipping</h5>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                <div>
                                    <span>Only shoppers in countries you ship to will see your listings in search.</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Shipping options</div>
                                            <div class="badge bg-light text-dark ms-2">Required</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 border border-2 border-dashed rounded-3 p-3">
                                    <div class="text-center">
                                        Fill out your shipping options for this listing. You can keep these options specific to this listing, or save them as a shipping profile to apply them to future listings.
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">{{ $countryOriginName }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="fw-bold">Courier Services</label>
                                    <select name="local_shipping_service_id" id="local_shipping_service" class="form-select mb-3" onchange="toggleOtherShippingService(this.value, 'local')">
                                        <option value="" selected>Select</option>
                                        @foreach ($shippinService as $val)
                                        <option value="{{ $val['id'] }}" {{ old('local_shipping_service_id', $product->local_shipping_service_id) == $val['id'] ? 'selected' : '' }}>{{ $val['name'] }}</option>
                                        @endforeach
                                        <option value="0" {{ old('local_shipping_service_id', $product->local_shipping_service_id) == '0' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('shipping_service_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div id="local_other_shipping_service" style="display: none;">
                                        <input type="text" name="local_shipping_service_other" class="form-control" placeholder="Please specify other shipping service" value="{{ old('local_shipping_service_other', $product->local_shipping_service_other) }}">
                                    </div>
                                    <label class="fw-bold mt-3">Delivery Time</label>
                                    <select name="localshippingPeriod_id" class="form-select">
                                        <option value="">Select</option>
                                        @foreach ($shippingPeriods as $period)
                                        <option value="{{ $period->id }}" {{ old('localshippingPeriod_id', $product->localshippingPeriod_id) == $period->id ? 'selected' : '' }}>{{ $period->start_day }} to {{ $period->end_day }} days</option>
                                        @endforeach
                                    </select>
                                    @error('localshippingPeriod_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="fw-bold">What you'll charge</label>
                                    <select name="shipping_type" class="form-select mb-3">
                                        <option value="">Select</option>
                                        @foreach ($shippingChargeType as $type)
                                        <option value="{{ $type['id'] }}" {{ old('shipping_type', $product->shipping_type) == $type['id'] ? 'selected' : '' }}>{{ $type['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('shipping_type')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    @if ($shipping_type == 1)
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="fw-bold">One Item Fee</label>
                                            <input type="number" name="local_default_shipping_price" class="form-control" required value="{{ old('local_default_shipping_price', $product->local_default_shipping_price) }}" step="0.001">
                                            @error('local_default_shipping_price')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold">Additional Item Fee</label>
                                            <input type="number" name="local_shipping_price" class="form-control" value="{{ old('local_shipping_price', $product->local_shipping_price) }}" step="0.001">
                                            @error('local_shipping_price')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">International</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="fw-bold">Courier Services</label>
                                    <select name="international_shipping_service_id" id="international_shipping_service" class="form-select mb-3" onchange="toggleOtherShippingService(this.value, 'international')">
                                        <option value="">Select</option>
                                        @foreach ($shippinService as $val)
                                        <option value="{{ $val['id'] }}" {{ old('international_shipping_service_id', $product->international_shipping_service_id) == $val['id'] ? 'selected' : '' }}>{{ $val['name'] }}</option>
                                        @endforeach
                                        <option value="0" {{ old('international_shipping_service_id', $product->international_shipping_service_id) == '0' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('shipping_service_other_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div id="international_other_shipping_service" style="display: none;">
                                        <input type="text" name="international_shipping_service_other" class="form-control" placeholder="Please specify other shipping service" value="{{ old('international_shipping_service_other', $product->international_shipping_service_other) }}">
                                    </div>
                                    <label class="fw-bold mt-3">Delivery Time</label>
                                    <select name="internationalshippingPeriod_id" class="form-select">
                                        <option value="">Select</option>
                                        @foreach ($shippingPeriods as $period)
                                        <option value="{{ $period->id }}" {{ old('internationalshippingPeriod_id', $product->internationalshippingPeriod_id) == $period->id ? 'selected' : '' }}>{{ $period->start_day }} to {{ $period->end_day }} days</option>
                                        @endforeach
                                    </select>
                                    @error('internationalshippingPeriod_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="fw-bold">What you'll charge</label>
                                    <select name="shipping_type_other" class="form-select mb-3">
                                        @foreach ($shippingChargeType as $type)
                                        <option value="{{ $type['id'] }}" {{ old('shipping_type_other', $product->shipping_type_other) == $type['id'] ? 'selected' : '' }}>{{ $type['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('shipping_type_other')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    @if ($shipping_type_other == 1)
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="fw-bold">One Item Fee</label>
                                            <input type="number" name="default_shipping_price" class="form-control" required value="{{ old('default_shipping_price', $product->default_shipping_price) }}" step="0.001">
                                            @error('default_shipping_price')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold">Additional Item Fee</label>
                                            <input type="number" name="shipping_price" class="form-control" value="{{ old('shipping_price', $product->shipping_price) }}" step="0.001">
                                            @error('shipping_price')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BEGIN: Return and Exchanges -->
                <div class="card p-4 mb-4">
                    <div class="border rounded-3 p-4">
                        <div class="d-flex align-items-center border-bottom pb-3 justify-content-between">
                            <h5 class="mb-0">
                                <i class="bi bi-chevron-down me-2"></i> Returns and exchanges
                            </h5>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                <div>
                                    <span>The selected policy will apply to this listing.</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Returns</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="form-check form-switch">
                                        <input name="item_return" id="item_return" class="form-check-input" type="checkbox" value="1" {{ old('item_return', $product->item_return) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="item_return">{{$itemReturnString}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Exchanges</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="form-check form-switch">
                                        <input name="item_exchange" id="item_exchange" class="form-check-input" type="checkbox" value="1" {{ old('item_exchange', $product->item_exchange) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="item_exchange">{{$itemExchangeString}}</label>
                                    </div>
                                </div>
                            </div>
                            @if($item_exchange || $item_return)
                            <div class="d-flex flex-column flex-xl-row mt-4 pt-4">
                                <div class="mb-3 mb-xl-0 me-xl-4" style="width: 256px;">
                                    <div class="text-left">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-medium">Buyer must contact me and ship item back within</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <select name="total_return_days" class="form-select">
                                        @foreach ($returnDeliveryDays as $returnDelivery)
                                        <option value="{{ $returnDelivery['id'] }}" {{ old('total_return_days', $product->total_return_days) == $returnDelivery['id'] ? 'selected' : '' }}>{{ $returnDelivery['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('total_return_days')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- BEGIN: Images -->
                <div class="card p-4 mb-4">
                    <div class="border rounded-3 p-4">
                        <div class="d-flex align-items-center border-bottom pb-3">
                            <i class="bi bi-chevron-down me-2"></i>
                            <h5 class="mb-0">Product Images</h5>
                        </div>
                        <div class="mt-4">
                            <div class="mb-3">
                                <label for="images" class="form-label">Upload Images</label>
                                <input type="file" id="images" name="images[]" multiple accept="image/*" class="form-control">
                                <div class="form-text">You can select multiple images.</div>
                                @error('images')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @if($product->media->count())
                                <div class="mt-3">
                                    <label class="form-label">Current Images</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($product->media as $media)
                                            <div class="position-relative">
                                                <img src="{{ asset('storage/'.$media->url) }}" class="rounded" style="width:100px;height:100px;object-fit:cover;">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="deleteImage({{ $media->id }})">×</button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-end gap-3 mt-4">
                    <button type="submit" class="btn btn-primary w-100 w-md-auto">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- TinyMCE --}}
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
tinymce.init({
    selector: '#description',
    plugins: 'image link media code fullscreen',
    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | image link media | code fullscreen',
    menubar: false,
    height: 300
});

// Toggle digital fields
document.getElementById('product_type').addEventListener('change', function(){
    let digi = new bootstrap.Collapse(document.getElementById('digital-fields'), {
        toggle: this.value==='digital'
    });
});

// Variation One
function addVariationOneOption() {
    const title = document.getElementById('variationOneOptionInput').value.trim();
    const price = document.getElementById('variationOneOptionPriceInput').value.trim();
    if (title) {
        const container = document.getElementById('variation-one-options-container');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `<input type="text" name="variationOneOptions[][title]" class="form-control" value="${title}" readonly><input type="text" name="variationOneOptions[][price]" class="form-control" value="${price}" readonly><button class="btn btn-danger" type="button" onclick="this.parentNode.remove()">Remove</button>`;
        container.appendChild(div);
        document.getElementById('variationOneOptionInput').value = '';
        document.getElementById('variationOneOptionPriceInput').value = '';
    }
}

// Variation Two
function addVariationTwoOption() {
    const title = document.getElementById('variationTwoOptionInput').value.trim();
    const price = document.getElementById('variationTwoOptionPriceInput').value.trim();
    if (title) {
        const container = document.getElementById('variation-two-options-container');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `<input type="text" name="variationTwoOptions[][title]" class="form-control" value="${title}" readonly><input type="text" name="variationTwoOptions[][price]" class="form-control" value="${price}" readonly><button class="btn btn-danger" type="button" onclick="this.parentNode.remove()">Remove</button>`;
        container.appendChild(div);
        document.getElementById('variationTwoOptionInput').value = '';
        document.getElementById('variationTwoOptionPriceInput').value = '';
    }
}

// Toggle other shipping service
function toggleOtherShippingService(value, type) {
    const element = document.getElementById(`${type}_other_shipping_service`);
    if (value === '0') {
        element.style.display = 'block';
    } else {
        element.style.display = 'none';
    }
}

// Delete image
function deleteImage(mediaId) {
    if (confirm('Are you sure you want to delete this image?')) {
        fetch(`/media/${mediaId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}
</script>
@endsection
