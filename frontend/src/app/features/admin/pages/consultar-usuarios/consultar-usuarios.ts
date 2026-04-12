/**
 * @fileoverview Componente de Consulta de Usuarios
 * @description Permite consultar, filtrar, editar y eliminar usuarios del sistema
 * @component ConsultarUsuariosComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { ApiService } from '@core/services/api';
import { UserService } from '@core/services/user';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-consultar-usuarios',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatTableModule,
    MatPaginatorModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  templateUrl: './consultar-usuarios.html',
  styleUrls: ['./consultar-usuarios.css']
})
export class ConsultarUsuariosComponent implements OnInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private apiService = inject(ApiService);
  private userService = inject(UserService);
  private snackBar = inject(MatSnackBar);
  
  filtrosForm!: FormGroup;
  usuarios: User[] = [];
  displayedColumns: string[] = ['id', 'ci', 'nombre', 'email', 'usuario_asignado', 'estado', 'tipo', 'acciones'];
  
  loading = false;
  error = '';
  totalItems = 0;
  pageSize = 10;
  currentPage = 0;
  
  modalHistorialVisible = false;
  historialUsuario: any[] = [];
  usuarioSeleccionado: User | null = null;
  cargandoHistorial = false;
  
  ngOnInit(): void {
    this.initForm();
    this.cargarUsuarios();
  }
  
  private initForm(): void {
    this.filtrosForm = this.fb.group({
      ci: [''],
      estado: [''],
      tipo: [''],
      rango_fecha: ['']
    });
  }
cargarUsuarios(): void {
  this.loading = true;
  this.error = '';
  
  const params: any = { page: this.currentPage + 1, limit: this.pageSize };
  
  this.apiService.get(API_ENDPOINTS.ADMIN_USERS, params).subscribe({
    next: (response: any) => {
      console.log('Respuesta completa:', response);
      
      if (response === null) {
        this.error = 'Error: El servidor devolvió null. Verificar logs del backend.';
        this.usuarios = [];
        this.totalItems = 0;
      }
      else if (response && response.success === true) {
        this.usuarios = response['usuarios'] || [];
        this.totalItems = response['total'] || this.usuarios.length;
      }
      else if (response && response.success === false) {
        this.error = response.message || 'Error del servidor';
        this.usuarios = [];
        this.totalItems = 0;
      }
      else if (Array.isArray(response)) {
        this.usuarios = response;
        this.totalItems = response.length;
      }
      else {
        this.error = 'Formato de respuesta inválido';
        this.usuarios = [];
        this.totalItems = 0;
      }
      this.loading = false;
    },
    error: (err) => {
      console.error('Error detallado:', err);
      this.error = err.message || 'Error de conexión';
      this.loading = false;
    }
  });
}
  
  buscarUsuarios(): void {
    this.currentPage = 0;
    this.cargarUsuarios();
  }
  
  limpiarFiltros(): void {
    this.filtrosForm.reset({ ci: '', estado: '', tipo: '', rango_fecha: '' });
    this.buscarUsuarios();
  }
  
  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.cargarUsuarios();
  }
  
  regresar(): void {
    this.router.navigate(['/admin/gestion-usuarios']);
  }
  
  editarUsuario(uuid: string): void {
    this.router.navigate([`/admin/editar-usuario/${uuid}`]);
  }
  
  cambiarEstado(usuario: User): void {
    this.router.navigate([`/admin/editar-usuario/${usuario.id}`], { queryParams: { modo: 'estado' } });
  }
  
  verHistorial(usuario: User): void {
    this.usuarioSeleccionado = usuario;
    this.modalHistorialVisible = true;
    this.cargarHistorialUsuario(usuario.id);
  }
  
  cargarHistorialUsuario(usuarioId: string): void {
    this.cargandoHistorial = true;
    this.userService.getHistorialActividades(usuarioId).subscribe({
      next: (historial) => {
        this.historialUsuario = historial;
        this.cargandoHistorial = false;
      },
      error: () => {
        this.historialUsuario = [];
        this.cargandoHistorial = false;
      }
    });
  }
  
  cerrarModalHistorial(): void {
    this.modalHistorialVisible = false;
    this.historialUsuario = [];
    this.usuarioSeleccionado = null;
  }
  
  eliminarUsuario(usuario: User): void {
    if (!confirm(`¿Está seguro de eliminar al usuario ${usuario.nombre} ${usuario.apellido}?`)) return;
    
    this.loading = true;
    this.apiService.delete(API_ENDPOINTS.ADMIN_USER_BY_ID(usuario.id)).subscribe({
      next: (response) => {
        if (response.success) {
          this.snackBar.open('Usuario eliminado correctamente', 'Cerrar', { duration: 3000 });
          this.cargarUsuarios();
        } else {
          this.snackBar.open(response.message || 'Error al eliminar usuario', 'Cerrar', { duration: 3000 });
        }
        this.loading = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al eliminar usuario', 'Cerrar', { duration: 3000 });
        this.loading = false;
      }
    });
  }
}