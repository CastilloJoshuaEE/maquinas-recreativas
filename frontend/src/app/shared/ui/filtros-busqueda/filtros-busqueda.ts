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

export interface FilterConfig { key: string; label: string; type: 'text' | 'select' | 'date' | 'date-range' | 'number'; options?: Array<{ value: any; label: string }>; placeholder?: string; defaultValue?: any; }

@Component({
  selector: 'app-filtros-busqueda',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatSelectModule, MatDatepickerModule, MatNativeDateModule, MatButtonModule, MatIconModule, MatChipsModule],
  templateUrl: './filtros-busqueda.html',
  styleUrls: ['./filtros-busqueda.css']
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
  
  ngOnInit(): void { this.initForm(); this.setupValueChanges(); }
  ngOnDestroy(): void { this.destroy$.next(); this.destroy$.complete(); }
  
  private initForm(): void {
    const group: any = {};
    for (const filter of this.filters) {
      if (filter.type === 'date-range') { group[`${filter.key}_inicio`] = [filter.defaultValue?.inicio || '']; group[`${filter.key}_fin`] = [filter.defaultValue?.fin || '']; }
      else { group[filter.key] = [filter.defaultValue || '']; }
    }
    this.filtrosForm = this.fb.group(group);
  }
  
  private setupValueChanges(): void {
    this.filtrosForm.valueChanges.pipe(debounceTime(this.debounceTime), distinctUntilChanged(), takeUntil(this.destroy$)).subscribe(values => { this.updateActiveFilters(); this.onFilterChange.emit(this.getFilterValues()); });
  }
  
  getFilterValues(): any {
    const values: any = {};
    for (const filter of this.filters) {
      if (filter.type === 'date-range') {
        const inicio = this.filtrosForm.get(`${filter.key}_inicio`)?.value;
        const fin = this.filtrosForm.get(`${filter.key}_fin`)?.value;
        if (inicio || fin) values[filter.key] = { inicio, fin };
      } else {
        const value = this.filtrosForm.get(filter.key)?.value;
        if (value && value !== '') values[filter.key] = value;
      }
    }
    return values;
  }
  
  getFilterValue(key: string): any { return this.filtrosForm.get(key)?.value; }
  clearFilter(key: string): void { this.filtrosForm.get(key)?.setValue(''); }
  applyFilters(): void { this.onSearch.emit(this.getFilterValues()); }
  
  resetFilters(): void {
    for (const filter of this.filters) {
      if (filter.type === 'date-range') { this.filtrosForm.get(`${filter.key}_inicio`)?.setValue(''); this.filtrosForm.get(`${filter.key}_fin`)?.setValue(''); }
      else { this.filtrosForm.get(filter.key)?.setValue(''); }
    }
    this.applyFilters();
  }
  
  removeFilter(key: string): void { this.clearFilter(key); this.applyFilters(); }
  
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
          this.activeFilters.push({ key: filter.key, label: filter.label, displayValue });
        }
      } else {
        const value = this.filtrosForm.get(filter.key)?.value;
        if (value && value !== '') {
          let displayValue = value;
          if (filter.type === 'select' && filter.options) { const option = filter.options.find(opt => opt.value === value); if (option) displayValue = option.label; }
          else if (filter.type === 'date') displayValue = new Date(value).toLocaleDateString();
          this.activeFilters.push({ key: filter.key, label: filter.label, displayValue });
        }
      }
    }
    this.hasChanges = this.activeFilters.length > 0;
  }
  
  toggleCollapse(): void { this.collapsed = !this.collapsed; }
}