import { Component, Input, Output, EventEmitter, OnInit, OnChanges, SimpleChanges, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { NotificationService } from '@core/services/notification';
import { User } from '@core/models/user.model';
import { NotificacionMaquinaService } from '@core/services/notification-maquina';

@Component({
  selector: 'app-notificaciones-list',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule],
  templateUrl: './notificaciones-list.html',
  styleUrls: ['./notificaciones-list.css']
})
export class NotificacionesListComponent implements OnInit, OnChanges {
  @Input() user: User | null = null;
  @Input() emptyMessage: string = 'No hay notificaciones...';
  @Input() currentUser: User | null = null;
  @Output() onClose = new EventEmitter<void>();
  @Output() close = new EventEmitter<void>();

  private notificationService = inject(NotificationService);
  private router = inject(Router);
private notificacionMaquinaService = inject(NotificacionMaquinaService);

  notificaciones: any[] = [];
  noLeidas: number = 0;
  mostrarNotificaciones: boolean = false;
  cargando: boolean = false;

  ngOnInit(): void {
    // Usar currentUser como fallback si user no está definido
    const activeUser = this.user ?? this.currentUser;
    if (activeUser?.id) {
      this.cargarNotificaciones();
    }
  }

  ngOnChanges(changes: SimpleChanges): void {
    if ((changes['user'] || changes['currentUser']) && !this.notificaciones.length) {
      const activeUser = this.user ?? this.currentUser;
      if (activeUser?.id) {
        this.cargarNotificaciones();
      }
    }
  }

  private getActiveUser(): User | null {
    return this.user ?? this.currentUser ?? null;
  }

cargarNotificaciones(): void {
  const activeUser = this.getActiveUser();
  if (!activeUser?.id) return;

  console.log('🔍 NotificacionesList: Cargando para usuario:', activeUser.id);
  this.cargando = true;
  
  this.notificacionMaquinaService.getNotificacionesMaquina(activeUser.id).subscribe({
    next: (notifs) => {
      console.log('📬 NotificacionesList - recibidas:', notifs);
      this.notificaciones = notifs;
      this.noLeidas = notifs.filter(n => n.Estado !== 'Leido').length;
      this.cargando = false;
    },
    error: (err) => { 
      console.error('❌ Error cargando notificaciones:', err);
      this.cargando = false; 
    }
  });
}

  toggleMostrar(): void {
    this.mostrarNotificaciones = !this.mostrarNotificaciones;
    if (this.mostrarNotificaciones && this.notificaciones.length === 0) {
      this.cargarNotificaciones();
    }
  }
marcarComoLeida(id: string): void {
  console.log('📌 NotificacionesList.marcarComoLeida - ID:', id);
  
  if (!id) {
    console.error('❌ ID es undefined o vacío');
    return;
  }
  
  this.notificacionMaquinaService.marcarComoLeida(id).subscribe({
    next: (success) => {
      console.log('📨 Respuesta marcarComoLeida:', success);
      if (success) {
        const notif = this.notificaciones.find(n => n.ID_Notificacion === id);
        if (notif && notif.Estado !== 'Leido') {
          notif.Estado = 'Leido';
          this.noLeidas = Math.max(this.noLeidas - 1, 0);
        }
      }
    },
    error: (err) => {
      console.error('❌ Error al marcar como leída:', err);
    }
  });
}
  verReporte(reporteId: string): void {
    if (!reporteId) return;
    this.router.navigate(['/reportes/chat', reporteId]);
    this.onClose.emit();
  }

  cerrar(): void {
    this.onClose.emit();
  }
}