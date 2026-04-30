/**
 * @fileoverview Rutas del módulo de Reportes
 * @description Configuración de rutas para gestión de reportes y chat
 * @module reportes.routes
 */

import { Routes } from '@angular/router';
import { MainLayoutComponent } from '@layouts/main-layout/main-layout';

export const REPORTES_ROUTES: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
{ path: 'gestion-reportes', loadComponent: () => import('./pages/gestion-reportes/gestion-reportes').then(m => m.GestionReportesComponent) },
      { path: 'chat-view', loadComponent: () => import('./pages/chat-view/chat-view').then(m => m.ChatViewComponent) },
      { 
        path: 'chat/:reporteId', 
        loadComponent: () => import('./pages/chat-view/chat-view').then(m => m.ChatViewComponent) 
      },
      { 
        path: 'chat/:emisorId/:destinatarioId', 
        loadComponent: () => import('./pages/chat-view/chat-view').then(m => m.ChatViewComponent) 
      },
      { 
        path: '', 
        redirectTo: 'gestion', 
        pathMatch: 'full' 
      }
    ]
  }
];