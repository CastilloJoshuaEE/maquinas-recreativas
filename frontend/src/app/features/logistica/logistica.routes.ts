/**
 * @fileoverview Rutas del módulo de Logística
 * @description Configuración de rutas para el panel de logística
 * @module logistica.routes
 */

import { Routes } from '@angular/router';
import { MainLayoutComponent } from '@layouts/main-layout/main-layout';

export const LOGISTICA_ROUTES: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
      { 
        path: 'dashboard', 
        loadComponent: () => import('./pages/dashboard-logistica/dashboard-logistica').then(m => m.DashboardLogisticaComponent) 
      },
      { 
        path: 'consultar-informe-distribucion',
    loadComponent: () => import('@shared/ui/consultar-informe-distribucion/consultar-informe-distribucion').then(m => m.ConsultarInformeDistribucionComponent)
  },
      { 
        path: '', 
        redirectTo: 'dashboard', 
        pathMatch: 'full' 
      }
    ]
  }
];