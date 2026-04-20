// notificaciones-panel.ts
import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ReportesService } from '../../services/reportes';
import { NotificacionMaquinaService } from '@core/services/notification-maquina';
import { User } from '@core/models/user.model';
import { forkJoin } from 'rxjs';

// Interfaz unificada para mostrar
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
  
  private router = inject(Router);
  private reportesService = inject(ReportesService);
  private notificacionMaquinaService = inject(NotificacionMaquinaService);
  private snackBar = inject(MatSnackBar);
  
  notificaciones: NotificacionUnificada[] = [];
  unreadCount = 0;
  cargando = false;
  error = '';
  private refreshInterval: any;
  
  ngOnInit(): void { 
    this.cargarNotificaciones(); 
    this.refreshInterval = setInterval(() => this.cargarNotificaciones(), 30000); 
  }
  
  ngOnDestroy(): void { 
    if (this.refreshInterval) clearInterval(this.refreshInterval); 
  }
  // notificaciones-panel.ts
cargarNotificaciones(): void {
  if (!this.currentUser?.id) return;
  this.cargando = true;
  this.error = '';
  
  console.log('🔍 Cargando notificaciones para usuario:', this.currentUser.id);
  
  forkJoin({
    reportes: this.reportesService.getNotificaciones(this.currentUser.id),
    maquinas: this.notificacionMaquinaService.getNotificacionesMaquina(this.currentUser.id)
  }).subscribe({
    next: ({ reportes, maquinas }) => {
      console.log('📬 Notificaciones reportes (crudas):', JSON.stringify(reportes, null, 2));
      console.log('🔧 Notificaciones máquinas (crudas):', JSON.stringify(maquinas, null, 2));
      
      const notificacionesUnificadas: NotificacionUnificada[] = [];
      
      // Procesar notificaciones de reportes
      if (reportes && reportes.length > 0) {
        reportes.forEach((n: any) => {
          console.log('  Procesando reporte:', n);
          notificacionesUnificadas.push({
            id: n.ID_Notificaciones,
            tipo: 'reporte',
            mensaje: n.mensaje || '',
            fecha: n.fecha_hora || new Date().toISOString(),
            leida: n.leida === 1 || n.leida === true,
            reporteId: n.ID_Reporte,
            emisorNombre: n.emisor_nombre,
            emisorApellido: n.emisor_apellido,
            datosOriginales: n
          });
        });
      }
      
      // Procesar notificaciones de máquina
      if (maquinas && maquinas.length > 0) {
        maquinas.forEach((n: any) => {
          console.log('  Procesando máquina:', n);
          console.log('    ID_Notificacion:', n.ID_Notificacion);
          console.log('    Mensaje:', n.Mensaje);
          console.log('    Estado:', n.Estado);
          
          notificacionesUnificadas.push({
            id: n.ID_Notificacion,
            tipo: 'maquina',
            mensaje: n.Mensaje || n.mensaje || '',
            fecha: n.Fecha || n.fecha || new Date().toISOString(),
            leida: n.Estado === 'Leido' || n.leida === true,
            tipoMaquina: n.Tipo,
            nombreMaquina: n.Nombre_Maquina || n.NombreMaquina,
            nombreComercio: n.NombreComercio,
            datosOriginales: n
          });
        });
      }
      
      console.log('✅ Notificaciones unificadas:', notificacionesUnificadas);
      
      // Ordenar por fecha
      notificacionesUnificadas.sort((a, b) => 
        new Date(b.fecha).getTime() - new Date(a.fecha).getTime()
      );
      
      this.notificaciones = notificacionesUnificadas;
      this.unreadCount = this.notificaciones.filter(n => !n.leida).length;
      this.cargando = false;
    },
    error: (err) => {
      console.error('❌ Error en forkJoin:', err);
      this.error = err.message || 'Error al cargar notificaciones';
      this.cargando = false;
    }
  });
}
marcarComoLeida(notificacion: NotificacionUnificada): void {
  console.log('📌 marcarComoLeida llamado con:', notificacion);
  console.log('  - leida actual:', notificacion.leida);
  console.log('  - id:', notificacion.id);
  console.log('  - tipo:', notificacion.tipo);
  
  if (notificacion.leida) {
    console.log('⏭️ Ya está leída, ignorando');
    return;
  }
  
  if (!notificacion.id) {
    console.error('❌ ID de notificación es undefined o vacío');
    console.error('  Datos originales:', notificacion.datosOriginales);
    this.snackBar.open('Error: No se pudo identificar la notificación', 'Cerrar', { duration: 3000 });
    return;
  }
  
  console.log('✅ Marcando notificación con ID:', notificacion.id, 'tipo:', notificacion.tipo);
  
  const request$ = notificacion.tipo === 'reporte'
    ? this.reportesService.marcarNotificacionLeida(notificacion.id)
    : this.notificacionMaquinaService.marcarComoLeida(notificacion.id);
  
  request$.subscribe({
    next: (success: boolean) => {
      console.log('📨 Respuesta de marcar como leída:', success);
      if (success) {
        notificacion.leida = true;
        this.unreadCount = Math.max(this.unreadCount - 1, 0);
        this.snackBar.open('Notificación marcada como leída', 'Cerrar', { duration: 2000 });
      } else {
        this.snackBar.open('No se pudo marcar como leída', 'Cerrar', { duration: 3000 });
      }
    },
    error: (err) => {
      console.error('❌ Error al marcar como leída:', err);
      this.snackBar.open('Error al marcar notificación', 'Cerrar', { duration: 3000 });
    }
  });
}

  marcarTodasLeidas(): void {
    // Marcar todas como leídas (solo las no leídas)
    const noLeidas = this.notificaciones.filter(n => !n.leida);
    
    if (noLeidas.length === 0) return;
    
    let completadas = 0;
    const total = noLeidas.length;
    
    noLeidas.forEach(notificacion => {
      const request$ = notificacion.tipo === 'reporte'
        ? this.reportesService.marcarNotificacionLeida(notificacion.id)
        : this.notificacionMaquinaService.marcarComoLeida(notificacion.id);
      
      request$.subscribe({
        next: () => {
          completadas++;
          if (completadas === total) {
            this.notificaciones.forEach(n => n.leida = true);
            this.unreadCount = 0;
            this.snackBar.open('Todas las notificaciones marcadas como leídas', 'Cerrar', { duration: 3000 });
          }
        },
        error: () => {
          completadas++;
        }
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
    
    const iconos: { [key: string]: string } = {
      'Mantenimiento': 'build',
      'Distribucion': 'local_shipping',
      'Recaudacion': 'attach_money',
      'Nuevo montaje': 'precision_manufacturing',
      'Comprobar maquina recreativa': 'verified',
      'Reensamblar maquina recreativa': 'handyman'
    };
    return iconos[notificacion.tipoMaquina || ''] || 'notifications';
  }
  
  getIconoClass(notificacion: NotificacionUnificada): string {
    if (notificacion.tipo === 'reporte') return 'icon-reporte';
    return 'icon-maquina';
  }
  
  // Helper para obtener mensaje formateado
  getMensajeFormateado(notificacion: NotificacionUnificada): string {
    if (notificacion.tipo === 'reporte') {
      const nombre = notificacion.emisorNombre 
        ? `${notificacion.emisorNombre} ${notificacion.emisorApellido || ''}: `
        : '';
      return nombre + notificacion.mensaje;
    }
    return notificacion.mensaje;
  }
}