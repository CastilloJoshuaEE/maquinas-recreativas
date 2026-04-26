/**
 * @fileoverview Gestión de Componentes para Técnicos
 * @description Permite a los técnicos ver y gestionar componentes disponibles y en uso
 * @component GestionComponentesComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { TecnicoService } from '../../services/tecnico';
import { AuthService } from '@core/services/auth';
import { Componente, LiberarComponenteData } from '@core/models/componente.model';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-gestion-componentes',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, MatTableModule, MatPaginatorModule, MatSelectModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './gestion-componentes.html',
  styleUrls: ['./gestion-componentes.css']
})
export class GestionComponentesComponent implements OnInit {
  private router = inject(Router);
  private tecnicoService = inject(TecnicoService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  user: User | null = null;
  selectedMachine: Maquina | null = null;
  componentesDisponibles: Componente[] = [];
  displayedColumnsDisponibles: string[] = ['ID_Componente', 'nombre', 'tipo', 'precio', 'acciones'];
  cargandoDisponibles = false;
  totalDisponibles = 0;
  pageSize = 10;
  currentPage = 0;
  filtroTipo = '';
  componentesEnUso: Componente[] = [];
  displayedColumnsEnUso: string[] = ['ID_Componente', 'nombre', 'tipo', 'Nombre_Maquina', 'acciones'];
  cargandoEnUso = false;
  
  ngOnInit(): void {
    this.user = this.authService.getCurrentUser();
    this.cargarMquinaSeleccionada();
    if (this.user?.id) {
      this.cargarComponentesDisponibles();
      this.cargarComponentesEnUso();
    }
  }
  
private cargarMquinaSeleccionada(): void {
    const storedMachine = localStorage.getItem('selectedMachine');
    if (storedMachine) { 
        try {
            const machine = JSON.parse(storedMachine);
            // Verificar que la máquina tenga un nombre válido
            if (machine && machine.Nombre_Maquina && machine.Nombre_Maquina !== '') {
                this.selectedMachine = machine;
                console.log('Máquina cargada:', this.selectedMachine);
            } else {
                console.warn('Máquina guardada sin nombre válido:', machine);
                localStorage.removeItem('selectedMachine');
                this.snackBar.open('La máquina seleccionada no es válida. Seleccione otra.', 'Cerrar', { duration: 3000 });
            }
        } catch (e) {
            console.error('Error parsing stored machine:', e);
            localStorage.removeItem('selectedMachine');
            this.snackBar.open('Error al cargar la máquina seleccionada', 'Cerrar', { duration: 3000 });
        }
    }
    
    // Si no hay máquina seleccionada, intentar cargar desde el servicio
    if (!this.selectedMachine && this.user?.id) {
        this.cargarMaquinasDelTecnico();
    }
}
  private cargarMaquinasDelTecnico(): void {
    // Intentar cargar máquinas del técnico según su especialidad
    const especialidad = this.user?.Especialidad?.toLowerCase() || '';
    
    if (especialidad === 'ensamblador') {
        this.tecnicoService.getMaquinasEnsamblador(this.user!.id).subscribe({
            next: (maquinas) => {
                if (maquinas.length > 0) {
                    this.selectedMachine = maquinas[0];
                    localStorage.setItem('selectedMachine', JSON.stringify(this.selectedMachine));
                    this.snackBar.open(`Máquina "${this.selectedMachine.Nombre_Maquina}" seleccionada automáticamente`, 'Cerrar', { duration: 3000 });
                    this.cargarComponentesEnUso();
                }
            },
            error: () => {}
        });
    } else if (especialidad === 'comprobador') {
        this.tecnicoService.getMaquinasComprobador(this.user!.id).subscribe({
            next: (maquinas) => {
                if (maquinas.length > 0) {
                    this.selectedMachine = maquinas[0];
                    localStorage.setItem('selectedMachine', JSON.stringify(this.selectedMachine));
                    this.cargarComponentesEnUso();
                }
            },
            error: () => {}
        });
    } else if (especialidad === 'mantenimiento') {
        this.tecnicoService.getMaquinasMantenimiento(this.user!.id).subscribe({
            next: (maquinas) => {
                if (maquinas.length > 0) {
                    this.selectedMachine = maquinas[0];
                    localStorage.setItem('selectedMachine', JSON.stringify(this.selectedMachine));
                    this.cargarComponentesEnUso();
                }
            },
            error: () => {}
        });
    }
}
  cargarComponentesDisponibles(): void {
    this.cargandoDisponibles = true;
    const tipoParam = this.filtroTipo || undefined;
    this.tecnicoService.getComponentesDisponibles(tipoParam, this.currentPage + 1, this.pageSize).subscribe({
      next: (response) => { 
        this.componentesDisponibles = response.componentes; 
        this.totalDisponibles = response.total; 
        this.cargandoDisponibles = false; 
      },
      error: () => { 
        this.cargandoDisponibles = false; 
        this.snackBar.open('Error al cargar componentes disponibles', 'Cerrar', { duration: 3000 });
      }
    });
  }
  cargarComponentesEnUso(): void {
    this.cargandoEnUso = true;
    const machineId = this.selectedMachine?.ID_Maquina;
    
    console.log('=== CARGANDO COMPONENTES EN USO ===');
    console.log('Usuario ID:', this.user?.id);
    console.log('Máquina ID:', machineId);
    
    this.tecnicoService.getComponentesEnUso(this.user!.id, machineId).subscribe({
        next: (componentes) => { 
            console.log('Componentes recibidos (raw):', componentes);
            
            // Normalizar los datos - convertir 'id' a 'ID_Componente'
            this.componentesEnUso = componentes.map((comp: any) => ({
                ID_Componente: comp.ID_Componente || comp.id,  // ← clave: aceptar ambos formatos
                nombre: comp.nombre,
                tipo: comp.tipo,
                precio: comp.precio,
                Nombre_Maquina: comp.Nombre_Maquina || comp.nombre_maquina || this.selectedMachine?.Nombre_Maquina || 'N/A',
                fecha_asignacion: comp.fecha_asignacion
            }));
            
            console.log('Componentes en uso normalizados:', this.componentesEnUso);
            this.cargandoEnUso = false;
        },
        error: (err) => { 
            console.error('Error detallado al cargar componentes en uso:', err);
            this.cargandoEnUso = false; 
            this.snackBar.open('Error al cargar componentes en uso', 'Cerrar', { duration: 3000 });
        }
    });
}
componenteEnUso(componente: Componente): boolean {
    // Buscar por ID_Componente (normalizado)
    return this.componentesEnUso.some(c => 
        c.ID_Componente === componente.ID_Componente
    );
}
 usarComponente(componente: Componente): void {
    if (!this.selectedMachine) { 
        this.snackBar.open('Por favor, seleccione una máquina primero', 'Cerrar', { duration: 3000 });
        return; 
    }
    
    const machineName = this.selectedMachine.Nombre_Maquina || 'Máquina sin nombre';
    const machineId = this.selectedMachine.ID_Maquina;
    const componentId = componente.ID_Componente;
    
    console.log('=== USAR COMPONENTE ===');
    console.log('Componente ID:', componentId);
    console.log('Componente nombre:', componente.nombre);
    console.log('Máquina ID:', machineId);
    console.log('Máquina nombre:', machineName);
    console.log('Usuario ID:', this.user?.id);
    
    if (!machineId) {
        this.snackBar.open('La máquina seleccionada no tiene ID válido', 'Cerrar', { duration: 3000 });
        return;
    }
    
    if (!componentId) {
        this.snackBar.open('El componente no tiene ID válido', 'Cerrar', { duration: 3000 });
        return;
    }
    
    if (!confirm(`¿Está seguro de usar el componente "${componente.nombre}" en la máquina "${machineName}"?`)) return;
    
    const data = { 
        ID_Componente: componentId, 
        id: this.user!.id, 
        ID_Maquina: machineId 
    };
    
    console.log('Enviando datos:', data);
    
    this.tecnicoService.usarComponente(data).subscribe({
        next: (success) => { 
            console.log('Respuesta usarComponente:', success);
            if (success) { 
                this.snackBar.open('Componente usado correctamente', 'Éxito', { duration: 3000 });
                this.cargarComponentesDisponibles(); 
                this.cargarComponentesEnUso(); 
            } else { 
                this.snackBar.open('Error al usar el componente (respuesta false)', 'Cerrar', { duration: 3000 });
            } 
        },
        error: (err) => { 
            console.error('Error usando componente - detalles:', err);
            console.error('Status:', err.status);
            console.error('Message:', err.message);
            console.error('Error body:', err.error);
            this.snackBar.open(`Error: ${err.error?.message || 'Error al usar el componente'}`, 'Cerrar', { duration: 3000 });
        }
    });
}
  
liberarComponente(componente: Componente): void {
    if (!confirm(`¿Está seguro de liberar el componente ${componente.nombre}?`)) return;
    
    // Solo enviamos idComponente
    const data: LiberarComponenteData = { 
        idComponente: componente.ID_Componente
    };
    
    console.log('Liberando componente - Datos enviados:', data);
    
    this.tecnicoService.liberarComponente(data).subscribe({
        next: (response) => { 
            console.log('Respuesta liberarComponente:', response);
            if (response) { 
                this.snackBar.open('Componente liberado correctamente', 'Éxito', { duration: 3000 });
                this.cargarComponentesDisponibles(); 
                this.cargarComponentesEnUso(); 
            } else { 
                this.snackBar.open('Error al liberar el componente', 'Cerrar', { duration: 3000 });
            } 
        },
        error: (err) => { 
            console.error('Error al liberar componente:', err);
            this.snackBar.open(`Error: ${err.error?.message || 'Error al liberar el componente'}`, 'Cerrar', { duration: 3000 });
        }
    });
}
  
  onPageChangeDisponibles(event: PageEvent): void {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.cargarComponentesDisponibles();
  }
regresar(): void {
    console.log('=== REGRESAR - DIAGNÓSTICO ===');
    
    // Intentar obtener usuario de múltiples fuentes
    let user = this.authService.getCurrentUser();
    console.log('Usuario desde AuthService:', user);
    
    let especialidad = user?.Especialidad;
    
    // Intentar desde localStorage directamente
    if (!especialidad) {
        try {
            const storedUser = localStorage.getItem('currentUser');
            if (storedUser) {
                const parsedUser = JSON.parse(storedUser);
                especialidad = parsedUser.Especialidad || parsedUser.especialidad;
                console.log('Especialidad desde localStorage:', especialidad);
                
                // También actualizar el usuario en el servicio si falta la especialidad
                if (parsedUser && !user?.Especialidad) {
                    this.user = parsedUser;
                    console.log('Usuario actualizado desde localStorage:', this.user);
                }
            }
        } catch (e) {
            console.error('Error parsing stored user:', e);
        }
    }
    
    // Si el usuario es técnico pero no tiene especialidad, intentar obtenerla del tipo
    if (!especialidad && user?.tipo === 'Tecnico') {
        // Esto es un fallback - deberías obtener la especialidad del backend
        console.warn('Usuario técnico sin especialidad definida');
        // Podrías redirigir a una página genérica o mostrar un mensaje
        this.snackBar.open('Error: Perfil de técnico incompleto. Contacte al administrador.', 'Cerrar', { duration: 5000 });
        this.router.navigate(['/tecnico/ensamblador']);
        return;
    }
    
    // Mapeo de rutas
    const rutaMap: { [key: string]: string } = {
        'Ensamblador': '/tecnico/ensamblador',
        'ensamblador': '/tecnico/ensamblador',
        'Comprobador': '/tecnico/comprobador',
        'comprobador': '/tecnico/comprobador',
        'Mantenimiento': '/tecnico/mantenimiento',
        'mantenimiento': '/tecnico/mantenimiento'
    };
    
    const rutaDestino = especialidad ? (rutaMap[especialidad] || '/tecnico/ensamblador') : '/tecnico/ensamblador';
    
    console.log('Especialidad final:', especialidad);
    console.log('Redirigiendo a:', rutaDestino);
    
    // Limpiar la máquina seleccionada
    localStorage.removeItem('selectedMachine');
    
    // Navegar
    this.router.navigate([rutaDestino]).then(success => {
        if (!success) {
            console.error('Error en navegación a:', rutaDestino);
            this.router.navigate(['/tecnico/ensamblador']);
        }
    });
} 
}