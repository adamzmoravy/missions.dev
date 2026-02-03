import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  server: {
    fs: {
      allow: ['../node_modules/material-symbols', '.'],
    }
  },
  build: {
    outDir: '../public_html'
  },
  plugins: [vue({
      template: {
        compilerOptions: {
          isCustomElement: (tag) => ['add-to-calendar-button'].includes(tag),
        }
      }
    })]
})
