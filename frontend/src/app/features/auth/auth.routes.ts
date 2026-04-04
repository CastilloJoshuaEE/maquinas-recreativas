/**
 * @fileoverview Rutas del módulo de autenticación
 * @description Configuración de rutas para login, registro y recuperación
 */

import { Routes } from '@angular/router';
import { AuthLayoutComponent } from '@layouts/auth-layout/auth-layout.component';

export const AUTH_ROUTES: Routes = [
  {
    path: '',
    component: AuthLayoutComponent,
    children: [
      { path: 'login', loadComponent: () => import('./pages/login/login.component').then(m => m.LoginComponent) },
      { path: 'register', loadComponent: () => import('./pages/registro/registro.component').then(m => m.RegistroComponent) },
      { path: 'recuperar-contrasena', loadComponent: () => import('./pages/recuperar-contrasena/recuperar-contrasena.component').then(m => m.RecuperarContrasenaComponent) },
      { path: 'recuperar-usuario', loadComponent: () => import('./pages/recuperar-usuario/recuperar-usuario.component').then(m => m.RecuperarUsuarioComponent) },
      { path: 'actualizar-usuario', loadComponent: () => import('./pages/actualizar-usuario/actualizar-usuario.component').then(m => m.ActualizarUsuarioComponent) },
      { path: '', redirectTo: 'login', pathMatch: 'full' }
    ]
  }
];