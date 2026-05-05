import { defineConfig } from 'vite';
import statamic from '@statamic/cms/vite-plugin';

export default defineConfig({
    plugins: [statamic()],
    build: {
        outDir: 'dist',
        emptyOutDir: false,
        lib: {
            entry: 'resources/js/app.js',
            name: 'PopularAddon',
            formats: ['iife'],
            fileName: () => 'js/app.js',
        },
    },
});
