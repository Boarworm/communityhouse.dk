import { defineConfig } from 'vite';
import { resolve } from 'path';
import tailwindcss from '@tailwindcss/vite'
import svgSprite from 'vite-plugin-svg-sprite';

const themeName = 'main';

export default defineConfig({
    root: '.',
    base: `/themes/${themeName}/assets/dist/`,
    publicDir: false,
    resolve: {
        alias: {
            '@modules': resolve(__dirname, '../../modules'),
        },
    },

    build: {
        cssMinify: true,
        outDir: 'assets/dist',
        assetsDir: '',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'assets/src/js/app.js')
            },
            output: {
                entryFileNames: 'js/[name].js',
                chunkFileNames: 'js/[name].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith('.css')) {
                        return 'css/[name][extname]';
                    }
                    return 'assets/[name][extname]';
                },
            },
        },
        sourcemap: true,
    },


    plugins: [
        tailwindcss(),
        svgSprite({
            symbolId: '[name]',
        }),
    ],

    server: {
        host: true,
        hmr: {
            protocol: 'ws',
        },
        cors: {
            origin: 'http://communityhouse.local:8004',
        },
        watch: {
            usePolling: false,
        },
    },
});
