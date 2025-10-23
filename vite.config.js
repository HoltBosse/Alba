import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'src/corecontrollers/js/dist',
    lib: {
      entry: {
        '@tiptap/core': 'srcjs/tiptap-core-entry.js',
        '@tiptap/starter-kit': 'srcjs/tiptap-starter-kit-entry.js',
        '@tiptap/extensions': 'srcjs/tiptap-extensions-entry.js',
        '@tiptap/extension-superscript': 'srcjs/tiptap-extension-superscript-entry.js',
        '@tiptap/extension-subscript': 'srcjs/tiptap-extension-subscript-entry.js',
        '@tiptap/extension-link': 'srcjs/tiptap-extension-link-entry.js',
        '@tiptap/extension-youtube': 'srcjs/tiptap-extension-youtube-entry.js',
        '@tiptap/extension-image': 'srcjs/tiptap-extension-image-entry.js',
        '@tiptap/extension-text-align': 'srcjs/tiptap-extension-text-align-entry.js',
        '@tiptap/extension-paragraph': 'srcjs/tiptap-extension-paragraph-entry.js',
        '@tiptap/extension-heading': 'srcjs/tiptap-extension-heading-entry.js',
        '@tiptap/extension-bubble-menu': 'srcjs/tiptap-extension-bubble-menu-entry.js',
        '@tiptap/extension-details': 'srcjs/tiptap-extension-details-entry.js',
        '@tiptap/extension-file-handler': 'srcjs/tiptap-extension-file-handler-entry.js',
        '@tiptap/pm': 'srcjs/tiptap-pm-entry.js'
      },
      formats: ['es'],
      fileName: (format, entryName) => `${entryName}.js`
    },
    rollupOptions: {
      external: [],
    },
    minify: 'terser',
  },
});