/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/views/**/*.blade.php",
    "./**/*.php",        
    "./resources/views/components/**/*.blade.php",
    "./resources/assets/js/**/*.js",       // se i tuoi JS stanno qui
    "./source/assets/js/**/*.js",          // (solo se esiste davvero questa cartella)
    "./**/*.php"                           // TUTTI i template del tema WP
  ],
  theme: {
    extend: {
      fontFamily: {
        // usa un alias unico per il body
        sans: ['Nunito Sans', 'Nunito', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        // se vuoi conservare un alias "bold"
        nunitoBold: ['Nunito', 'sans-serif']
      },
      colors: {
        'custom-green': '#84CE59',
        'custom-dark-green': '#45752c',
        'custom-light-green': '#E8FCCF',
      },
      animation: {
        blink: 'blink 1s step-end infinite',
        slideInRight: 'slideInRight 0.5s ease-out forwards',
        fadeInUp: 'fadeInUp 0.6s ease-out forwards',
        fadeIn: 'fadeIn 0.6s ease-out forwards'
      },
      keyframes: {
        blink: {
          '0%, 100%': { opacity: 1 },
          '50%': { opacity: 0 }
        },
        slideInRight: {
          '0%': { opacity: 0, transform: 'translateX(20px)' },
          '100%': { opacity: 1, transform: 'translateX(0)' }
        },
        fadeInUp: {
          '0%': { opacity: 0, transform: 'translateY(20px)' },
          '100%': { opacity: 1, transform: 'translateY(0)' }
        },
        fadeIn: {
          '0%': { opacity: 0 },
          '100%': { opacity: 1 }
        }
      }
    },
  },
  // opzionale: se generi classi dinamiche via stringhe
  safelist: [
    // esempi: badge di stato, ecc.
    { pattern: /bg-(red|green|gray|blue)-(100|300|600|800)/ },
    { pattern: /text-(red|green|gray|blue)-(600|800)/ }
  ],
  plugins: [
    require('@tailwindcss/forms'),
    require('tailwind-scrollbar')({ nocompatible: true })
  ],
}
