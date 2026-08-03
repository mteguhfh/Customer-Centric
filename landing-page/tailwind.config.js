/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./*.html'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['"Space Grotesk"', '"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        brand: {
          50: '#eef1ff',
          100: '#dfe4ff',
          200: '#c7cfff',
          300: '#a4b1ff',
          400: '#7d8dff',
          500: '#5666fb',
          600: '#3e4df0',
          700: '#323cd1',
          800: '#2b33a9',
          900: '#282e86',
        },
        ink: {
          DEFAULT: '#0a1020',
          deep: '#060a15',
          soft: '#111a33',
        },
      },
    },
  },
  plugins: [],
}
