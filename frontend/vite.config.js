import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    // Eliminar el proxy - ahora usamos URL directa
    // El proxy estaba añadiendo /api a las peticiones
  },
});