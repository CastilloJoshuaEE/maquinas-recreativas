/**
 * @fileoverview Levantar Informe de Recaudación
 * @description Genera y guarda el informe completo de una recaudación
 * @component LevantarInformeComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { ContabilidadService } from '../../services/contabilidad';
import { Recaudacion, Comercio } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';
import { Componente } from '@core/models/componente.model';
import { User } from '@core/models/user.model';
import { AuthService } from '@core/services/auth';

@Component({
  selector: 'app-levantar-informe',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './levantar-informe.html',
  styleUrls: ['./levantar-informe.css']
})
export class LevantarInformeComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  recaudacion: Recaudacion | null = null;
  maquina: Maquina | null = null;
  comercio: Comercio | null = null;
  componentes: Componente[] = [];
  tecnicos = { ensamblador: null, comprobador: null, mantenimiento: null } as any;
  loading = true;
  guardando = false;
  success = false;
  error = '';
  fechaEmision = new Date();
  
  ngOnInit(): void { this.cargarDatos(); }
  
  private cargarDatos(): void {
    const idRecaudacion = this.route.snapshot.params['idRecaudacion'];
    if (!idRecaudacion) { this.error = 'ID de recaudación no válido'; this.loading = false; return; }
    
    this.contabilidadService.getRecaudacionById(idRecaudacion).subscribe({
      next: (recaudacion) => {
        if (!recaudacion) { this.error = 'Recaudación no encontrada'; this.loading = false; return; }
        this.recaudacion = recaudacion;
        this.cargarMaquina(recaudacion.ID_Maquina);
      },
      error: (err) => { this.error = err.message || 'Error al cargar recaudación'; this.loading = false; }
    });
  }
  
  private cargarMaquina(idMaquina: string): void {
    this.contabilidadService.getMaquinasRecaudacion().subscribe({
      next: (maquinas) => {
        this.maquina = maquinas.find(m => m.ID_Maquina === idMaquina) || null;
        if (this.maquina) {
          this.cargarComercio(this.maquina.ID_Comercio);
          this.cargarComponentes(idMaquina);
        } else { this.error = 'Máquina no encontrada'; this.loading = false; }
      },
      error: () => { this.error = 'Error al cargar máquina'; this.loading = false; }
    });
  }
  
  private cargarComercio(idComercio: string): void {
    this.contabilidadService.getComercios().subscribe({
      next: (comercios) => {
        this.comercio = comercios.find(c => c.ID_Comercio === idComercio) || null;
        this.cargarTecnicos();
      },
      error: () => { this.error = 'Error al cargar comercio'; this.loading = false; }
    });
  }
  
  private cargarComponentes(idMaquina: string): void { this.componentes = []; this.loading = false; }
  private cargarTecnicos(): void { this.loading = false; }
  
  get totalPagosTecnicos(): number {
    let total = 0;
    if (this.tecnicos.ensamblador) total += 400;
    if (this.tecnicos.comprobador) total += 400;
    if (this.tecnicos.mantenimiento) total += 400;
    return total;
  }
  
  get totalComponentes(): number { return this.componentes.reduce((sum, comp) => sum + (comp.precio || 0), 0); }
  
  imprimirInforme(): void { setTimeout(() => window.print(), 300); }
  
  guardarInforme(): void {
    if (!this.recaudacion) return;
    this.guardando = true;
    const currentUser = this.authService.getCurrentUser();
    const informeData = {
      ID_Recaudacion: this.recaudacion.ID_Recaudacion, ID_Comercio: this.comercio?.ID_Comercio,
      CI_Usuario: currentUser?.ci, Nombre_Maquina: this.maquina?.Nombre_Maquina,
      Nombre_Comercio: this.comercio?.Nombre, Direccion_Comercio: this.comercio?.Direccion,
      Telefono_Comercio: this.comercio?.Telefono,
      Pago_Ensamblador: this.tecnicos.ensamblador ? 400 : 0, Pago_Comprobador: this.tecnicos.comprobador ? 400 : 0,
      Pago_Mantenimiento: this.tecnicos.mantenimiento ? 400 : 0,
      componentes: this.componentes.map(c => ({ ID_Componente: c.ID_Componente })),
      Monto_Total: this.recaudacion.Monto_Total
    };
    
    this.contabilidadService.guardarInforme(informeData).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Informe guardado correctamente', 'Cerrar', { duration: 3000 });
          setTimeout(() => this.router.navigate(['/contabilidad/consultar-recaudaciones']), 2000);
        } else { this.snackBar.open(response.message || 'Error al guardar informe', 'Cerrar', { duration: 3000 }); }
        this.guardando = false;
      },
      error: (err) => { this.snackBar.open(err.message || 'Error al guardar informe', 'Cerrar', { duration: 3000 }); this.guardando = false; }
    });
  }
  
  regresar(): void { this.router.navigate(['/contabilidad/consultar-recaudaciones']); }
}