import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig(({ mode }) => ({
  // la root è /source
  root: path.resolve(__dirname, 'source'),

  // path pubblico degli asset generati (utile per WordPress + manifest)
  // se il tema è in /wp-content/themes/my_structure/
  base: '/wp-content/themes/my_structure/public/',

  build: {
    // output in /public (accanto a /source)
    outDir: path.resolve(__dirname, 'public'),
    emptyOutDir: true,
    manifest: true,
    assetsDir: 'assets', // cartella base per asset statici

    rollupOptions: {
      // 1 solo entry JS; lo SCSS lo importiamo da JS (vedi sotto)
      input: {
        main: path.resolve(__dirname, 'source/assets/js/main.js'),
      },
      output: {
        // js
        entryFileNames: 'assets/js/[name]-[hash].js',
        chunkFileNames: 'assets/js/[name]-[hash].js',
        // css / images / fonts
        assetFileNames: (assetInfo) => {
          const name = assetInfo.name ? assetInfo.name.toLowerCase() : '';
          if (name.endsWith('.css')) return 'assets/css/[name]-[hash][extname]';
          if (/\.(png|jpe?g|gif|svg|webp|avif)$/.test(name)) return 'assets/img/[name]-[hash][extname]';
          if (/\.(woff2?|eot|ttf|otf)$/.test(name)) return 'assets/fonts/[name]-[hash][extname]';
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },

  resolve: {
    alias: {
      // @ -> source/assets
      '@': path.resolve(__dirname, 'source/assets'),
    },
  },

  css: {
    preprocessorOptions: {
      scss: {
        // se ti serve includere variabili globali:
        // additionalData: `@use "@/scss/_variables.scss" as *;`
      },
    },
  },
}));
