/**
 * @fileoverview Servicio de Monitoreo de Sesión
 * @description Detecta inactividad del usuario y muestra modal de sesión expirada
 * @service SessionMonitorService
 */

import { Injectable, inject, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { MatDialog } from '@angular/material/dialog';
import { fromEvent, Subscription, timer } from 'rxjs';
import { throttleTime } from 'rxjs/operators';
import { SesionExpiradaComponent } from '@shared/ui/sesion-expirada/sesion-expirada';
import { AuthService } from '@core/services/auth';
import { ToastrService } from 'ngx-toastr';
@Injectable({
  providedIn: 'root'
})
export class SessionMonitorService implements OnDestroy {
  private router = inject(Router);
  private dialog = inject(MatDialog);
  private authService = inject(AuthService);
  
  // Tiempo de inactividad en milisegundos (15 minutos)
  private readonly INACTIVITY_TIMEOUT = 15 * 60 * 1000;
  
  // Tiempo para mostrar advertencia (1 minuto antes)
  private readonly WARNING_TIMEOUT = 14 * 60 * 1000;
  
  private inactivityTimer: any = null;
  private warningTimer: any = null;
  private subscriptions: Subscription[] = [];
  private modalAbierto = false;
  
  constructor() {
    this.initInactivityDetection();
  }
  
  ngOnDestroy(): void {
    this.clearTimers();
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
  
  private initInactivityDetection(): void {
    // Detectar eventos de actividad del usuario
    const events = ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'];
    
    events.forEach(event => {
      const subscription = fromEvent(window, event)
        .pipe(throttleTime(1000))
        .subscribe(() => this.resetTimers());
      this.subscriptions.push(subscription);
    });
    
    // Iniciar el timer al cargar
    this.resetTimers();
  }
  
  private resetTimers(): void {
    this.clearTimers();
    
    // Solo iniciar timers si el usuario está autenticado
    if (this.authService.isAuthenticated() && !this.router.url.includes('/auth/login')) {
      this.inactivityTimer = setTimeout(() => this.handleInactivity(), this.INACTIVITY_TIMEOUT);
      this.warningTimer = setTimeout(() => this.showWarning(), this.WARNING_TIMEOUT);
    }
  }
  
  private clearTimers(): void {
    if (this.inactivityTimer) {
      clearTimeout(this.inactivityTimer);
      this.inactivityTimer = null;
    }
    if (this.warningTimer) {
      clearTimeout(this.warningTimer);
      this.warningTimer = null;
    }
  }
  
  private showWarning(): void {
    if (this.modalAbierto) return;
    
    // Mostrar advertencia de que la sesión está por expirar
    const toastr = inject(ToastrService);
    toastr.warning(
      'Su sesión expirará en 1 minuto por inactividad.',
      'Sesión por expirar',
      { timeOut: 5000, closeButton: true }
    );
  }
  
  private handleInactivity(): void {
    if (this.modalAbierto) return;
    
    this.modalAbierto = true;
    
    // Limpiar sesión local
    this.authService.clearSession();
    
    // Abrir modal de sesión expirada
    const dialogRef = this.dialog.open(SesionExpiradaComponent, {
      width: '400px',
      disableClose: true,
      backdropClass: 'sesion-expirada-backdrop',
      data: {
        mensaje: 'Su sesión ha expirado por inactividad.',
        tiempoRestante: Math.floor(this.INACTIVITY_TIMEOUT / 1000)
      }
    });
    
    dialogRef.afterClosed().subscribe(() => {
      this.modalAbierto = false;
      this.authService.clearSession();
      this.router.navigate(['/auth/login']);
    });
  }
  
  /**
   * Reinicia manualmente el monitor (útil después de acciones importantes)
   */
  reset(): void {
    this.resetTimers();
  }
  
  /**
   * Detiene el monitoreo (útil al cerrar sesión)
   */
  stop(): void {
    this.clearTimers();
  }
}