/**
 * @fileoverview Rutas del módulo de Contabilidad
 * @description Configuración de rutas para el panel de contabilidad
 * @module contabilidad.routes
 */

import { Routes } from '@angular/router';
import { MainLayoutComponent } from '@layouts/main-layout/main-layout.component';

export const CONTABILIDAD_ROUTES: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
      { 
        path: 'dashboard', 
        loadComponent: () => import('./pages/dashboard-contabilidad/dashboard-contabilidad.component').then(m => m.DashboardContabilidadComponent) 
      },
      { 
        path: 'gestion-recaudacion', 
        loadComponent: () => import('./pages/gestion-recaudacion/gestion-recaudacion.component').then(m => m.GestionRecaudacionComponent) 
      },
      { 
        path: 'registrar-recaudacion', 
        loadComponent: () => import('./pages/registrar-recaudacion/registrar-recaudacion.component').then(m => m.RegistrarRecaudacionComponent) 
      },
      { 
        path: 'consultar-recaudaciones', 
        loadComponent: () => import('./pages/consultar-recaudaciones/consultar-recaudaciones.component').then(m => m.ConsultarRecaudacionesComponent) 
      },
      { 
        path: 'actualizar-recaudacion/:uuid', 
        loadComponent: () => import('./pages/actualizar-recaudacion/actualizar-recaudacion.component').then(m => m.ActualizarRecaudacionComponent) 
      },
      { 
        path: 'levantar-informe/:idRecaudacion', 
        loadComponent: () => import('./pages/levantar-informe/levantar-informe.component').then(m => m.LevantarInformeComponent) 
      },
      { 
        path: 'ver-informe/:idRecaudacion', 
        loadComponent: () => import('./pages/ver-informe/ver-informe.component').then(m => m.VerInformeComponent) 
      },
      { 
        path: 'consultar-informe-distribucion', 
        loadComponent: () => import('./pages/consultar-informe-distribucion/consultar-informe-distribucion.component').then(m => m.ConsultarInformeDistribucionComponent) 
      },
      { 
        path: '', 
        redirectTo: 'dashboard', 
        pathMatch: 'full' 
      }
    ]
  }
];