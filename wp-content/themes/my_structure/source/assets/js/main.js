// source/assets/js/main.js
import '../scss/style.scss';

import Alpine from 'alpinejs';
import { setupCartStore } from './cart.js';


// esponi Alpine per debug
window.Alpine = Alpine;

// Inizializza Pinia + bridge Alpine (crea Alpine.store('cart', ...) una volta sola)
setupCartStore();

// Avvia Alpine dopo il bridge
Alpine.start();

console.log('main.js loaded');
