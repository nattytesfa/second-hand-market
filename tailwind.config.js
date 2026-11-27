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
                ink: '#14201c',
                forest: {
                    DEFAULT: '#0f3d34',
                    deep: '#0a2a24',
                    soft: '#1f6b58',
                },
                mist: '#e8efec',
                fog: '#f3f7f5',
                saffron: {
                    DEFAULT: '#c9920a',
                    bright: '#e0a912',
                },
                stone: {
                    DEFAULT: '#5c6b65',
                    light: '#8a9791',
                },
            },
            fontFamily: {
                sans: ['DM Sans', 'Noto Sans Ethiopic', ...defaultTheme.fontFamily.sans],
                display: ['Syne', 'Noto Sans Ethiopic', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                panel: '0 18px 50px -28px rgba(15, 61, 52, 0.35)',
                soft: '0 8px 24px -12px rgba(20, 32, 28, 0.18)',
            },
            borderRadius: {
                panel: '1.25rem',
            },
        },
    },

    plugins: [forms],
};
