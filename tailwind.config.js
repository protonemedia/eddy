/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./app/Http/Controllers/**/*.php",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/protonemedia/laravel-splade/lib/**/*.vue",
        "./vendor/protonemedia/laravel-splade/resources/views/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Rubik", "ui-sans-serif", "system-ui", "-apple-system", "BlinkMacSystemFont", "Segoe UI", "Roboto", "Helvetica Neue", "Arial", "Noto Sans", "sans-serif", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"],
            },

            colors: {
                gray: {
                    50: "#FCFCFD",
                    100: "#F6F7F9",
                    200: "#DEE2E8",
                    300: "#BEC5D0",
                    400: "#97A3B4",
                    500: "#64748B",
                    600: "#58667A",
                    700: "#4D596B",
                    800: "#3C4553",
                    900: "#2B323B",
                    950: "#15191E"
                },

                green: {
                    '50': '#f1fcf8',
                    '100': '#d0f7ec',
                    '200': '#a1eeda',
                    '300': '#56dabc',
                    '400': '#3bc6ab',
                    '500': '#22aa91',
                    '600': '#188977',
                    '700': '#186d61',
                    '800': '#17584e',
                    '900': '#184943',
                    '950': '#082b29',
                },

                indigo: {
                    '50': '#f2f9fd',
                    '100': '#e5f2f9',
                    '200': '#c4e5f3',
                    '300': '#73c2e2',
                    '400': '#56b6da',
                    '500': '#309dc7',
                    '600': '#217fa8',
                    '700': '#1c6688',
                    '800': '#1b5671',
                    '900': '#1b485f',
                    '950': '#122f3f',
                },

                red: {
                    '50': '#fdf3f3',
                    '100': '#fbe8e8',
                    '200': '#f7d4d6',
                    '300': '#f0b1b5',
                    '400': '#e8848e',
                    '500': '#da5666',
                    '600': '#c53950',
                    '700': '#a62a42',
                    '800': '#8b263d',
                    '900': '#782339',
                    '950': '#420f1a',
                }
            }
        },


    },

    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
};