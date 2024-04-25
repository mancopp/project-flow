/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        'pf-black': '#445E5E',
        'pf-cyan': '#9FB6C5',
        'pf-cyan-light': '#CFEAEB',
        'pf-cyan-blue': '#2081C3',
        'pf-sky-blue': '#63D2FF',
      },
    },
  },
  plugins: [],
}
