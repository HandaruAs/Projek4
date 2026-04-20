import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css','resources/css/login.css','resources/css/forgot-password.css','resources/css/register-admin.css','resources/css/register-user.css','resources/css/reset-password.css','resources/css/verify-otp.css','resources/css/auth-base.css','resources/css/landing.css','resources/js/app.js','resources/js/landing.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
