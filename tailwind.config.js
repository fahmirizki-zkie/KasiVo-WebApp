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
                kasivo: {
                    coral: '#E87559',
                    'coral-hover': '#D66448',
                    blush: '#FCEDEA',
                    mint: '#DDF1EC',
                    sky: '#E1F0F7',
                    text: '#252525',
                    secondary: '#777777',
                    border: '#EEEEEE',
                    success: '#43A58A',
                    warning: '#E7A34A',
                    error: '#DC6464',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'soft-sm': '0 2px 10px rgba(0, 0, 0, 0.03)',
                'soft': '0 10px 30px rgba(232, 117, 89, 0.08)',
                'soft-lg': '0 20px 40px rgba(37, 37, 37, 0.06)',
                'card': '0 8px 24px rgba(0, 0, 0, 0.04)',
            },
        },
    },

    plugins: [forms],
};
