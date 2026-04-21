import { Component, OnInit, OnDestroy, ElementRef, ViewChild, inject, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatDialog, MatDialogModule, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { ReportesService } from '../../services/reportes';
import { ReportService } from '@core/services/report';
import { AuthService } from '@core/services/auth';
import { User } from '@core/models/user.model';
import { Reporte, Comentario, EditarComentarioData } from '@core/models/reporte.model';

// Modal para editar comentario
@Component({
  selector: 'app-editar-comentario-modal',
  standalone: true,
  imports: [CommonModule, MatDialogModule, MatButtonModule, MatFormFieldModule, MatInputModule, FormsModule],
  template: `
    <h2 mat-dialog-title>✏️ Editar mensaje</h2>
    <mat-dialog-content>
      <mat-form-field appearance="outline" class="full-width">
        <mat-label>Mensaje</mat-label>
        <textarea matInput [(ngModel)]="comentarioEditado" rows="4" placeholder="Escribe tu mensaje..."></textarea>
      </mat-form-field>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="cancelar()">Cancelar</button>
      <button mat-raised-button color="primary" (click)="guardar()" [disabled]="!comentarioEditado.trim()">
        Guardar cambios
      </button>
    </mat-dialog-actions>
  `,
  styles: ['.full-width { width: 100%; min-width: 400px; } textarea { resize: none; }']
})
export class EditarComentarioModalComponent {
  comentarioEditado: string;
  constructor(
    public dialogRef: MatDialogRef<EditarComentarioModalComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { comentarioActual: string }
  ) {
    this.comentarioEditado = data.comentarioActual;
  }
  cancelar(): void { this.dialogRef.close(null); }
  guardar(): void { this.dialogRef.close(this.comentarioEditado); }
}

// Modal de confirmación para eliminar
@Component({
  selector: 'app-eliminar-comentario-modal',
  standalone: true,
  imports: [CommonModule, MatDialogModule, MatButtonModule],
  template: `
    <h2 mat-dialog-title>🗑️ Eliminar mensaje</h2>
    <mat-dialog-content>
      <p>¿Estás seguro de que deseas eliminar este mensaje?</p>
      <p class="warning">Esta acción no se puede deshacer.</p>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="cancelar()">Cancelar</button>
      <button mat-raised-button color="warn" (click)="confirmar()">Eliminar</button>
    </mat-dialog-actions>
  `,
  styles: ['.warning { color: #e74c3c; font-size: 12px; margin-top: 8px; }']
})
export class EliminarComentarioModalComponent {
  constructor(public dialogRef: MatDialogRef<EliminarComentarioModalComponent>) {}
  cancelar(): void { this.dialogRef.close(false); }
  confirmar(): void { this.dialogRef.close(true); }
}

@Component({
  selector: 'app-chat-view',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, 
            MatFormFieldModule, MatInputModule, MatSelectModule, MatProgressSpinnerModule, 
            AdminHeaderComponent, MatDialogModule],
  templateUrl: './chat-view.html',
  styleUrls: ['./chat-view.css']
})
export class ChatViewComponent implements OnInit, OnDestroy {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private reportesService = inject(ReportesService);
  private reportService = inject(ReportService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  private dialog = inject(MatDialog);
  
  @ViewChild('scrollAnchor') scrollAnchor!: ElementRef;
  
  currentUserId = '';
  currentUser: User | null = null;
  usuarios: User[] = [];
  usuariosFiltrados: User[] = [];
  searchTerm = '';
  usuarioSeleccionado: User | null = null;
  cargandoUsuarios = false;
  reportes: Reporte[] = [];
  reporteSeleccionadoId: string | null = null;
  comentarios: Comentario[] = [];
  nuevoMensaje = '';
  cargandoMensajes = false;
  enviando = false;
  mensajesNoLeidos: { [key: string]: number } = {};
  private refreshInterval: any;
  
  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.currentUserId = this.currentUser?.id || '';
    if (this.currentUserId) {
      this.cargarUsuarios();
      this.inicializarDesdeParams();
    }
    this.refreshInterval = setInterval(() => {
      if (this.reporteSeleccionadoId && this.reporteSeleccionadoId !== 'nuevo') this.cargarComentarios();
    }, 5000);
  }
  
  ngOnDestroy(): void { if (this.refreshInterval) clearInterval(this.refreshInterval); }
  
  private inicializarDesdeParams(): void {
    const reporteId = this.route.snapshot.params['reporteId'];
    const emisorId = this.route.snapshot.params['emisorId'];
    const destinatarioId = this.route.snapshot.params['destinatarioId'];
    if (reporteId) this.cargarReportePorId(reporteId);
    else if (emisorId && destinatarioId) this.cargarUsuarioPorId(emisorId === this.currentUserId ? destinatarioId : emisorId);
  }
  
  private cargarReportePorId(reporteId: string): void {
    this.reportesService.getReportesByUser(this.currentUserId).subscribe({
      next: (reportes) => {
        const reporte = reportes.find(r => r.ID_Reporte === reporteId);
        if (reporte) {
          const otroId = reporte.ID_Usuario_Emisor === this.currentUserId ? reporte.ID_Usuario_Destinatario : reporte.ID_Usuario_Emisor;
          this.cargarUsuarioPorId(otroId);
          this.reporteSeleccionadoId = reporteId;
        }
      }
    });
  }
  
  private cargarUsuarioPorId(usuarioId: string | undefined): void {
    if (!usuarioId) {
      console.warn('No se proporcionó ID de usuario');
      return;
    }
    
    this.reportesService.getUsuariosChat(this.currentUserId).subscribe({
      next: (usuarios) => {
        const usuario = usuarios.find(u => u.id === usuarioId);
        if (usuario) { 
          this.usuarioSeleccionado = usuario; 
          this.cargarReportes(); 
        }
      }
    });
  }
  
  cargarUsuarios(): void {
    this.cargandoUsuarios = true;
    this.reportesService.getUsuariosChat(this.currentUserId).subscribe({
      next: (usuarios) => {
        this.usuarios = usuarios.filter(u => u.id !== this.currentUserId);
        this.usuariosFiltrados = [...this.usuarios];
        this.cargandoUsuarios = false;
      },
      error: () => { this.cargandoUsuarios = false; this.snackBar.open('Error al cargar usuarios', 'Cerrar', { duration: 3000 }); }
    });
  }
  
  filtrarUsuarios(): void {
    if (!this.searchTerm.trim()) this.usuariosFiltrados = [...this.usuarios];
    else {
      const term = this.searchTerm.toLowerCase();
      this.usuariosFiltrados = this.usuarios.filter(u => u.nombre.toLowerCase().includes(term) || u.apellido.toLowerCase().includes(term) || u.email.toLowerCase().includes(term));
    }
  }
  
  seleccionarUsuario(usuario: User): void {
    this.usuarioSeleccionado = usuario;
    this.reporteSeleccionadoId = null;
    this.comentarios = [];
    this.cargarReportes();
  }
  
  getReporteId(reporte: Reporte): string {
    return (reporte as any).id || reporte.ID_Reporte || '';
  }
  
  cargarReportes(): void {
    if (!this.usuarioSeleccionado) return;
    this.reportesService.getChat(this.currentUserId, this.usuarioSeleccionado.id).subscribe({
      next: (data) => {
        this.reportes = data.reportes;
        console.log('📊 Reportes del chat:', this.reportes);
        
        if (this.reportes.length > 0) { 
          const primerId = this.getReporteId(this.reportes[0]);
          this.reporteSeleccionadoId = primerId; 
          this.cargarComentarios(); 
        } else {
          this.reporteSeleccionadoId = null;
        }
      },
      error: () => { 
        this.snackBar.open('Error al cargar reportes', 'Cerrar', { duration: 3000 }); 
      }
    });
  }
  cargarComentarios(): void {
    if (!this.reporteSeleccionadoId || this.reporteSeleccionadoId === 'nuevo') return;
    this.cargandoMensajes = true;
    this.reportesService.getComentarios(this.reporteSeleccionadoId).subscribe({
        next: (comentarios) => {
            // Filtrar comentarios que no estén eliminados (por si acaso)
            this.comentarios = comentarios.filter(c => !c.eliminado);
            this.cargandoMensajes = false;
            setTimeout(() => this.scrollToBottom(), 100);
            if (this.usuarioSeleccionado) this.mensajesNoLeidos[this.usuarioSeleccionado.id] = 0;
        },
        error: () => { this.cargandoMensajes = false; }
    });
}
  
  crearNuevoReporte(): void {
    if (!this.usuarioSeleccionado) return;
    this.reportesService.crearReporte({
      ID_Usuario_Emisor: this.currentUserId,
      ID_Usuario_Destinatario: this.usuarioSeleccionado.id,
      descripcion: `Chat con ${this.usuarioSeleccionado.nombre} ${this.usuarioSeleccionado.apellido}`
    }).subscribe({
      next: (reporteId) => {
        if (reporteId) { this.reporteSeleccionadoId = reporteId; this.cargarReportes(); this.snackBar.open('Conversación iniciada', 'Éxito', { duration: 3000 }); }
        else this.snackBar.open('Error al crear conversación', 'Cerrar', { duration: 3000 });
      },
      error: () => { this.snackBar.open('Error al crear conversacion', 'Cerrar', { duration: 3000 });}
    });
  }
  
  enviarMensaje(): void {
    if (!this.nuevoMensaje.trim() || !this.reporteSeleccionadoId || this.reporteSeleccionadoId === 'nuevo') return;
    this.enviando = true;
    this.reportesService.crearComentario(this.reporteSeleccionadoId, this.currentUserId, this.nuevoMensaje).subscribe({
      next: (success) => {
        if (success) { this.cargarComentarios(); this.nuevoMensaje = ''; }
        else this.snackBar.open('Error al enviar mensaje', 'Cerrar', { duration: 3000 });
        this.enviando = false;
      },
      error: () => { this.snackBar.open('Error al enviar mensaje', 'Cerrar', { duration: 3000 }); this.enviando = false; }
    });
  }
  
  // ==================== MÉTODOS PARA EDITAR/ELIMINAR ====================
  
  puedeEditar(comentario: Comentario): boolean {
    if (comentario.puede_editar !== undefined) {
      return comentario.puede_editar;
    }
    const esPropio = comentario.ID_Usuario_Emisor === this.currentUserId;
    if (!esPropio) return false;
    
    const fechaComentario = new Date(comentario.fecha_hora);
    const ahora = new Date();
    const diferenciaMinutos = (ahora.getTime() - fechaComentario.getTime()) / 1000 / 60;
    return diferenciaMinutos <= 15;
  }
  
  puedeEliminar(comentario: Comentario): boolean {
    if (comentario.puede_eliminar !== undefined) {
      return comentario.puede_eliminar;
    }
    const esPropio = comentario.ID_Usuario_Emisor === this.currentUserId;
    if (!esPropio) return false;
    
    const fechaComentario = new Date(comentario.fecha_hora);
    const ahora = new Date();
    const diferenciaMinutos = (ahora.getTime() - fechaComentario.getTime()) / 1000 / 60;
    return diferenciaMinutos <= 15;
  }
  
  editarComentario(comentario: Comentario): void {
    if (!this.puedeEditar(comentario)) {
      this.snackBar.open('No puedes editar este mensaje. Solo tienes 15 minutos después de enviarlo.', 'Cerrar', { duration: 3000 });
      return;
    }
    
    const dialogRef = this.dialog.open(EditarComentarioModalComponent, {
      width: '500px',
      data: { comentarioActual: comentario.comentario }
    });
    
    dialogRef.afterClosed().subscribe((nuevoTexto: string) => {
      if (nuevoTexto && nuevoTexto.trim() !== comentario.comentario) {
        this.reportService.editarComentario({
          idComentario: comentario.ID_Comentario,
          comentario: nuevoTexto.trim()
        }).subscribe({
          next: (success) => {
            if (success) {
              this.snackBar.open('Mensaje editado correctamente', 'Cerrar', { duration: 3000 });
              this.cargarComentarios();
            } else {
              this.snackBar.open('Error al editar el mensaje', 'Cerrar', { duration: 3000 });
            }
          },
          error: () => {
            this.snackBar.open('Error al editar el mensaje', 'Cerrar', { duration: 3000 });
          }
        });
      }
    });
  }
  
  eliminarComentario(comentario: Comentario): void {
    if (!this.puedeEliminar(comentario)) {
      this.snackBar.open('No puedes eliminar este mensaje. Solo tienes 15 minutos después de enviarlo.', 'Cerrar', { duration: 3000 });
      return;
    }
    
    const dialogRef = this.dialog.open(EliminarComentarioModalComponent, {
      width: '400px'
    });
    
    dialogRef.afterClosed().subscribe((confirmado: boolean) => {
      if (confirmado) {
        this.reportService.eliminarComentario(comentario.ID_Comentario).subscribe({
          next: (success) => {
            if (success) {
              this.snackBar.open('Mensaje eliminado correctamente', 'Cerrar', { duration: 3000 });
              this.cargarComentarios();
            } else {
              this.snackBar.open('Error al eliminar el mensaje', 'Cerrar', { duration: 3000 });
            }
          },
          error: () => {
            this.snackBar.open('Error al eliminar el mensaje', 'Cerrar', { duration: 3000 });
          }
        });
      }
    });
  }
  
  obtenerNoLeidas(usuarioId: string): number { return this.mensajesNoLeidos[usuarioId] || 0; }
  
  private scrollToBottom(): void { setTimeout(() => { if (this.scrollAnchor) this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' }); }, 100); }
  
  regresar(): void { this.router.navigate(['/reportes/gestion']); }
}