import prettier from 'eslint-config-prettier/flat';
import vue from 'eslint-plugin-vue';


// import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';

// export default defineConfigWithVueTs(
//     vue.configs['flat/essential'],
//     vueTsConfigs.recommended,
//     {
//         ignores: ['vendor', 'node_modules', 'public', 'bootstrap/ssr', 'tailwind.config.js', 'resources/js/components/ui/*'],
//     },
//     {
//         rules: {
//             'vue/multi-word-component-names': 'off',
//             '@typescript-eslint/no-explicit-any': 'off',
//         },
//     },
//     prettier,
// );


export default [
  {
    // Ignorar librerías externas y carpetas de build
    ignores: [
      'vendor/**',
      'node_modules/**',
      'public/**',
      'bootstrap/ssr/**',
      'tailwind.config.js',
      'resources/js/components/ui/*',
    ],

    languageOptions: {
      parserOptions: {
        ecmaVersion: 2023,
        sourceType: 'module',
      },
    },

    plugins: { vue },

    rules: {
      // Desactivar esta regla que te daba muchos falsos positivos
      'no-unused-expressions': 'off',

      // Configs de Vue
      'vue/multi-word-component-names': 'off',
    },
  },

  // Integrar Prettier al final para que sobreescriba formateo
  prettier,
];
