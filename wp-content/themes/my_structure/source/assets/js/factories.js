// source/assets/js/factories.js
console.log('[factories.js] loaded');
export function registerDataFactories(Alpine) {
  Alpine.data('productCard', ({ pid, stock, availability }) => ({
    pid, stock, availability,
    get max() { return Alpine.store('shop').maxFromBits(this.pid, this.stock, this.availability) },
    get rem() {
      const m = this.max
      return m === Infinity ? Infinity : Math.max(0, m - Alpine.store('shop').inCartQty(this.pid))
    },
    add(name, price, image) {
      Alpine.store('shop').addToCart({ id: this.pid, name, price, image, stock: this.stock, availability: this.availability })
    },
    open(prodObj) { Alpine.store('shop').openProduct(prodObj) }
  }))
}
