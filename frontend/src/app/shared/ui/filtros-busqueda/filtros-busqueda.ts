/**
 * @fileoverview Componente de Filtros de Búsqueda
 * @description Componente reutilizable para filtros avanzados de búsqueda
 * @component FiltrosBusquedaComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { Subject, debounceTime, distinctUntilChanged, takeUntil } from 'rxjs';

export interface FilterConfig {
  key: string;
  label: string;
  type: 'text' | 'select' | 'date' | 'date-range' | 'number';
  options?: Array<{ value: any; label: string }>;
  placeholder?: string;
  defaultValue?: any;
}

@Component({
  selector: 'app-filtros-busqueda',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatButtonModule,
    MatIconModule,
    MatChipsModule
  ],
  template: `
    <div class="filtros-container" [class.collapsed]="collapsed">
      <div class="filtros-header" (click)="toggleCollapse()">
        <h3>
          <mat-icon>filter_list</mat-icon>
          Filtros de búsqueda
        </h3>
        <button mat-icon-button type="button">
          <mat-icon>{{ collapsed ? 'expand_more' : 'expand_less' }}</mat-icon>
        </button>
      </div>
      
      <div class="filtros-body" *ngIf="!collapsed">
        <form [formGroup]="filtrosForm" class="filtros-form">
          <div *ngFor="let filter of filters" class="filter-field" [ngClass]="filter.type">
            <!-- Campo de texto -->
            <mat-form-field appearance="outline" *ngIf="filter.type === 'text'">
              <mat-label>{{ filter.label }}</mat-label>
              <input matInput [formControlName]="filter.key" [placeholder]="filter.placeholder || ''">
              <mat-icon matSuffix *ngIf="getFilterValue(filter.key)" (click)="clearFilter(filter.key)">close</mat-icon>
            </mat-form-field>
            
            <!-- Select -->
            <mat-form-field appearance="outline" *ngIf="filter.type === 'select'">
              <mat-label>{{ filter.label }}</mat-label>
              <mat-select [formControlName]="filter.key">
                <mat-option value="">Todos</mat-option>
                <mat-option *ngFor="let option of filter.options" [value]="option.value">
                  {{ option.label }}
                </mat-option>
              </mat-select>
              <mat-icon matSuffix *ngIf="getFilterValue(filter.key)" (click)="clearFilter(filter.key)">close</mat-icon>
            </mat-form-field>
            
            <!-- Fecha -->
            <mat-form-field appearance="outline" *ngIf="filter.type === 'date'">
              <mat-label>{{ filter.label }}</mat-label>
              <input matInput [matDatepicker]="picker" [formControlName]="filter.key">
              <mat-datepicker-toggle matSuffix [for]="picker"></mat-datepicker-toggle>
              <mat-datepicker #picker></mat-datepicker>
              <mat-icon matSuffix *ngIf="getFilterValue(filter.key)" (click)="clearFilter(filter.key)">close</mat-icon>
            </mat-form-field>
            
            <!-- Rango de fechas -->
            <div class="date-range" *ngIf="filter.type === 'date-range'">
              <mat-form-field appearance="outline">
                <mat-label>Desde</mat-label>
                <input matInput [matDatepicker]="startPicker" [formControlName]="filter.key + '_inicio'">
                <mat-datepicker-toggle matSuffix [for]="startPicker"></mat-datepicker-toggle>
                <mat-datepicker #startPicker></mat-datepicker>
              </mat-form-field>
              <mat-form-field appearance="outline">
                <mat-label>Hasta</mat-label>
                <input matInput [matDatepicker]="endPicker" [formControlName]="filter.key + '_fin'">
                <mat-datepicker-toggle matSuffix [for]="endPicker"></mat-datepicker-toggle>
                <mat-datepicker #endPicker></mat-datepicker>
              </mat-form-field>
            </div>
            
            <!-- Número -->
            <mat-form-field appearance="outline" *ngIf="filter.type === 'number'">
              <mat-label>{{ filter.label }}</mat-label>
              <input matInput type="number" [formControlName]="filter.key" [placeholder]="filter.placeholder || ''">
              <mat-icon matSuffix *ngIf="getFilterValue(filter.key)" (click)="clearFilter(filter.key)">close</mat-icon>
            </mat-form-field>
          </div>
          
          <div class="filter-actions">
            <button mat-raised-button color="primary" type="button" (click)="applyFilters()" [disabled]="!hasChanges">
              <mat-icon>search</mat-icon>
              Buscar
            </button>
            <button mat-raised-button type="button" (click)="resetFilters()">
              <mat-icon>clear</mat-icon>
              Limpiar
            </button>
          </div>
        </form>
        
        <!-- Filtros activos -->
        <div class="active-filters" *ngIf="activeFilters.length > 0">
          <span class="active-label">Filtros activos:</span>
          <mat-chip-listbox>
            <mat-chip-option *ngFor="let filter of activeFilters" (removed)="removeFilter(filter.key)">
              {{ filter.label }}: {{ filter.displayValue }}
              <mat-icon matChipRemove>cancel</mat-icon>
            </mat-chip-option>
          </mat-chip-listbox>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .filtros-container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      margin-bottom: 1rem;
      overflow: hidden;
      transition: all 0.3s ease;
    }
    
    .filtros-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.5rem;
      cursor: pointer;
      background: rgba(0, 0, 0, 0.2);
    }
    
    .filtros-header h3 {
      margin: 0;
      color: white;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1rem;
    }
    
    .filtros-header h3 mat-icon {
      font-size: 1.2rem;
    }
    
    .filtros-body {
      padding: 1.5rem;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .filtros-form {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1rem;
      align-items: start;
    }
    
    .filter-field mat-form-field {
      width: 100%;
    }
    
    .date-range {
      display: flex;
      gap: 1rem;
    }
    
    .date-range mat-form-field {
      flex: 1;
    }
    
    .filter-actions {
      display: flex;
      gap: 0.5rem;
      align-items: center;
      margin-top: 0.5rem;
    }
    
    .active-filters {
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
    
    .active-label {
      color: white;
      font-size: 0.85rem;
    }
    
    @media (max-width: 768px) {
      .filtros-form {
        grid-template-columns: 1fr;
      }
      
      .date-range {
        flex-direction: column;
        gap: 0;
      }
      
      .filter-actions {
        flex-direction: column;
      }
      
      .filter-actions button {
        width: 100%;
      }
    }
  `]
})
export class FiltrosBusquedaComponent implements OnInit, OnDestroy {
  @Input() filters: FilterConfig[] = [];
  @Input() debounceTime = 300;
  @Output() onFilterChange = new EventEmitter<any>();
  @Output() onSearch = new EventEmitter<any>();
  
  private fb = inject(FormBuilder);
  private destroy$ = new Subject<void>();
  
  filtrosForm!: FormGroup;
  collapsed = false;
  hasChanges = false;
  activeFilters: Array<{ key: string; label: string; displayValue: string }> = [];
  
  ngOnInit(): void {
    this.initForm();
    this.setupValueChanges();
  }
  
  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
  
  private initForm(): void {
    const group: any = {};
    
    for (const filter of this.filters) {
      if (filter.type === 'date-range') {
        group[`${filter.key}_inicio`] = [filter.defaultValue?.inicio || ''];
        group[`${filter.key}_fin`] = [filter.defaultValue?.fin || ''];
      } else {
        group[filter.key] = [filter.defaultValue || ''];
      }
    }
    
    this.filtrosForm = this.fb.group(group);
  }
  
  private setupValueChanges(): void {
    this.filtrosForm.valueChanges
      .pipe(debounceTime(this.debounceTime), distinctUntilChanged(), takeUntil(this.destroy$))
      .subscribe(values => {
        this.updateActiveFilters();
        this.onFilterChange.emit(this.getFilterValues());
      });
  }
  
  getFilterValues(): any {
    const values: any = {};
    
    for (const filter of this.filters) {
      if (filter.type === 'date-range') {
        const inicio = this.filtrosForm.get(`${filter.key}_inicio`)?.value;
        const fin = this.filtrosForm.get(`${filter.key}_fin`)?.value;
        if (inicio || fin) {
          values[filter.key] = { inicio, fin };
        }
      } else {
        const value = this.filtrosForm.get(filter.key)?.value;
        if (value && value !== '') {
          values[filter.key] = value;
        }
      }
    }
    
    return values;
  }
  
  getFilterValue(key: string): any {
    return this.filtrosForm.get(key)?.value;
  }
  
  clearFilter(key: string): void {
    this.filtrosForm.get(key)?.setValue('');
  }
  
  applyFilters(): void {
    this.onSearch.emit(this.getFilterValues());
  }
  
  resetFilters(): void {
    for (const filter of this.filters) {
      if (filter.type === 'date-range') {
        this.filtrosForm.get(`${filter.key}_inicio`)?.setValue('');
        this.filtrosForm.get(`${filter.key}_fin`)?.setValue('');
      } else {
        this.filtrosForm.get(filter.key)?.setValue('');
      }
    }
    this.applyFilters();
  }
  
  removeFilter(key: string): void {
    this.clearFilter(key);
    this.applyFilters();
  }
  
  private updateActiveFilters(): void {
    this.activeFilters = [];
    
    for (const filter of this.filters) {
      if (filter.type === 'date-range') {
        const inicio = this.filtrosForm.get(`${filter.key}_inicio`)?.value;
        const fin = this.filtrosForm.get(`${filter.key}_fin`)?.value;
        if (inicio || fin) {
          let displayValue = '';
          if (inicio && fin) displayValue = `${inicio.toLocaleDateString()} - ${fin.toLocaleDateString()}`;
          else if (inicio) displayValue = `Desde ${inicio.toLocaleDateString()}`;
          else if (fin) displayValue = `Hasta ${fin.toLocaleDateString()}`;
          
          this.activeFilters.push({
            key: filter.key,
            label: filter.label,
            displayValue
          });
        }
      } else {
        const value = this.filtrosForm.get(filter.key)?.value;
        if (value && value !== '') {
          let displayValue = value;
          if (filter.type === 'select' && filter.options) {
            const option = filter.options.find(opt => opt.value === value);
            if (option) displayValue = option.label;
          } else if (filter.type === 'date') {
            displayValue = new Date(value).toLocaleDateString();
          }
          
          this.activeFilters.push({
            key: filter.key,
            label: filter.label,
            displayValue
          });
        }
      }
    }
    
    this.hasChanges = this.activeFilters.length > 0;
  }
  
  toggleCollapse(): void {
    this.collapsed = !this.collapsed;
  }
}