/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Primary brand color (from the green on the map)
        primary: '#009E60',

        // Secondary brand color (flag red)
        secondary: '#E31C23',

        // Accent / highlight color (pan-African gold)
        accent: '#FFCD00',

        // Neutrals
        neutral: {
          900: '#333333', // Dark text
          100: '#F7F7F7'  // Light backgrounds
        }
      }
    }
  },
  plugins: [],
}
