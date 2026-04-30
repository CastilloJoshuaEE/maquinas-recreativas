/**
 * @fileoverview Componente de Registro de Usuario
 * @description Página de registro - usuario_asignado y contraseña son generados automáticamente por el backend
 * @component RegistroComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '@core/services/auth';
import { RegisterData } from '@core/models/user.model';
import { AuthResponse } from '@core/models/user.model';



@Component({
  selector: 'app-registro',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatDialogModule
  ],
  templateUrl: './registro.html',
  styleUrls: ['./registro.css']
})
export class RegistroComponent {
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private router = inject(Router);
  private toastr = inject(ToastrService);
  private dialog = inject(MatDialog);
  
  registroForm: FormGroup;
  loading = false;
  fileName: string = '';
  
  constructor() {
    this.registroForm = this.fb.group({
      nombre: ['', [Validators.required, Validators.minLength(2)]],
      apellido: ['', [Validators.required, Validators.minLength(2)]],
      ci: ['', [Validators.required, Validators.pattern(/^\d{10}$/)]],
      email: ['', [Validators.required, Validators.email]],
      tipo: ['Logistica', [Validators.required]],
      especialidad: ['']
    });
    
    // Validación condicional para especialidad
    this.registroForm.get('tipo')?.valueChanges.subscribe(tipo => {
      const especialidadControl = this.registroForm.get('especialidad');
      if (tipo === 'Tecnico') {
        especialidadControl?.setValidators([Validators.required]);
      } else {
        especialidadControl?.clearValidators();
        especialidadControl?.setValue('');
      }
      especialidadControl?.updateValueAndValidity();
    });
  }
  
  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
      const file = input.files[0];
      if (file.type === 'application/pdf') {
        this.fileName = file.name;
        this.toastr.success(`Currículum "${file.name}" seleccionado correctamente`);
        
      } else {
        this.toastr.error('Por favor, seleccione un archivo PDF válido');
        input.value = '';
        this.fileName = '';
      }
    }
  }
  
  soloNumeros(event: KeyboardEvent): boolean {
    const charCode = event.charCode;
    if (charCode >= 48 && charCode <= 57) {
      return true;
    }
    event.preventDefault();
    return false;
  }
  
  onSubmit(): void {
    if (this.registroForm.invalid) {
      this.registroForm.markAllAsTouched();
      return;
    }
    
    if (!confirm('¿Seguro desea registrarse? Recibirá sus credenciales por correo electrónico.')) {
      return;
    }
    
    this.loading = true;
    const formData: RegisterData = {
      nombre: this.registroForm.value.nombre,
      apellido: this.registroForm.value.apellido,
      ci: this.registroForm.value.ci,
      email: this.registroForm.value.email,
      tipo: this.registroForm.value.tipo,
      especialidad: this.registroForm.value.especialidad
    };
    
    this.authService.register(formData).subscribe({
      next: (response: AuthResponse) => {
        this.loading = false;
        
        if (response.success) {

          // Limpiar formulario
          this.registroForm.reset({ tipo: 'Logistica' });
          this.fileName = '';
        } else {
          this.toastr.error(response.message || 'Error al registrar usuario', 'Error');
        }
      },
      error: (error) => {
        this.loading = false;
        this.toastr.error(error.message || 'Error de conexión con el servidor', 'Error');
      }
    });
  }
}