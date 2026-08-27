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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eef5fb',
                    100: '#d6e6f2',
                    200: '#aecde4',
                    300: '#7eb0d2',
                    400: '#4a8cbb',
                    500: '#1f6aa5',
                    600: '#185889',
                    700: '#134570',
                    800: '#0f385c',
                    900: '#0b2c49',
                    950: '#061a2e',
                },
            },
        },
    },

    plugins: [forms],
};
