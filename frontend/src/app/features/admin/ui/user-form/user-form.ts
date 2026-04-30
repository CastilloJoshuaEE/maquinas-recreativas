/**
 * @fileoverview Formulario de Usuario
 * @description Componente reutilizable para crear y editar usuarios
 * @component UserFormComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatStepperModule } from '@angular/material/stepper';
import { USER_ROLES, USER_STATES, TECNICO_ESPECIALIDADES, VALIDATION_PATTERNS } from '@core/constants/app.constants';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-user-form',
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
    MatStepperModule
  ],
  templateUrl: './user-form.html',
  styleUrls: ['./user-form.css']
})
export class UserFormComponent implements OnInit, OnDestroy {
  @Input() userData: User | null = null;
  @Input() modo: 'crear' | 'editar' | 'estado' = 'crear';
  @Output() onSubmit = new EventEmitter<any>();
  @Output() onCancel = new EventEmitter<void>();

  private fb = inject(FormBuilder);

  userForm!: FormGroup;
  hidePassword = true;
  hideConfirmPassword = true;
  submitting = false;
  success = false;
  error = '';

  roles = Object.values(USER_ROLES);
  estados = Object.values(USER_STATES);
  especialidades = Object.values(TECNICO_ESPECIALIDADES);

  ngOnInit(): void {
    this.initForm();
    if (this.userData && this.modo !== 'crear') {
      this.cargarDatos();
    }
    this.setupValidators();
  }

  ngOnDestroy(): void {}

  private initForm(): void {
    if (this.modo === 'estado') {
      this.userForm = this.fb.group({
        estado: ['', Validators.required]
      });
    } else {
      this.userForm = this.fb.group({
        nombre: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
        apellido: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
        ci: ['', [Validators.required, Validators.pattern(VALIDATION_PATTERNS.CI)]],
        email: ['', [Validators.required, Validators.email]],
        usuario_asignado: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(15)]],
        tipo: [USER_ROLES.LOGISTICA, Validators.required],
        especialidad: [''],
        contrasena: ['', this.modo === 'crear' ? [Validators.required, Validators.minLength(8)] : [Validators.minLength(8)]],
        confirmar_contrasena: ['']
      }, { validators: this.modo === 'crear' ? this.passwordMatchValidator : [] });
    }
  }

  private passwordMatchValidator(group: AbstractControl): ValidationErrors | null {
    const password = group.get('contrasena')?.value;
    const confirm = group.get('confirmar_contrasena')?.value;
    return password === confirm ? null : { passwordMismatch: true };
  }

  private setupValidators(): void {
    if (this.modo !== 'estado') {
      this.userForm.get('tipo')?.valueChanges.subscribe(tipo => {
        const especialidadControl = this.userForm.get('especialidad');
        if (tipo === USER_ROLES.TECNICO) {
          especialidadControl?.setValidators([Validators.required]);
        } else {
          especialidadControl?.clearValidators();
          especialidadControl?.setValue('');
        }
        especialidadControl?.updateValueAndValidity();
      });
    }
  }

  private cargarDatos(): void {
    if (!this.userData) return;

    if (this.modo === 'estado') {
      this.userForm.patchValue({ estado: this.userData.estado });
    } else {
      this.userForm.patchValue({
        nombre: this.userData.nombre,
        apellido: this.userData.apellido,
        ci: this.userData.ci,
        email: this.userData.email,
        usuario_asignado: this.userData.usuario_asignado,
        tipo: this.userData.tipo,
        especialidad: this.userData.Especialidad || ''
      });
    }
  }

  get formTitle(): string {
    if (this.modo === 'crear') return 'Registrar Nuevo Usuario';
    if (this.modo === 'estado') return 'Cambiar Estado de Usuario';
    return 'Editar Usuario';
  }

  get submitLabel(): string {
    if (this.modo === 'crear') return 'Registrar Usuario';
    if (this.modo === 'estado') return 'Actualizar Estado';
    return 'Guardar Cambios';
  }

  onSubmitForm(): void {
    if (this.userForm.invalid) {
      this.userForm.markAllAsTouched();
      return;
    }

    this.submitting = true;
    let formValue = this.userForm.value;

    if (this.modo !== 'estado') {
      delete formValue.confirmar_contrasena;
      if (!formValue.contrasena) delete formValue.contrasena;
    }

    this.onSubmit.emit(formValue);
  }

  onCancelForm(): void {
    this.onCancel.emit();
  }

  isFieldInvalid(fieldName: string): boolean {
    const field = this.userForm.get(fieldName);
    return field ? field.invalid && (field.dirty || field.touched) : false;
  }

  getFieldError(fieldName: string): string {
    const field = this.userForm.get(fieldName);
    if (!field) return '';

    if (field.hasError('required')) return 'Este campo es requerido';
    if (field.hasError('email')) return 'Ingrese un email válido';
    if (field.hasError('minlength')) return `Mínimo ${field.errors?.['minlength']?.requiredLength} caracteres`;
    if (field.hasError('maxlength')) return `Máximo ${field.errors?.['maxlength']?.requiredLength} caracteres`;
    if (field.hasError('pattern')) {
      if (fieldName === 'ci') return 'La cédula debe tener 10 dígitos';
      return 'Formato inválido';
    }
    if (field.hasError('passwordMismatch')) return 'Las contraseñas no coinciden';
    
    return '';
  }
}