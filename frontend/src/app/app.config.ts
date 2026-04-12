/**
 * @fileoverview Configuración principal de la aplicación Angular
 * @description Configura los proveedores, interceptores y rutas de la aplicación
 * @module app.config
 */

import { ApplicationConfig, provideZoneChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptors, withFetch } from '@angular/common/http';
import { provideAnimations } from '@angular/platform-browser/animations';
import { provideToastr } from 'ngx-toastr';
import { MAT_FORM_FIELD_DEFAULT_OPTIONS } from '@angular/material/form-field';
import { MAT_SNACK_BAR_DEFAULT_OPTIONS } from '@angular/material/snack-bar';

import { routes } from './app.routes';
import { authInterceptor } from './core/interceptors/auth.interceptor';
import { errorInterceptor } from './core/interceptors/error.interceptor';
import { APP_INITIALIZER } from '@angular/core';
import { SessionMonitorService } from './core/services/session-monitor';
/**
 * Configuración global de la aplicación
 * Incluye:
 * - Router con todas las rutas
 * - HTTP Client con interceptores
 * - Animaciones
 * - Toastr para notificaciones
 * - Configuraciones de Material Design
 */
export const appConfig: ApplicationConfig = {
  providers: [
    // Optimización de detección de cambios
    provideZoneChangeDetection({ eventCoalescing: true }),
    
    // Router
    provideRouter(routes),
    
    // HTTP Client con interceptores y fetch API
    provideHttpClient(
      withFetch(),
      withInterceptors([authInterceptor, errorInterceptor])
    ),
    
    // Animaciones
    provideAnimations(),
    
    // Toastr para notificaciones
    provideToastr({
      timeOut: 3000,
      positionClass: 'toast-top-right',
      preventDuplicates: true,
      closeButton: true,
      progressBar: true,
      newestOnTop: true
    }),
     {
      provide: APP_INITIALIZER,
      useFactory: (sessionMonitor: SessionMonitorService) => () => {
        // Inicializar el monitor de sesión
        return Promise.resolve();
      },
      deps: [SessionMonitorService],
      multi: true
    },
    // Configuración de Material Form Field
    {
      provide: MAT_FORM_FIELD_DEFAULT_OPTIONS,
      useValue: { appearance: 'outline', floatLabel: 'auto' }
    },
    
    // Configuración de Material Snackbar
    {
      provide: MAT_SNACK_BAR_DEFAULT_OPTIONS,
      useValue: { duration: 3000, horizontalPosition: 'end', verticalPosition: 'top' }
    }
  ]
};