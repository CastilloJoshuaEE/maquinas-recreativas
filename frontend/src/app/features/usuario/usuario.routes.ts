/**
 * @fileoverview Rutas del módulo de Usuario
 * @description Configuración de rutas para perfil y gestión de usuario
 * @module usuario.routes
 */

import { Routes } from '@angular/router';
import { MainLayoutComponent } from '@layouts/main-layout/main-layout.component';

export const USUARIO_ROUTES: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
      { 
        path: 'perfil', 
        loadComponent: () => import('./pages/perfil/perfil.component').then(m => m.PerfilComponent) 
      },
      { 
        path: 'actualizar-perfil', 
        loadComponent: () => import('./pages/actualizar-perfil/actualizar-perfil.component').then(m => m.ActualizarPerfilComponent) 
      },
      { 
        path: '', 
        redirectTo: 'perfil', 
        pathMatch: 'full' 
      }
    ]
  }
];