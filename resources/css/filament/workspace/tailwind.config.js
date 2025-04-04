import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/diogogpinto/filament-auth-ui-enhancer/resources/**/*.blade.php',
        './vendor/joaopaulolndev/filament-edit-profile/resources/**/*.blade.php',
        './resources/css/filament/workspace/theme.css',
        './resources/css/filament/workspace/filament.css',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#009EF7', // Main button color
                secondary: '#053071', // Sidebar background
                accent: '#53E1CD', // Highlight color
                success: '#C5FF97', // Success notifications
                background: '#F5FBF0', // Light background color
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
