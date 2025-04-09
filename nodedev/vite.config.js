import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  root: './src',
  build: {
    outDir: '../../nodeassets',
    assetsDir: '',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        main: './src/main.jsx',
        lexical: './src/lexical.jsx',
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: 'chunks/[name].js',
        assetFileNames: 'assets/[name].[ext]'
      }
    }
  },
  server: {
    strictPort: true,
    port: 3000
  }
})