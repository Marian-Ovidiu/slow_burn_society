import { defineConfig } from 'vite';
import sass from 'sass';
import path from 'path';
export default defineConfig({
    root: 'source',
    build: {
        outDir: path.resolve(__dirname, 'public'),
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                main: path.resolve(__dirname, 'source/assets/js/main.js'),
                style: path.resolve(__dirname, 'source/assets/scss/style.scss')
            },
            output: {
                entryFileNames: 'js/[name]-[hash].js',
                chunkFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    if (/\.(css)$/.test(assetInfo.name)) {
                        return 'css/[name]-[hash][extname]';
                    }
                /*    if (/\.(ttf|woff|woff2)$/.test(assetInfo.name)) {
                        return 'fonts/[name]-[hash][extname]';
                    }*/
                    return '[ext]/[name]-[hash][extname]';
                }
            },
        }
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'source/assets')
        }
    },
    css: {
        preprocessorOptions: {
            scss: {
                implementation: sass,
            }
        }
    }
});
