// pricingStore.js (script classico, caricalo dopo Alpine e dopo cartStore)
(function () {
  function ensure() {
    Alpine.store('pricing', {
      shippingAmount() {
        const total = Alpine.store('cart').total();
        return total >= 50 ? 0 : 6.90; // <-- la tua logica
      },
      shippingLabel() {
        const a = this.shippingAmount();
        return a === 0 ? 'Spedizione gratuita' : `Spedizione € ${a.toFixed(2)}`;
      },
      remainingToFree() {
        const total = Alpine.store('cart').total();
        return Math.max(0, 50 - total); // soglia 50€
      },
      grandTotal() {
        const cart = Alpine.store('cart');
        return Number((cart.total() + this.shippingAmount()).toFixed(2));
      },
      vatLabel() { return 'IVA inclusa'; }
    });

    // Se Alpine era già partito, ri-inizializza il DOM
    if (document.documentElement.__alpine) Alpine.initTree(document.body);
  }

  if (window.Alpine) ensure();
  document.addEventListener('alpine:init', ensure, { once: true });
})();
