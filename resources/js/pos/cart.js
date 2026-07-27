const STORAGE_KEY = 'mobilepos.cart.v1';

function loadFromStorage() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        return [];
    }
}

function round2(n) {
    return Math.round((n + Number.EPSILON) * 100) / 100;
}

/**
 * Registers a global `cart` Alpine store backed by localStorage so the cart
 * survives an accidental reload. Call before Alpine.start().
 */
export function registerCartStore(Alpine) {
    Alpine.store('cart', {
        items: loadFromStorage(),

        persist() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items));
        },

        // Adds a product, or increments quantity if it's already in the cart.
        addProduct(product, qty = 1) {
            const existing = this.items.find((item) => item.id === product.id);
            if (existing) {
                existing.quantity += qty;
            } else {
                this.items.push({
                    id: product.id,
                    barcode: product.barcode,
                    name: product.name,
                    price: product.price,
                    taxRate: product.tax_rate ?? 0,
                    quantity: qty,
                    discountAmount: 0,
                });
            }
            this.persist();
        },

        setQuantity(id, qty) {
            const item = this.items.find((i) => i.id === id);
            if (!item) return;
            item.quantity = Math.max(1, Math.floor(qty) || 1);
            this.persist();
        },

        increment(id, by = 1) {
            const item = this.items.find((i) => i.id === id);
            if (!item) return;
            item.quantity = Math.max(1, item.quantity + by);
            this.persist();
        },

        setDiscount(id, amount) {
            const item = this.items.find((i) => i.id === id);
            if (!item) return;
            item.discountAmount = Math.max(0, parseFloat(amount) || 0);
            this.persist();
        },

        remove(id) {
            this.items = this.items.filter((i) => i.id !== id);
            this.persist();
        },

        clear() {
            this.items = [];
            this.persist();
        },

        get isEmpty() {
            return this.items.length === 0;
        },

        get itemCount() {
            return this.items.reduce((sum, i) => sum + i.quantity, 0);
        },

        lineFor(item) {
            const lineGross = round2(item.price * item.quantity);
            const discount = Math.min(item.discountAmount || 0, lineGross);
            const taxable = round2(lineGross - discount);
            const tax = round2(taxable * ((item.taxRate || 0) / 100));
            const total = round2(taxable + tax);
            return { lineGross, discount, taxable, tax, total };
        },

        get totals() {
            return this.items.reduce(
                (acc, item) => {
                    const line = this.lineFor(item);
                    acc.subtotal = round2(acc.subtotal + line.lineGross);
                    acc.discount = round2(acc.discount + line.discount);
                    acc.tax = round2(acc.tax + line.tax);
                    acc.total = round2(acc.total + line.total);
                    return acc;
                },
                { subtotal: 0, discount: 0, tax: 0, total: 0 },
            );
        },
    });
}
