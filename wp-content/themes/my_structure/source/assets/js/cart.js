document.addEventListener('alpine:init', () => {
    Alpine.store('cartReady', false);

    Alpine.store('cart', {
        items: JSON.parse(localStorage.getItem('cart_items') || '[]'),

        add(item) {
            const found = this.items.find(i => i.id === item.id);
            if (found) {
                found.qty++;
            } else {
                this.items.push({ ...item, qty: 1 });
            }
            this.save();
        },

        remove(id) {
            this.items = this.items.filter(i => i.id !== id);
            this.save();
        },

        clear() {
            this.items = [];
            this.save();
        },

        total() {
            return this.items.reduce((sum, i) => sum + i.qty * parseFloat(i.price), 0);
        },

        save() {
            localStorage.setItem('cart_items', JSON.stringify(this.items));
        }
    });

    Alpine.store('cartReady', true);
});
