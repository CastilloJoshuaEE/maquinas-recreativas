// notificaciones-panel.ts
// FIX: usaba ReportesService (features/reportes) que no tiene getNotificaciones ni
//      marcarNotificacionLeida. Ahora usa NotificationService (@core/services/notification)
//      que sí tiene getNotifications() y markAsRead().

import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { forkJoin } from 'rxjs';
import { NotificationService } from '@core/services/notification';
import { NotificacionMaquinaService } from '@core/services/notification-maquina';
import { User } from '@core/models/user.model';

// Interfaz unificada para mostrar ambos tipos en el mismo panel
interface NotificacionUnificada {
  id: string;
  tipo: 'reporte' | 'maquina';
  mensaje: string;
  fecha: string;
  leida: boolean;
  reporteId?: string;
  tipoMaquina?: string;
  nombreMaquina?: string;
  nombreComercio?: string;
  emisorNombre?: string;
  emisorApellido?: string;
  datosOriginales: any;
}

@Component({
  selector: 'app-notificaciones-panel',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule],
  templateUrl: './notificaciones-panel.html',
  styleUrls: ['./notificaciones-panel.css']
})
export class NotificacionesPanelComponent implements OnInit, OnDestroy {
  @Input() currentUser: User | null = null;
  @Output() onClose = new EventEmitter<void>();

  // FIX: NotificationService (core) en lugar de ReportesService (features)
  private notificationService        = inject(NotificationService);
  private notificacionMaquinaService = inject(NotificacionMaquinaService);
  private router                     = inject(Router);
  private snackBar                   = inject(MatSnackBar);

  notificaciones: NotificacionUnificada[] = [];
  unreadCount = 0;
  cargando    = false;
  error       = '';

  private refreshInterval: any;

  ngOnInit(): void {
    this.cargarNotificaciones();
    this.refreshInterval = setInterval(() => this.cargarNotificaciones(), 30000);
  }

  ngOnDestroy(): void {
    if (this.refreshInterval) clearInterval(this.refreshInterval);
  }

  cargarNotificaciones(): void {
    if (!this.currentUser?.id) return;
    this.cargando = true;
    this.error    = '';

    forkJoin({
      // FIX: getNotifications (NotificationService de core) devuelve NotificacionReporte[]
      reportes: this.notificationService.getNotifications(this.currentUser.id),
      maquinas: this.notificacionMaquinaService.getNotificacionesMaquina(this.currentUser.id)
    }).subscribe({
      next: ({ reportes, maquinas }) => {
        const unificadas: NotificacionUnificada[] = [];

        // ── Notificaciones de REPORTES ─────────────────────────────────
        (reportes || []).forEach((n: any) => {
          unificadas.push({
            id:              n.ID_Notificaciones || '',
            tipo:            'reporte',
            mensaje:         n.mensaje || '',
            fecha:           n.fecha_hora || new Date().toISOString(),
            leida:           n.leida === 1 || n.leida === true,
            reporteId:       n.ID_Reporte,
            emisorNombre:    n.emisor_nombre,
            emisorApellido:  n.emisor_apellido,
            datosOriginales: n
          });
        });

        // ── Notificaciones de MÁQUINA ──────────────────────────────────
        (maquinas || []).forEach((n: any) => {
          unificadas.push({
            id:              n.ID_Notificacion || '',
            tipo:            'maquina',
            mensaje:         n.Mensaje || n.mensaje || '',
            fecha:           n.Fecha   || n.fecha   || new Date().toISOString(),
            leida:           n.Estado === 'Leido' || n.leida === true,
            tipoMaquina:     n.Tipo,
            nombreMaquina:   n.Nombre_Maquina || n.NombreMaquina,
            nombreComercio:  n.NombreComercio,
            datosOriginales: n
          });
        });

        // Ordenar por fecha descendente
        unificadas.sort((a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime());

        this.notificaciones = unificadas;
        this.unreadCount    = unificadas.filter(n => !n.leida).length;
        this.cargando       = false;
      },
      error: (err) => {
        this.error    = err.message || 'Error al cargar notificaciones';
        this.cargando = false;
      }
    });
  }

  marcarComoLeida(notificacion: NotificacionUnificada): void {
    if (notificacion.leida || !notificacion.id) return;

    // FIX: para reportes usa NotificationService.markAsRead()
    //      para máquinas usa NotificacionMaquinaService.marcarComoLeida()
    const request$ = notificacion.tipo === 'reporte'
      ? this.notificationService.markAsRead(notificacion.id)
      : this.notificacionMaquinaService.marcarComoLeida(notificacion.id);

    request$.subscribe({
      next: (success: boolean) => {
        if (success) {
          notificacion.leida = true;
          this.unreadCount   = Math.max(this.unreadCount - 1, 0);
        } else {
          this.snackBar.open('No se pudo marcar como leída', 'Cerrar', { duration: 3000 });
        }
      },
      error: () => this.snackBar.open('Error al marcar notificación', 'Cerrar', { duration: 3000 })
    });
  }

  marcarTodasLeidas(): void {
    const noLeidas = this.notificaciones.filter(n => !n.leida);
    if (noLeidas.length === 0) return;

    let completadas = 0;
    noLeidas.forEach(notificacion => {
      const request$ = notificacion.tipo === 'reporte'
        ? this.notificationService.markAsRead(notificacion.id)
        : this.notificacionMaquinaService.marcarComoLeida(notificacion.id);

      request$.subscribe({
        next: () => {
          completadas++;
          if (completadas === noLeidas.length) {
            this.notificaciones.forEach(n => n.leida = true);
            this.unreadCount = 0;
            this.snackBar.open('Todas marcadas como leídas', 'Cerrar', { duration: 3000 });
          }
        },
        error: () => { completadas++; }
      });
    });
  }

  verReporte(reporteId: string): void {
    if (reporteId) {
      this.router.navigate(['/reportes/chat', reporteId]);
      this.onClose.emit();
    }
  }

  getIcono(notificacion: NotificacionUnificada): string {
    if (notificacion.tipo === 'reporte') return 'report_problem';
    const iconos: Record<string, string> = {
      'Nuevo montaje':                     'precision_manufacturing',
      'Comprobar maquina recreativa':      'verified',
      'Reensamblar maquina recreativa':    'handyman',
      'Distribuir maquina recreativa':     'local_shipping',
      'Dar mantenimiento a maquina recreativa': 'build',
      'Maquina recreativa retirada':       'cancel',
      'Maquina recreativa reparada':       'check_circle',
    };
    return iconos[notificacion.tipoMaquina || ''] || 'videogame_asset';
  }

  getIconoClass(notificacion: NotificacionUnificada): string {
    return notificacion.tipo === 'reporte' ? 'icon-reporte' : 'icon-maquina';
  }
}