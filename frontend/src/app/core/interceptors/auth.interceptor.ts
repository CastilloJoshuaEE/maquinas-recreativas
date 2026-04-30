import { inject } from '@angular/core';
import { HttpInterceptorFn, HttpRequest, HttpHandlerFn } from '@angular/common/http';
import { AuthService } from '@core/services/auth';
export const authInterceptor: HttpInterceptorFn = (req, next) => {
    const authService = inject(AuthService);
    
    // Siempre incluir withCredentials para cookies de sesión PHP
    let cloned = req.clone({ withCredentials: true });
    
    // Si hay token, agregar header Authorization
    const token = authService.getToken();
    if (token) {
        cloned = cloned.clone({
            headers: cloned.headers.set('Authorization', `Bearer ${token}`)
        });
    }
    
    return next(cloned);
};