import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, ElementRef, ViewChild, inject, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatDialog, MatDialogModule, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AuthService } from '@core/services/auth';
import { ReportService } from '@core/services/report';
import { User } from '@core/models/user.model';
import { Reporte, Comentario } from '@core/models/reporte.model';
import { Subject } from 'rxjs';

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
    private dialogRef: MatDialogRef<EditarComentarioModalComponent>,
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
  constructor(private dialogRef: MatDialogRef<EliminarComentarioModalComponent>) {}
  cancelar(): void { this.dialogRef.close(false); }
  confirmar(): void { this.dialogRef.close(true); }
}

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, 
            MatFormFieldModule, MatInputModule, MatProgressSpinnerModule, MatDialogModule, MatSelectModule],
  templateUrl: './chat.html',
  styleUrls: ['./chat.css']
})
export class ChatComponent implements OnInit, OnDestroy {
  @Input() currentUser: User | null = null;
  @Input() asPanel = false;
  @Input() modalScrollable = false;
  @Input() showUserList = true;
  @Input() showSearch = true;
  @Input() initialUserId: string | null = null;
  @Input() initialReporteId: string | null = null;
  @Output() onClose = new EventEmitter<void>();
  @Output() onMessageSent = new EventEmitter<Comentario>();
  
  private authService = inject(AuthService);
  private reportService = inject(ReportService);
  private dialog = inject(MatDialog);
  private snackBar = inject(MatSnackBar);
  
  @ViewChild('scrollAnchor') scrollAnchor!: ElementRef;
  
  currentUserId = '';
  usuarios: User[] = [];
  usuariosFiltrados: User[] = [];
  searchTerm = '';
  selectedUser: User | null = null;
  selectedReporteId: string | null = null;
  reportes: Reporte[] = [];
  comentarios: Comentario[] = [];
  nuevoMensaje = '';
  cargandoUsuarios = false;
  cargandoMensajes = false;
  enviando = false;
  editandoMensajeId: string | null = null;
  private destroy$ = new Subject<void>();
  private refreshInterval: any;

  ngOnInit(): void {
    this.currentUserId = this.currentUser?.id || this.authService.getCurrentUser()?.id || '';
    if (this.currentUserId) this.cargarUsuarios();
    this.refreshInterval = setInterval(() => { if (this.selectedUser && this.selectedReporteId) this.cargarComentarios(); }, 5000);
  }
  
  ngOnDestroy(): void {
    this.destroy$.next(); this.destroy$.complete();
    if (this.refreshInterval) clearInterval(this.refreshInterval);
  }
  
  cargarUsuarios(): void {
    this.cargandoUsuarios = true;
    this.reportService.getUsuariosChat(this.currentUserId).subscribe({
      next: (usuarios) => {
        this.usuarios = usuarios.filter(u => u.id !== this.currentUserId);
        this.usuariosFiltrados = [...this.usuarios];
        this.cargandoUsuarios = false;
        if (this.initialUserId && !this.selectedUser) {
          const usuarioInicial = this.usuarios.find(u => u.id === this.initialUserId);
          if (usuarioInicial) this.seleccionarUsuario(usuarioInicial);
        }
      },
      error: () => { this.cargandoUsuarios = false; }
    });
  }
  
  filtrarUsuarios(): void {
    if (!this.searchTerm.trim()) this.usuariosFiltrados = [...this.usuarios];
    else {
      const term = this.searchTerm.toLowerCase();
      this.usuariosFiltrados = this.usuarios.filter(u => u.nombre.toLowerCase().includes(term) || u.apellido.toLowerCase().includes(term) || u.email.toLowerCase().includes(term));
    }
  }
  
  seleccionarUsuario(usuario: User): void { this.selectedUser = usuario; this.cargarReportes(); }
  
  cargarReportes(): void {
    if (!this.selectedUser) return;
    this.reportService.getChat(this.currentUserId, this.selectedUser.id).subscribe({
      next: (data) => {
        this.reportes = data.reportes;
        if (this.initialReporteId && this.reportes.length > 0) {
          const reporteInicial = this.reportes.find(r => 
            (r.ID_Reporte || r.id) === this.initialReporteId
          );
          if (reporteInicial) { 
            this.selectedReporteId = reporteInicial.ID_Reporte || reporteInicial.id || null; 
            this.cargarComentarios(); 
          } else if (this.reportes.length > 0) { 
            this.selectedReporteId = this.reportes[0].ID_Reporte || this.reportes[0].id || null; 
            this.cargarComentarios(); 
          }
        } else if (this.reportes.length > 0) { 
          this.selectedReporteId = this.reportes[0].ID_Reporte || this.reportes[0].id || null; 
          this.cargarComentarios(); 
        } else { 
          this.comentarios = []; 
        }
      },
      error: () => { this.comentarios = []; }
    });
  }
  
  cargarComentarios(): void {
    if (!this.selectedReporteId) return;
    this.cargandoMensajes = true;
    this.reportService.getComentariosByReporte(this.selectedReporteId).subscribe({
      next: (comentarios) => { 
        this.comentarios = comentarios; 
        this.cargandoMensajes = false; 
        setTimeout(() => this.scrollToBottom(), 100); 
      },
      error: () => { this.cargandoMensajes = false; }
    });
  }
  
  enviarMensaje(): void {
    if (!this.nuevoMensaje.trim() || !this.selectedUser) return;
    this.enviando = true;
    const crearReporte = () => {
      this.reportService.createReporte({ 
        ID_Usuario_Emisor: this.currentUserId, 
        ID_Usuario_Destinatario: this.selectedUser!.id, 
        descripcion: `Chat con ${this.selectedUser!.nombre} ${this.selectedUser!.apellido}` 
      }).subscribe({
        next: (reporteId) => { 
          if (reporteId) { 
            this.selectedReporteId = reporteId; 
            this.enviarComentario(reporteId); 
          } else { 
            this.enviando = false; 
          } 
        },
        error: () => { this.enviando = false; }
      });
    };
    if (!this.selectedReporteId) crearReporte();
    else this.enviarComentario(this.selectedReporteId);
  }
  
  private enviarComentario(reporteId: string): void {
    this.reportService.createComentario(reporteId, this.currentUserId, this.nuevoMensaje).subscribe({
      next: (success) => {
        if (success) {
          this.onMessageSent.emit({ 
            ID_Comentario: Date.now().toString(), 
            ID_Reporte: reporteId, 
            ID_Usuario_Emisor: this.currentUserId, 
            comentario: this.nuevoMensaje, 
            fecha_hora: new Date().toISOString(), 
            nombre: this.currentUser?.nombre, 
            apellido: this.currentUser?.apellido 
          } as Comentario);
          this.cargarComentarios(); 
          this.nuevoMensaje = '';
        }
        this.enviando = false;
      },
      error: () => { this.enviando = false; }
    });
  }

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
  
  getUnreadCount(userId: string): number { return 0; }
  
  cerrarChat(): void { 
    this.selectedUser = null; 
    this.selectedReporteId = null; 
    this.comentarios = []; 
    this.onClose.emit(); 
  }
  
  private scrollToBottom(): void { 
    setTimeout(() => { 
      if (this.scrollAnchor) 
        this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' }); 
    }, 100); 
  }
}