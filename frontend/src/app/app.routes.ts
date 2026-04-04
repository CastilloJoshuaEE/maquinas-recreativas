/**
 * @fileoverview Configuración de rutas principales de la aplicación
 * @description Define todas las rutas y sus respectivos guards
 * @module app.routes
 */

import { Routes } from '@angular/router';
import { AuthGuard } from './core/guards/auth.guard';
import { RoleGuard } from './core/guards/role.guard';

/**
 * Rutas principales de la aplicación
 * Utiliza lazy loading para optimizar la carga inicial
 */
export const routes: Routes = [
  // Redirección por defecto
  { path: '', redirectTo: '/auth/login', pathMatch: 'full' },
  
  // Módulo de autenticación (sin lazy loading por ser página inicial)
  {
    path: 'auth',
    loadChildren: () => import('./features/auth/auth.routes').then(m => m.AUTH_ROUTES)
  },
  
  // Módulo de administrador
  {
    path: 'admin',
    loadChildren: () => import('./features/admin/admin.routes').then(m => m.ADMIN_ROUTES),
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['Administrador'] }
  },
  
  // Módulo de contabilidad
  {
    path: 'contabilidad',
    loadChildren: () => import('./features/contabilidad/contabilidad.routes').then(m => m.CONTABILIDAD_ROUTES),
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['Contabilidad'] }
  },
  
  // Módulo de logística
  {
    path: 'logistica',
    loadChildren: () => import('./features/logistica/logistica.routes').then(m => m.LOGISTICA_ROUTES),
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['Logistica'] }
  },
  
  // Módulo de técnicos
  {
    path: 'tecnico',
    loadChildren: () => import('./features/tecnico/tecnico.routes').then(m => m.TECNICO_ROUTES),
    canActivate: [AuthGuard, RoleGuard],
    data: { roles: ['Tecnico'] }
  },
  
  // Módulo de reportes
  {
    path: 'reportes',
    loadChildren: () => import('./features/reportes/reportes.routes').then(m => m.REPORTES_ROUTES),
    canActivate: [AuthGuard]
  },
  
  // Módulo de usuario
  {
    path: 'usuario',
    loadChildren: () => import('./features/usuario/usuario.routes').then(m => m.USUARIO_ROUTES),
    canActivate: [AuthGuard]
  },
  
  // Páginas públicas
  {
    path: 'informacion',
    loadComponent: () => import('./features/pages/informacion/informacion.component').then(m => m.InformacionComponent)
  },
  {
    path: 'no-autorizado',
    loadComponent: () => import('./features/pages/no-autorizado/no-autorizado.component').then(m => m.NoAutorizadoComponent)
  },
  {
    path: 'acceso-restringido',
    loadComponent: () => import('./features/pages/acceso-restringido/acceso-restringido.component').then(m => m.AccesoRestringidoComponent)
  },
  
  // Ruta comodín - redirige a login
  { path: '**', redirectTo: '/auth/login' }
];