// main.ts
import 'zone.js';  // Esto debe estar al inicio
import { bootstrapApplication } from '@angular/platform-browser';
import { appConfig } from './app/app.config';
import { AppComponent } from './app/app';
bootstrapApplication(AppComponent, appConfig).catch((err) => {
  console.error('Error al iniciar la aplicación:', err);
});