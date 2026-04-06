/**
 * @fileoverview Rutas del módulo de Técnico
 * @description Configuración de rutas para los diferentes tipos de técnicos
 */

import { Routes } from '@angular/router';
import { MainLayoutComponent } from '@layouts/main-layout/main-layout';

export const TECNICO_ROUTES: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
      { path: 'ensamblador', loadComponent: () => import('./pages/dashboard-ensamblador/dashboard-ensamblador').then(m => m.DashboardEnsambladorComponent) },
      { path: 'comprobador', loadComponent: () => import('./pages/dashboard-comprobador/dashboard-comprobador').then(m => m.DashboardComprobadorComponent) },
      { path: 'mantenimiento', loadComponent: () => import('./pages/dashboard-mantenimiento/dashboard-mantenimiento').then(m => m.DashboardMantenimientoComponent) },
      { path: 'gestion-componentes', loadComponent: () => import('./pages/gestion-componentes/gestion-componentes').then(m => m.GestionComponentesComponent) },
      { path: '', redirectTo: 'ensamblador', pathMatch: 'full' }
    ]
  }
];