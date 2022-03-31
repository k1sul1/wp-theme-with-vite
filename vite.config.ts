import { defineConfig } from "vite"
import react from "@vitejs/plugin-react"
import { resolve, basename } from "path"

const themeFolder = basename(__dirname)

// https://vitejs.dev/config/
export default defineConfig({
  server: {
    port: 8888,
    origin: "http://localhost:8888",
  },
  plugins: [react()],

  base: `/wp-content/themes/${themeFolder}/dist/`,

  build: {
    manifest: true,
    minify: false,
    target: "es2015",
    rollupOptions: {
      input: {
        client: resolve(__dirname, "src/client.tsx"),
        admin: resolve(__dirname, "src/admin.tsx"),
      },
    },
  },
})
