import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'

import path from 'path';
import {fileURLToPath} from 'url';

// Fix __dirname for ES modules
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// https://vite.dev/config/
export default defineConfig({
    plugins: [vue()],
    build: {
        outDir: 'dist', // Ensure the build files are output in the "dist" folder
        emptyOutDir: true, // Clear the dist folder before building
    },
    server: {
        host: process.env.VITE_HOST || '0.0.0.0', // Bind to all network interfaces
        port: parseInt(process.env.VITE_PORT || "5173", 10), // Match Docker's exposed port
        watch: {
            usePolling: true, // Necessary for file change detection in Docker
        },
        hmr: {
            protocol: process.env.VITE_HMR_PROTOCOL || 'ws', // WebSocket protocol
            host: process.env.VITE_HMR_HOST || undefined, // Dynamic domain support
            clientPort: parseInt(process.env.VITE_CLIENT_PORT || "80", 10), // Front-facing port (set to 443 for HTTPS)
        },
        cors: true, // Allow cross-origin requests
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'src'),
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
        },
    },
});
