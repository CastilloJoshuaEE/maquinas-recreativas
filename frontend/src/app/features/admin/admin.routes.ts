/**
 * @fileoverview Rutas del módulo de Administrador
 * @description Configuración de rutas para el panel de administración
 */

import { Routes } from '@angular/router';
import { MainLayoutComponent } from '@layouts/main-layout/main-layout.component';

export const ADMIN_ROUTES: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
      { path: 'dashboard', loadComponent: () => import('./pages/dashboard-admin/dashboard-admin.component').then(m => m.DashboardAdminComponent) },
      { path: 'gestion-usuarios', loadComponent: () => import('./pages/gestion-usuarios/gestion-usuarios.component').then(m => m.GestionUsuariosComponent) },
      { path: 'consultar-usuarios', loadComponent: () => import('./pages/consultar-usuarios/consultar-usuarios.component').then(m => m.ConsultarUsuariosComponent) },
      { path: 'registrar-usuario', loadComponent: () => import('./pages/registrar-usuario/registrar-usuario.component').then(m => m.RegistrarUsuarioComponent) },
      { path: 'editar-usuario/:uuid', loadComponent: () => import('./pages/editar-usuario/editar-usuario.component').then(m => m.EditarUsuarioComponent) },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  }
];