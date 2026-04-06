/**
 * @fileoverview Interceptor de Autenticación
 * @description Agrega el token de autenticación a las peticiones HTTP
 * @interceptor authInterceptor
 */

import { inject } from '@angular/core';
import { HttpInterceptorFn, HttpRequest, HttpHandlerFn } from '@angular/common/http';
import { AuthService } from '@core/services/auth.ts';

/**
 * Interceptor que añade el token de autorización a las peticiones
 * @param req - Petición HTTP original
 * @param next - Siguiente handler
 * @returns Petición modificada con el token
 */
export const authInterceptor: HttpInterceptorFn = (req: HttpRequest<unknown>, next: HttpHandlerFn) => {
  const authService = inject(AuthService);
  const token = authService.getToken();
  
  // No agregar token para peticiones de login, register y recuperación
  const excludeUrls = ['/login', '/register', '/recuperar-contrasena', '/recuperar-usuario', '/buscar-email'];
  const shouldExclude = excludeUrls.some(url => req.url.includes(url));
  
  if (token && !shouldExclude) {
    const authReq = req.clone({
      headers: req.headers.set('Authorization', `Bearer ${token}`)
    });
    return next(authReq);
  }
  
  return next(req);
};