import Alpine from 'alpinejs';

export function checkoutPage() {
    return {
        orderDiscount: 0,
        paymentMethod: 'cash',
        amountTendered: null,
        submitting: false,
        errorMessage: '',

        get cart() {
            return Alpine.store('cart');
        },

        get displayTotal() {
            return Math.max(0, this.cart.totals.total - (parseFloat(this.orderDiscount) || 0));
        },

        get changeDue() {
            if (this.paymentMethod !== 'cash' || this.amountTendered === null || this.amountTendered === '') {
                return null;
            }
            return Math.round((parseFloat(this.amountTendered) - this.displayTotal + Number.EPSILON) * 100) / 100;
        },

        get canSubmit() {
            if (this.cart.isEmpty || this.submitting) return false;
            if (this.paymentMethod === 'cash') {
                return this.amountTendered !== null && this.amountTendered !== '' && parseFloat(this.amountTendered) >= this.displayTotal;
            }
            return true;
        },

        async submit() {
            if (!this.canSubmit) return;
            this.submitting = true;
            this.errorMessage = '';

            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            const payload = {
                items: this.cart.items.map((item) => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    discount_amount: item.discountAmount,
                })),
                order_discount: parseFloat(this.orderDiscount) || 0,
                payment_method: this.paymentMethod,
                amount_tendered: this.paymentMethod === 'cash' ? parseFloat(this.amountTendered) : null,
            };

            try {
                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (!response.ok) {
                    this.errorMessage = data.message || 'Checkout failed. Please review your cart and try again.';
                    this.submitting = false;
                    return;
                }

                this.cart.clear();
                window.location = data.redirect;
            } catch (e) {
                this.errorMessage = 'Network error — please check your connection and try again.';
                this.submitting = false;
            }
        },
    };
}
