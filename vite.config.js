import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            manifest: {
                name: 'Doors Meeting App',
                short_name: 'Doors',
                description: 'Room Booking System',
                theme_color: '#10B981',
                background_color: '#ffffff',
                display: 'standalone',
                scope: '/',
                start_url: '/',
                orientation: 'any',
                icons: [
                    {
                        src: '/images/pwa-icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/images/pwa-icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                ],
            },
            workbox: {
                navigateFallback: null,
            },
            devOptions: {
                enabled: true,
                type: 'module',
            },
        }),
    ],
    server: {
        host: '127.0.0.1',
    },
});
