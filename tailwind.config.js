import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                // Brand — deep navy/indigo from the BPU logo.
                brand: {
                    50: '#f5f5fb',
                    100: '#ebeaf6',
                    200: '#d2d0ec',
                    300: '#aaa6d9',
                    400: '#7d77c0',
                    500: '#564fa3',
                    600: '#3f3a86',
                    700: '#2d2a5e', // logo navy
                    800: '#252247',
                    900: '#1b1934',
                },
                // Accent — amber/gold from the BPU logo pages & circuitry.
                accent: {
                    50: '#fff8eb',
                    100: '#feecc7',
                    200: '#fdd88a',
                    300: '#fbc04d',
                    400: '#f5a623', // logo amber
                    500: '#e88c10',
                    600: '#c96c0a',
                    700: '#a14e0d',
                    800: '#834012',
                    900: '#6e3512',
                },
                // Deep navy-black for hero/announcement surfaces.
                ink: '#1b1934',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            boxShadow: {
                card: '0 1px 2px rgba(16,24,40,.04), 0 4px 16px rgba(16,24,40,.06)',
                'card-hover': '0 8px 30px rgba(16,24,40,.12)',
            },
        },
    },

    plugins: [forms],
};
