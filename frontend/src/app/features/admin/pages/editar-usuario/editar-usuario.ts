/**
 * @fileoverview Componente de Edición de Usuario
 * @description Permite editar información de usuarios y cambiar su estado
 * @component EditarUsuarioComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
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
  selector: 'app-editar-usuario',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  templateUrl: './editar-usuario.html',
  styleUrls: ['./editar-usuario.css']
})
export class EditarUsuarioComponent implements OnInit {
  private fb = inject(FormBuilder);
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private apiService = inject(ApiService);
  private userService = inject(UserService);
  private snackBar = inject(MatSnackBar);
  
  usuarioForm!: FormGroup;
  usuario: User | null = null;
  modo: 'actualizar' | 'estado' = 'actualizar';
  loading = true;
  submitting = false;
  success = false;
  error = '';
  hidePassword = true;
  
  ngOnInit(): void {
    this.route.params.subscribe(params => {
      const uuid = params['uuid'];
      this.route.queryParams.subscribe(queryParams => {
        this.modo = queryParams['modo'] === 'estado' ? 'estado' : 'actualizar';
        if (uuid) {
          this.cargarUsuario(uuid);
        }
      });
    });
  }
  
  private cargarUsuario(uuid: string): void {
    this.loading = true;
    this.error = '';
    
    this.apiService.get(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid)).subscribe({
      next: (response) => {
        if (response.success && response.usuario) {
          this.usuario = response.usuario;
          this.inicializarFormulario();
        } else {
          this.error = response.message || 'Usuario no encontrado';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar usuario';
        this.loading = false;
      }
    });
  }
  
  private inicializarFormulario(): void {
    if (!this.usuario) return;
    
    if (this.modo === 'actualizar') {
      this.usuarioForm = this.fb.group({
        nombre: [this.usuario.nombre, Validators.required],
        apellido: [this.usuario.apellido, Validators.required],
        ci: [this.usuario.ci, [Validators.required, Validators.pattern(/^\d{10}$/)]],
        email: [this.usuario.email, [Validators.required, Validators.email]],
        usuario_asignado: [this.usuario.usuario_asignado, [Validators.required, Validators.maxLength(15)]],
        tipo: [this.usuario.tipo, Validators.required],
        especialidad: [this.usuario.Especialidad || ''],
        contrasena: ['', [Validators.minLength(8)]]
      });
    } else {
      this.usuarioForm = this.fb.group({
        estado: ['', Validators.required]
      });
    }
  }
  
  onSubmit(): void {
    if (this.usuarioForm.invalid) {
      this.snackBar.open('Complete todos los campos correctamente', 'Cerrar', { duration: 3000 });
      return;
    }
    
    if (!confirm('¿Está seguro de guardar los cambios?')) return;
    
    this.submitting = true;
    const uuid = this.route.snapshot.params['uuid'];
    
    if (this.modo === 'actualizar') {
      this.actualizarUsuarioCompleto(uuid);
    } else {
      this.actualizarEstadoUsuario(uuid);
    }
  }
  
  private actualizarUsuarioCompleto(uuid: string): void {
    const formData = this.usuarioForm.value;
    
    if (!formData.contrasena) {
      delete formData.contrasena;
    }
    
    this.apiService.put(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid), formData).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Usuario actualizado correctamente', 'Cerrar', { duration: 3000 });
          setTimeout(() => this.router.navigate(['/admin/consultar-usuarios']), 2000);
        } else {
          this.snackBar.open(response.message || 'Error al actualizar usuario', 'Cerrar', { duration: 3000 });
        }
        this.submitting = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al actualizar usuario', 'Cerrar', { duration: 3000 });
        this.submitting = false;
      }
    });
  }
  
  private actualizarEstadoUsuario(uuid: string): void {
    const nuevoEstado = this.usuarioForm.get('estado')?.value;
    
    this.apiService.patch(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid), { estado: nuevoEstado }).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Estado actualizado correctamente', 'Cerrar', { duration: 3000 });
          setTimeout(() => this.router.navigate(['/admin/consultar-usuarios']), 2000);
        } else {
          this.snackBar.open(response.message || 'Error al cambiar estado', 'Cerrar', { duration: 3000 });
        }
        this.submitting = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al cambiar estado', 'Cerrar', { duration: 3000 });
        this.submitting = false;
      }
    });
  }
  
  regresar(): void {
    this.router.navigate(['/admin/consultar-usuarios']);
  }
}