// With Tailwind v4 + @tailwindcss/vite, Tailwind is handled by the Vite plugin,
// not as a PostCSS plugin. autoprefixer is still useful for browser compatibility.
export default {
    plugins: {
        autoprefixer: {},
    },
};
