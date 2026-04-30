import { Component, OnInit, OnDestroy, inject, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatStepperModule, MatStepper } from '@angular/material/stepper';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { ContabilidadService } from '../../services/contabilidad';
import { AuthService } from '@core/services/auth';
import { Comercio } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-registrar-recaudacion',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, MatStepperModule, MatFormFieldModule,
    MatInputModule, MatSelectModule, MatButtonModule, MatIconModule,
    MatProgressSpinnerModule, AdminHeaderComponent
  ],
  templateUrl: './registrar-recaudacion.html',
  styleUrls: ['./registrar-recaudacion.css']
})
export class RegistrarRecaudacionComponent implements OnInit, OnDestroy {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  @ViewChild('stepper') stepper!: MatStepper;
  
  // Formularios separados por paso
  paso1Form!: FormGroup;
  paso2Form!: FormGroup;
  
  comercios: Comercio[] = [];
  maquinas: Maquina[] = [];
  cargandoMaquinas = false;
  submitting = false;
  success = false;
  private subscriptions: Subscription[] = [];
  
  ngOnInit(): void {
    this.initForms();
    this.cargarComercios();
    this.setupCalculosAutomaticos();
  }
  
  ngOnDestroy(): void {
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
  
  private initForms(): void {
    this.paso1Form = this.fb.group({
      ID_Comercio: ['', Validators.required],
      ID_Maquina: ['', Validators.required],
      Tipo_Comercio: [{ value: '', disabled: true }]
    });
    
    this.paso2Form = this.fb.group({
      Porcentaje_Comercio: [20, [Validators.min(0), Validators.max(100)]],
      Monto_Total: ['', [Validators.required, Validators.min(0.01)]],
      Monto_Comercio: [{ value: '0.00', disabled: true }],
      Monto_Empresa: [{ value: '0.00', disabled: true }],
      fecha: [this.getFechaHoraActual(), Validators.required],
      detalle: ['']
    });
  }
  
  private getFechaHoraActual(): string {
    const ahora = new Date();
    ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
    return ahora.toISOString().slice(0, 16);
  }
  
  private cargarComercios(): void {
    this.contabilidadService.getComercios().subscribe({
      next: (data) => { this.comercios = data; },
      error: () => { this.snackBar.open('Error al cargar comercios', 'Cerrar', { duration: 3000 }); }
    });
  }
  
onComercioChange(): void {
    const idComercio = this.paso1Form.get('ID_Comercio')?.value;
    const comercio = this.comercios.find(c => c.ID_Comercio === idComercio);
    if (comercio) {
        // Asegurar que Tipo_Comercio tenga un valor válido
        const tipoComercio = comercio.Tipo || comercio.tipo || 'Minorista';
        this.paso1Form.patchValue({ Tipo_Comercio: tipoComercio });
        console.log('Tipo de comercio asignado:', tipoComercio);
        this.cargarMaquinasPorComercio(idComercio);
    }
}
  
  private cargarMaquinasPorComercio(idComercio: string): void {
    this.cargandoMaquinas = true;
    this.paso1Form.patchValue({ ID_Maquina: '' });
    
    this.contabilidadService.getMaquinasOperativasPorComercio(idComercio).subscribe({
        next: (data) => {
            console.log('Máquinas cargadas:', data);
            this.maquinas = data;
            this.cargandoMaquinas = false;
            
            if (data.length === 0) {
                this.snackBar.open('No hay máquinas operativas para este comercio', 'Cerrar', { duration: 3000 });
            }
        },
        error: (err) => {
            console.error('Error cargando máquinas:', err);
            this.maquinas = [];
            this.cargandoMaquinas = false;
            this.snackBar.open('Error al cargar máquinas', 'Cerrar', { duration: 3000 });
        }
    });
  }
  
  onMaquinaChange(): void {
    const idMaquina = this.paso1Form.get('ID_Maquina')?.value;
    console.log('Máquina seleccionada:', idMaquina);
  }
  
  isStep1Valid(): boolean {
    const tieneComercio = !!this.paso1Form.get('ID_Comercio')?.value;
    const tieneMaquina = !!this.paso1Form.get('ID_Maquina')?.value;
    return tieneComercio && tieneMaquina;
  }
  
  private setupCalculosAutomaticos(): void {
    const montoTotalSub = this.paso2Form.get('Monto_Total')?.valueChanges.subscribe(() => this.calcularMontos());
    const porcentajeSub = this.paso2Form.get('Porcentaje_Comercio')?.valueChanges.subscribe(() => this.calcularMontos());
    if (montoTotalSub) this.subscriptions.push(montoTotalSub);
    if (porcentajeSub) this.subscriptions.push(porcentajeSub);
  }
  
  private calcularMontos(): void {
    const tipo = this.paso1Form.get('Tipo_Comercio')?.value;
    const montoTotal = parseFloat(this.paso2Form.get('Monto_Total')?.value) || 0;
    if (tipo === 'Mayorista') {
      const porcentaje = parseFloat(this.paso2Form.get('Porcentaje_Comercio')?.value) || 0;
      const montoComercio = montoTotal * (porcentaje / 100);
      const montoEmpresa = montoTotal - montoComercio;
      this.paso2Form.patchValue({
        Monto_Comercio: montoComercio.toFixed(2),
        Monto_Empresa: montoEmpresa.toFixed(2)
      }, { emitEvent: false });
    } else {
      this.paso2Form.patchValue({
        Monto_Comercio: '0.00',
        Monto_Empresa: montoTotal.toFixed(2)
      }, { emitEvent: false });
    }
  }
registrarRecaudacion(): void {
    if (this.paso2Form.invalid) {
        this.snackBar.open('Complete todos los campos correctamente', 'Cerrar', { duration: 3000 });
        return;
    }
    if (!confirm('¿Está seguro de registrar esta recaudación?')) return;
    
    this.submitting = true;
    const currentUser = this.authService.getCurrentUser();
    const paso1Value = this.paso1Form.getRawValue();
    const paso2Value = this.paso2Form.getRawValue();
    
    // Obtener tipoComercio de forma segura
    let tipoComercio = paso1Value.Tipo_Comercio;
    if (!tipoComercio || tipoComercio === '') {
        // Buscar el comercio seleccionado para obtener su tipo
        const comercioSeleccionado = this.comercios.find(c => c.ID_Comercio === paso1Value.ID_Comercio);
        tipoComercio = comercioSeleccionado?.Tipo || comercioSeleccionado?.tipo || 'Minorista';
        console.log('Tipo de comercio obtenido del comercio:', tipoComercio);
    }
    
    const data = {
        idMaquina: paso1Value.ID_Maquina,
        tipoComercio: tipoComercio,  // Ahora con valor asegurado
        montoTotal: parseFloat(paso2Value.Monto_Total),
        porcentajeComercio: tipoComercio === 'Mayorista' ? paso2Value.Porcentaje_Comercio : 0,
        detalle: paso2Value.detalle || ''
    };
    
    console.log('Enviando datos al backend:', data);
    
    this.contabilidadService.registrarRecaudacion(data).subscribe({
        next: (response) => {
            if (response.success) {
                this.success = true;
                this.snackBar.open('Recaudación registrada correctamente', 'Cerrar', { duration: 3000 });
                setTimeout(() => this.router.navigate(['/contabilidad/gestion-recaudacion']), 2000);
            } else {
                this.snackBar.open(response.message || 'Error al registrar recaudación', 'Cerrar', { duration: 3000 });
            }
            this.submitting = false;
        },
        error: (err) => {
            console.error('Error al registrar recaudación:', err);
            this.snackBar.open(err.message || 'Error al registrar recaudación', 'Cerrar', { duration: 3000 });
            this.submitting = false;
        }
    });
}
  
  regresar(): void {
    this.router.navigate(['/contabilidad/gestion-recaudacion']);
  }
}