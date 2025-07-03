import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    // theme: {
    //     extend: {
    //         fontFamily: {
    //             sans: ['Font1', 'sans-serif'], // Set 'Font1' as the default sans-serif font
    //         },
    //     },
    // },
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor//awcodes/overlook/resources/**/*.blade.php',
    ],
}
