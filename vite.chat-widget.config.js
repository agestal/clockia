import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        outDir: 'public/widget',
        emptyOutDir: false,
        minify: true,
        cssCodeSplit: false,
        lib: {
            entry: resolve(__dirname, 'resources/widget-chat/index.js'),
            name: 'ClockiaChat',
            formats: ['iife'],
            fileName: () => 'clockia-chat-widget.js',
        },
        rollupOptions: {
            output: {
                extend: true,
                inlineDynamicImports: true,
            },
        },
    },
});
