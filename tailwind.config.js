/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./includes/**/*.php",
    "./admin/**/*.php",
    "./landing/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        'brand-red': '#cc0000',
        'top-bar-bg': '#f9f9f9',
        'search-bg': '#f5f5f5',
        'cart-icon-bg': '#f0f0f0',
      },
      fontFamily: {
        'sans': ['Arial', 'Helvetica', 'sans-serif'],
      }
    }
  },
  plugins: [],
}