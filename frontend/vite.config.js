import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// When running in Docker/OrbStack (see /docker-compose.yml), the Vite dev server proxies
// /api to the PHP container so the browser talks to a single origin (the backend sends no CORS headers).
const apiProxyTarget = process.env.API_PROXY_TARGET

// https://vitejs.dev/config/
export default defineConfig({
  server: {
    fs: {
      allow: ['../node_modules/material-symbols', '.'],
    },
    ...(apiProxyTarget ? {
      proxy: {
        // changeOrigin stays false so PHP sees Host: localhost:5173 and builds correct links.
        '/api': { target: apiProxyTarget, changeOrigin: false },
      },
    } : {}),
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
