/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        bg: '#111111',
        surface: '#161616',
        card: '#1A1A1A',
        border: '#2A2A2A',
        accent: '#8B00FF',
        'accent-dark': '#3D0080',
        text: '#F9F9F9',
        muted: '#888888',
        indigo: '#6366F1',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['"Space Grotesk"', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'monospace'],
      },
    },
  },
  plugins: [],
}
