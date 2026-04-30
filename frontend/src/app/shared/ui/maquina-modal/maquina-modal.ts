import { Component, Inject, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MaquinasSharedService } from '@core/services/maquina';
import { Maquina, CreateMaquinaData, UpdateMaquinaData } from '@core/models/maquina.model';
import { Comercio } from '@core/models/recaudacion.model';
import { LogisticaService } from '@features/logistica/services/logistica';

export interface MaquinaModalData {
  modo: 'crear' | 'editar';
  maquina?: Maquina;
  userId: string;
}

@Component({
  selector: 'app-maquina-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatButtonModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './maquina-modal.html',
  styleUrls: ['./maquina-modal.css']
})
export class MaquinaModalComponent implements OnInit {
  private fb = inject(FormBuilder);
  private maquinasService = inject(MaquinasSharedService);
  private logisticaService = inject(LogisticaService);
  private dialogRef = inject(MatDialogRef<MaquinaModalComponent>);
  private snackBar = inject(MatSnackBar);
  
  data = inject<MaquinaModalData>(MAT_DIALOG_DATA);

  maquinaForm!: FormGroup;
  comercios: Comercio[] = [];
  componentesDisponibles: any[] = [];
  cargandoComercios = false;
  cargandoComponentes = false;
  guardando = false;

  ngOnInit(): void {
    this.initForm();
    this.cargarComercios();
    if (this.data.modo === 'crear') {
      this.cargarComponentesDisponibles();
    }
  }

private initForm(): void {
  const maquina = this.data.maquina;
  
  // Obtener valores independientemente de mayúsculas/minúsculas
  const tipoValue = maquina?.tipo || maquina?.Tipo || '';
  const estadoValue = maquina?.estado || maquina?.Estado || 'Ensamblandose';
  
  console.log('Valores de máquina:', { tipo: tipoValue, estado: estadoValue });
  
  this.maquinaForm = this.fb.group({
    nombre: [maquina?.Nombre_Maquina || '', [Validators.required, Validators.minLength(3)]],
    tipo: [tipoValue, Validators.required],
    idComercio: [maquina?.ID_Comercio || '', Validators.required],
    idPlaca: ['', this.data.modo === 'crear' ? Validators.required : null],
    idCarcasa: ['', this.data.modo === 'crear' ? Validators.required : null],
    estado: [estadoValue]
  });
}
  private cargarComercios(): void {
    this.cargandoComercios = true;
    this.logisticaService.getComercios().subscribe({
      next: (comercios) => {
        this.comercios = comercios;
        this.cargandoComercios = false;
      },
      error: () => {
        this.snackBar.open('Error al cargar comercios', 'Cerrar', { duration: 3000 });
        this.cargandoComercios = false;
      }
    });
  }

  private cargarComponentesDisponibles(): void {
    this.cargandoComponentes = true;
    // Aquí llamas a tu servicio de componentes
    // this.componentesService.getComponentesDisponibles().subscribe(...)
    // Simulación por ahora
    this.componentesDisponibles = [
      { ID_Componente: '1', nombre: 'Placa Base Arcade Pro V2', tipo: 'Electronico' },
      { ID_Componente: '2', nombre: 'Placa Base Arcade Standard', tipo: 'Electronico' }
    ];
    this.cargandoComponentes = false;
  }

  guardar(): void {
    if (this.maquinaForm.invalid) {
      this.maquinaForm.markAllAsTouched();
      return;
    }

    this.guardando = true;
    const valores = this.maquinaForm.value;

    if (this.data.modo === 'crear') {
      const createData: CreateMaquinaData = {
        nombre: valores.nombre,
        tipo: valores.tipo,
        idComercio: valores.idComercio,
        idUsuarioLogistica: this.data.userId,
        idPlaca: valores.idPlaca,
        idCarcasa: valores.idCarcasa
      };

      this.maquinasService.createMaquina(createData).subscribe({
        next: (response) => {
          this.snackBar.open('Máquina creada exitosamente', 'Cerrar', { duration: 3000 });
          this.dialogRef.close({ success: true, maquinaId: response.maquinaId });
          this.guardando = false;
        },
        error: (error) => {
          this.snackBar.open(error.error?.message || 'Error al crear máquina', 'Cerrar', { duration: 3000 });
          this.guardando = false;
        }
      });
    } else {
      const updateData: UpdateMaquinaData = {
        idMaquina: this.data.maquina!.ID_Maquina,
        nombre: valores.nombre,
        tipo: valores.tipo,
        idComercio: valores.idComercio,
        estado: valores.estado
      };

      this.maquinasService.updateMaquina(updateData).subscribe({
        next: () => {
          this.snackBar.open('Máquina actualizada exitosamente', 'Cerrar', { duration: 3000 });
          this.dialogRef.close({ success: true });
          this.guardando = false;
        },
        error: (error) => {
          this.snackBar.open(error.error?.message || 'Error al actualizar máquina', 'Cerrar', { duration: 3000 });
          this.guardando = false;
        }
      });
    }
  }

  cerrar(): void {
    this.dialogRef.close({ success: false });
  }

  getTitulo(): string {
    return this.data.modo === 'crear' ? 'Nueva Máquina Recreativa' : 'Editar Máquina';
  }
}