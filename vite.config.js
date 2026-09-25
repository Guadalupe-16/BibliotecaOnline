import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    test: {
        environment: 'jsdom',
        // Los specs de tests/e2e son de Playwright; los ejecuta `npx playwright test`.
        include: ['resources/js/**/*.test.js'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'html'],
        },
    },
})