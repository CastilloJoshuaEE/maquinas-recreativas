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

export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);
  const toastr = inject(ToastrService);
  
  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      let errorMessage = 'Ocurrió un error inesperado';
      
      if (error.error instanceof ErrorEvent) {
        errorMessage = `Error: ${error.error.message}`;
      } else {
        switch (error.status) {
          case 0: errorMessage = 'No se pudo conectar con el servidor. Verifique su conexión.'; break;
          case 401: errorMessage = 'Su sesión ha expirado. Por favor inicie sesión nuevamente.'; router.navigate(['/auth/login']); break;
          case 403: errorMessage = 'No tiene permisos para realizar esta acción.'; break;
          case 404: errorMessage = 'El recurso solicitado no existe.'; break;
          case 422: errorMessage = error.error?.message || 'Error de validación.'; break;
          case 500: errorMessage = 'Error interno del servidor. Por favor intente más tarde.'; break;
          default: errorMessage = error.error?.message || `Error ${error.status}: ${error.statusText}`;
        }
      }
      
      toastr.error(errorMessage, 'Error');
      console.error('HTTP Error:', error);
      return throwError(() => error);
    })
  );
};