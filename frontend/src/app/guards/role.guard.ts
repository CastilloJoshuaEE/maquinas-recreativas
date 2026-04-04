/**
 * @fileoverview Guard de Roles
 * @description Protege rutas basadas en el rol del usuario
 * @guard RoleGuard
 */

import { Injectable, inject } from '@angular/core';
import { Router, type CanActivateFn } from '@angular/router';
import { AuthService } from '@core/services/auth';

/**
 * Guard que verifica si el usuario tiene el rol permitido
 * @param route - Ruta a proteger
 * @returns true si tiene permiso, redirige a no-autorizado si no
 */
export const RoleGuard: CanActivateFn = (route) => {
  const authService = inject(AuthService);
  const router = inject(Router);
  
  const allowedRoles = route.data?.['roles'] as string[];
  
  if (!allowedRoles || allowedRoles.length === 0) {
    return true;
  }
  
  if (authService.hasRole(allowedRoles)) {
    return true;
  }
  
  router.navigate(['/no-autorizado']);
  return false;
};