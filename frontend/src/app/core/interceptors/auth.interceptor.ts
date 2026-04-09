import { inject } from '@angular/core';
import { HttpInterceptorFn, HttpRequest, HttpHandlerFn } from '@angular/common/http';
import { AuthService } from '@core/services/auth';

export const authInterceptor: HttpInterceptorFn = (req: HttpRequest<unknown>, next: HttpHandlerFn) => {
  const authService = inject(AuthService);
  const token = authService.getToken();

  const excludeUrls = ['/login', '/register', '/recuperar-contrasena', '/recuperar-usuario', '/buscar-email'];
  const shouldExclude = excludeUrls.some(url => req.url.includes(url));

  // Siempre clonar con withCredentials:true para enviar la cookie de sesión PHP
  let cloned = req.clone({ withCredentials: true });

  if (token && !shouldExclude) {
    cloned = cloned.clone({
      headers: cloned.headers.set('Authorization', `Bearer ${token}`)
    });
  }

  return next(cloned);
};