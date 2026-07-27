import Alpine from 'alpinejs';

export function cartPage() {
    return {
        confirmingClear: false,

        get cart() {
            return Alpine.store('cart');
        },

        clearCart() {
            this.cart.clear();
            this.confirmingClear = false;
        },
    };
}
