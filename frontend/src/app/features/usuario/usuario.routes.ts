/**
 * @fileoverview Rutas del módulo de Usuario
 * @description Configuración de rutas para perfil y gestión de usuario
 */

import { Routes } from '@angular/router';
import { MainLayoutComponent } from '@layouts/main-layout/main-layout';

export const USUARIO_ROUTES: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
      { path: 'perfil', loadComponent: () => import('./pages/perfil/perfil').then(m => m.PerfilComponent) },
      { path: 'actualizar-perfil', loadComponent: () => import('./pages/actualizar-perfil/actualizar-perfil').then(m => m.ActualizarPerfilComponent) },
      { path: '', redirectTo: 'perfil', pathMatch: 'full' }
    ]
  }
];