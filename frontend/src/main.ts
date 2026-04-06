/**
 * @fileoverview Punto de entrada principal de la aplicación Angular
 * @description Configura e inicializa la aplicación con todos los módulos necesarios
 * @module main
 */

import { bootstrapApplication } from '@angular/platform-browser';
import { appConfig } from './app/app.config';
import { AppComponent } from './app/app';

/**
 * Inicializa la aplicación Angular con la configuración proporcionada
 * Maneja errores durante el bootstraping
 */
bootstrapApplication(AppComponent, appConfig).catch((err) =>
  console.error('Error al iniciar la aplicación:', err)
);