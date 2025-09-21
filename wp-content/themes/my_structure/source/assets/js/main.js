// source/assets/js/main.js
import '../scss/style.scss';

import Alpine from 'alpinejs';
import './cart.js';           // <-- SOLO import side-effect, niente named export

window.Alpine = Alpine;
Alpine.start();

console.log('main.js loaded');
