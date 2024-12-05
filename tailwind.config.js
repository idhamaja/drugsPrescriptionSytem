import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./resources/components/**/*.vue", // Tambahkan jika menggunakan komponen
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    light: "#3b82f6",
                    DEFAULT: "#2563eb",
                    dark: "#1e40af",
                },
            },
            boxShadow: {
                "outline-primary": "0 0 0 3px rgba(37, 99, 235, 0.5)",
            },
        },
    },

    darkMode: "class", // Gunakan class-based dark mode
    plugins: [forms],
};
