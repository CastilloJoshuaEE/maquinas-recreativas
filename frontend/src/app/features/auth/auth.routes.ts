/**
 * @fileoverview Rutas del módulo de autenticación
 * @description Configuración de rutas para login, registro y recuperación.
 *
 * FIX: Se eliminó la ruta 'auth/chatbot' que era incorrecta (duplicaba el
 * prefijo 'auth' y no existía en el árbol de rutas), haciendo que Angular
 * cayera en el wildcard ** y redirigiera a /auth/login.
 *
 * El chatbot ahora vive como componente inline en ChatbotFloatingComponent
 * y no necesita ruta propia.
 */

import { Routes } from '@angular/router';
import { AuthLayoutComponent } from '@layouts/auth-layout/auth-layout';

export const AUTH_ROUTES: Routes = [
  {
    path: '',
    component: AuthLayoutComponent,
    children: [
      {
        path: 'login',
        loadComponent: () => import('./pages/login/login').then(m => m.LoginComponent)
      },
      {
        path: 'register',
        loadComponent: () => import('./pages/registro/registro').then(m => m.RegistroComponent)
      },
      {
        path: 'recuperar-contrasena',
        loadComponent: () => import('./pages/recuperar-contrasena/recuperar-contrasena').then(m => m.RecuperarContrasenaComponent)
      },
      {
        path: 'recuperar-usuario',
        loadComponent: () => import('./pages/recuperar-usuario/recuperar-usuario').then(m => m.RecuperarUsuarioComponent)
      },
      {
        path: 'actualizar-usuario',
        loadComponent: () => import('./pages/actualizar-usuario/actualizar-usuario').then(m => m.ActualizarUsuarioComponent)
      },
      // NOTA: el chatbot ya NO tiene ruta propia. Se muestra como widget
      // flotante desde ChatbotFloatingComponent (inline en el DOM).
      { path: '', redirectTo: 'login', pathMatch: 'full' }
    ]
  }
];