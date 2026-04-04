/**
 * @fileoverview Rutas del módulo de Logística
 * @description Configuración de rutas para el panel de logística
 * @module logistica.routes
 */

import { Routes } from '@angular/router';
import { MainLayoutComponent } from '@layouts/main-layout/main-layout.component';

export const LOGISTICA_ROUTES: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
      { 
        path: 'dashboard', 
        loadComponent: () => import('./pages/dashboard-logistica/dashboard-logistica.component').then(m => m.DashboardLogisticaComponent) 
      },
      { 
        path: 'consultar-informe-distribucion', 
        loadComponent: () => import('./pages/consultar-informe-distribucion/consultar-informe-distribucion.component').then(m => m.ConsultarInformeDistribucionLogisticaComponent) 
      },
      { 
        path: '', 
        redirectTo: 'dashboard', 
        pathMatch: 'full' 
      }
    ]
  }
];