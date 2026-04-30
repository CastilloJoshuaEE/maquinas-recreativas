/**
 * @fileoverview Interceptor de Errores
 * @description Maneja errores globales de las peticiones HTTP
 * @interceptor errorInterceptor
 */

import { inject } from '@angular/core';
import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../services/auth';
import { MatDialog } from '@angular/material/dialog';
import { SesionExpiradaComponent } from '@shared/ui/sesion-expirada/sesion-expirada';
export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);
  const toastr = inject(ToastrService);
  const authService = inject(AuthService);
  const dialog = inject(MatDialog);
  
  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      let errorMessage = 'Ocurrió un error inesperado';
      
      // Verificar si error.error existe y tiene estructura válida
      if (error.error) {
        if (error.error instanceof ErrorEvent) {
          errorMessage = `Error: ${error.error.message}`;
        } else if (typeof error.error === 'object') {
          // Extraer mensaje del backend
          errorMessage = error.error.message || error.error.error || errorMessage;
        } else if (typeof error.error === 'string') {
          errorMessage = error.error;
        }
      } else if (error.message) {
        errorMessage = error.message;
      }
      
      // Mapear códigos de estado
      switch (error.status) {
        case 0:
          errorMessage = 'No se pudo conectar con el servidor. Verifique su conexión.';
          break;
        case 401:
          // Limpiar sesión local
          const dialogsOpen = document.querySelectorAll('.sesion-expirada-backdrop').length;
          if (dialogsOpen === 0) {
            authService.clearSession();
            const dialogRef = dialog.open(SesionExpiradaComponent, {
              width: '400px',
              disableClose: true,
              backdropClass: 'sesion-expirada-backdrop'
            });
            dialogRef.afterClosed().subscribe(() => {
              router.navigate(['/auth/login']);
            });
          }
          return throwError(() => error);
        case 403:
          errorMessage = 'No tiene permisos para realizar esta acción.';
          break;
        case 404:
          errorMessage = 'El recurso solicitado no existe.';
          break;
        case 422:
          errorMessage = error.error?.message || 'Error de validación.';
          break;
        case 500:
          errorMessage = 'Error interno del servidor. Por favor intente más tarde.';
          break;
      }
      
      // Solo mostrar toastr si no es error 401 (ya manejado)
      if (error.status !== 401) {
        toastr.error(errorMessage, 'Error');
      }
      
      console.error('HTTP Error:', error);
      return throwError(() => error);
    })
  );
};