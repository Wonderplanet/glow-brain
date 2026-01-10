import preset from '../../../../vendor/filament/filament/tailwind.config.preset'
import typography from '@tailwindcss/typography'
import daisyui from "daisyui"

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/awcodes/filament-tiptap-editor/resources/**/*.blade.php',
    ],
    plugins: [
        typography,
        daisyui,
    ],
    daisyui: {
        themes: ['fantasy'],
    },
    theme: {
        extend: {
            colors: {
                primary: {
                    500: '#3b82f6', // この値は実際に使用したい色に変更してください
                },
            },
          typography: {
            DEFAULT: {
              css: {
                maxWidth: '100%', // 最大幅を100%に設定
              }
            }
          }
        }
    },
    variants: {
        extend: {
            textColor: ['hover'],
        },
    },
}
