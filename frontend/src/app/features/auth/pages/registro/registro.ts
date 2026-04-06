/**
 * @fileoverview Componente de Registro de Usuario
 * @description Página de registro para nuevos usuarios del sistema
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
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '@core/services/auth';

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
    MatProgressSpinnerModule
  ],
  templateUrl: './registro.html',
  styleUrls: ['./registro.css']
})
export class RegistroComponent {
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private router = inject(Router);
  private toastr = inject(ToastrService);
  
  registroForm: FormGroup;
  loading = false;
  hidePassword = true;
  fileName: string = '';
  
  constructor() {
    this.registroForm = this.fb.group({
      nombre: ['', [Validators.required]],
      apellido: ['', [Validators.required]],
      ci: ['', [Validators.required, Validators.pattern(/^\d{10}$/)]],
      email: ['', [Validators.required, Validators.email]],
      tipo: ['Logistica', [Validators.required]],
      especialidad: [''],
      usuario_asignado: ['', [Validators.required, Validators.maxLength(15)]],
      contrasena: ['', [Validators.required, Validators.minLength(8)]]
    });
    
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
  
  onSubmit(): void {
    if (this.registroForm.invalid) return;
    
    if (!confirm('¿Seguro desea registrarse?')) return;
    
    this.loading = true;
    const formData = this.registroForm.value;
    
    this.authService.register(formData).subscribe({
      next: (response) => {
        if (response.success) {
          this.toastr.success(
            'No olvide su usuario y contraseña que acaba de registrar. Pronto nos pondremos en contacto con usted.',
            '¡Registro exitoso!'
          );
          setTimeout(() => this.router.navigate(['/auth/login']), 3000);
        } else {
          this.toastr.error(response.message || 'Error al registrar usuario', 'Error');
        }
        this.loading = false;
      },
      error: (error) => {
        this.toastr.error(error.message || 'Error de conexión con el servidor', 'Error');
        this.loading = false;
      }
    });
  }
}