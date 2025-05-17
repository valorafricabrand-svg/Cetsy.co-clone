import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        // Primary brand color (from the green on the map)
        primary: '#009E60',

        // Secondary brand color (flag red)
        secondary: '#E31C23',

        // Accent / highlight color (pan-African gold)
        accent: '#FFCD00',

        // Neutral shades
        neutral: {
          900: '#333333', // Main text, headings
          100: '#F7F7F7', // Backgrounds, borders
        },
      },
    },
  },

  plugins: [
    forms,
  ],
};
