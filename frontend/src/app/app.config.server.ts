/**
 * @fileoverview Configuración para Server Side Rendering (opcional)
 * @description Configuración específica para renderizado del lado del servidor
 */

import { mergeApplicationConfig, ApplicationConfig } from '@angular/core';
import { appConfig } from './app.config';

// Verificar si estamos en entorno server
const isServer = typeof window === 'undefined';

let serverConfig: ApplicationConfig = { providers: [] };

if (isServer) {
  try {
    // Intentar importar dinámicamente solo si estamos en server
    const { provideServerRendering } = require('@angular/platform-server');
    serverConfig = {
      providers: [provideServerRendering()]
    };
  } catch (error) {
    console.warn('SSR no disponible, ejecutando en modo cliente');
  }
}

export const config = mergeApplicationConfig(appConfig, serverConfig);