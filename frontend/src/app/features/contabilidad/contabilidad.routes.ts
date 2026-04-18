/**
 * @fileoverview Rutas del módulo de Contabilidad
 * @description Configuración de rutas para el panel de contabilidad
 * @module contabilidad.routes
 */

import { Routes } from '@angular/router';
import { MainLayoutComponent } from '@layouts/main-layout/main-layout';

export const CONTABILIDAD_ROUTES: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
      { 
        path: 'dashboard', 
        loadComponent: () => import('./pages/dashboard-contabilidad/dashboard-contabilidad').then(m => m.DashboardContabilidadComponent) 
      },
      { 
        path: 'gestion-recaudacion', 
        loadComponent: () => import('./pages/gestion-recaudacion/gestion-recaudacion').then(m => m.GestionRecaudacionComponent) 
      },
      { 
        path: 'registrar-recaudacion', 
        loadComponent: () => import('./pages/registrar-recaudacion/registrar-recaudacion').then(m => m.RegistrarRecaudacionComponent) 
      },
      { 
        path: 'consultar-recaudaciones', 
        loadComponent: () => import('./pages/consultar-recaudaciones/consultar-recaudaciones').then(m => m.ConsultarRecaudacionesComponent) 
      },
      { 
        path: 'actualizar-recaudacion/:uuid', 
        loadComponent: () => import('./pages/actualizar-recaudacion/actualizar-recaudacion').then(m => m.ActualizarRecaudacionComponent) 
      },
      { 
        path: 'levantar-informe/:idRecaudacion', 
        loadComponent: () => import('./pages/levantar-informe/levantar-informe').then(m => m.LevantarInformeComponent) 
      },
      { 
        path: 'ver-informe/:idRecaudacion', 
        loadComponent: () => import('./pages/ver-informe/ver-informe').then(m => m.VerInformeComponent) 
      },
      { 
 // Usar el componente compartido
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