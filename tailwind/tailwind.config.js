const path = require('path');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        path.join(__dirname, '../app/Views/**/*.php'),
        path.join(__dirname, '../public/js/*.js'),
    ],
    theme: {
        extend: {
            colors: {
                'galgo': {
                    red:  '#CC0000',
                    gold: '#F5A000',
                    dark: '#1A1A1A',
                },
            },
            fontFamily: {
                sans:    ['Inter', 'system-ui', 'sans-serif'],
                display: ['"Playfair Display"', 'Georgia', 'serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/line-clamp'),
    ],
};
