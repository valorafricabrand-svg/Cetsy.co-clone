@extends('theme.layouts.main')

@section('main')
<section class="cart-page py-5 bg-light" x-data="cartComponent()" x-init="init()">
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{!! session('success') !!}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($cart && count($cart) > 0)
            <form action="{{ route('cart.updateShippingSelection') }}" method="POST" id="shippingSelectionForm" @submit.prevent="submitForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered cart-table align-middle">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th style="width: 140px;">Quantity</th>
                                <th>Shipping Profile</th>
                                <th>Shipping Cost</th>
                                <th>Total</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="item.id">
                                <tr>
                                    {{-- Hidden inputs --}}
                                    <input type="hidden" :name="`product_ids[]`" :value="item.id">
                                    <input type="hidden" :name="`quantities[]`" :value="item.quantity">

                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3" x-text="item.photo ? '' : 'No Image'">
                                                <img :src="item.photo ? `{{ url('/') }}/storage/${item.photo}` : ''" :alt="item.name" width="80" x-show="item.photo">
                                            </div>
                                            <div x-text="item.name"></div>
                                        </div>
                                    </td>
                                    <td x-text="currency + ' ' + formatPrice(item.price)"></td>
                                  <td>
    <div class="d-flex align-items-center justify-content-center">
        <button 
            type="button" 
            @click="changeQuantity(index, -1)" 
            class="btn btn-sm btn-outline-secondary me-2" 
            title="Decrease quantity" 
            :disabled="item.quantity <= 1"
        >-</button>

        <input 
            type="text" 
            :value="item.quantity" 
            readonly 
            class="form-control form-control-sm text-center" 
            style="width: 60px;" 
        />

        <button 
            type="button" 
            @click="changeQuantity(index, 1)" 
            class="btn btn-sm btn-outline-secondary ms-2" 
            title="Increase quantity"
        >+</button>
    </div>
</td>

                                    <td>
                                        <select 
                                            :name="`shipping_profile_ids[${index}]`" 
                                            class="form-select form-select-sm" 
                                            required 
                                            x-model.number="item.selected_shipping_profile_id"
                                            @change="updateShippingCost(index)"
                                        >
                                            <template x-for="profile in item.shipping_profiles" :key="profile.id">
                                                <option :value="profile.id" x-text="`${profile.name} - KES ${formatPrice(profile.base_rate)}`"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td x-text="currency + ' ' + formatPrice(item.shippingCost)" :id="`shipping-cost-${index}`"></td>
                                    <td x-text="currency + ' ' + formatPrice(itemTotal(item))"></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" @click="removeItem(index)" title="Remove item">&times;</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <ul class="list-group w-50">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Subtotal</span>
                            <span x-text="currency + ' ' + formatPrice(subtotal)"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Shipping Cost</span>
                            <span x-text="currency + ' ' + formatPrice(totalShipping)"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span x-text="currency + ' ' + formatPrice(grandTotal)"></span>
                        </li>
                    </ul>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary btn-lg">Update Shipping & Proceed to Checkout</button>
                </div>
            </form>
        @else
            <div class="text-center py-5">
                <h4>Your cart is empty</h4>
                <a href="{{ route('listings') }}" class="btn btn-primary">Return to Shop</a>
            </div>
        @endif
    </div>
</section>

<script>
    function cartComponent() {
        return {
            currency: '{{ get_currency() }}',
            items: @json(array_values($cart)),

            init() {
                this.items.forEach((item, index) => {
                    this.updateShippingCost(index);
                });
            },

            changeQuantity(index, delta) {
                const item = this.items[index];
                const newQty = item.quantity + delta;
                if (newQty < 1) return;

                // Update quantity locally
                item.quantity = newQty;
                this.updateShippingCost(index);

                // Send update to backend
                fetch("{{ route('cart.update') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: item.id,
                        action: delta > 0 ? 'increase' : 'decrease'
                    })
                }).then(res => {
                    if (!res.ok) throw new Error('Failed to update cart');
                    return res.json();
                }).catch(() => {
                    alert('Error updating cart. Please reload the page.');
                });
            },

            updateShippingCost(index) {
                const item = this.items[index];
                const selectedProfile = item.shipping_profiles.find(p => p.id === item.selected_shipping_profile_id);
                item.shippingCost = selectedProfile ? selectedProfile.base_rate * item.quantity : 0;
            },

            itemTotal(item) {
                return (item.price * item.quantity) + (item.shippingCost || 0);
            },

            get subtotal() {
                return this.items.reduce((sum, item) => sum + item.price * item.quantity, 0);
            },

            get totalShipping() {
                return this.items.reduce((sum, item) => sum + (item.shippingCost || 0), 0);
            },

            get grandTotal() {
                return this.subtotal + this.totalShipping;
            },

            removeItem(index) {
                if (!confirm('Remove this product?')) return;

                const item = this.items[index];

                fetch("{{ route('cart.remove') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: item.id })
                }).then(res => {
                    if (!res.ok) throw new Error('Failed to remove item');
                    this.items.splice(index, 1);
                }).catch(() => {
                    alert('Error removing item. Please reload the page.');
                });
            },

            submitForm() {
                this.$el.submit();
            },

            formatPrice(value) {
                return parseFloat(value).toFixed(2);
            }
        }
    }
</script>
@endsection
