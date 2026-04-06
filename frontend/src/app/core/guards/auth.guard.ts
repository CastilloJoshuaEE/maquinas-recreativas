/**
 * @fileoverview Guard de Autenticación
 * @description Protege rutas que requieren autenticación
 * @guard AuthGuard
 */

import { Injectable, inject } from '@angular/core';
import { Router, type CanActivateFn } from '@angular/router';
import { AuthService } from '@core/services/auth.service';

/**
 * Guard que verifica si el usuario está autenticado
 * @returns true si está autenticado, redirige a login si no
 */
export const AuthGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);
  
  if (authService.isAuthenticated()) {
    return true;
  }
  
  router.navigate(['/auth/login']);
  return false;
};