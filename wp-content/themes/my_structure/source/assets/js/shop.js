// resources/js/alpine/shop.js
// Registra il componente Alpine "shop" e centralizza tutta la logica.

document.addEventListener('alpine:init', () => {
  Alpine.data('shop', () => ({
    // STATE
    modalOpen: false,
    selected: null,
    selectedImage: null,

    // HELPERS
    truncate(str, max = 35) {
      if (!str) return '';
      return String(str).length > max ? String(str).slice(0, max) + '…' : String(str);
    },

    // MODAL
    openModal(product) {
      this.modalOpen = true;
      this.selected = product;
      this.selectedImage = null;
    },
    closeModal() {
      this.modalOpen = false;
      this.selectedImage = null;
      this.selected = null;
    },

    // GALLERY
    selectImage(img) {
      this.selectedImage = img;
    },

    // CART
    addToCart(item) {
      // Normalizza per il tuo store { id, name, image, price }
      this.$store.cart.add({
        id: item.id,
        name: item.name ?? item.title,
        image: item.image ?? (item.gallery?.[0] ?? ''),
        price: Number(item.price)
      });
    },
    addSelectedToCart() {
      if (!this.selected) return;
      this.addToCart(this.selected);
    }
  }));
});
