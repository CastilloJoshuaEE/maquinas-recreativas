/**
 * @fileoverview Componente de Tabla Genérica
 * @description Tabla reutilizable con paginación, ordenamiento y acciones
 * @component TablaGenericaComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTableModule, MatTableDataSource } from '@angular/material/table';
import { MatPaginatorModule, MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSortModule, MatSort } from '@angular/material/sort';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';

export interface ColumnDefinition { key: string; header: string; type?: 'text' | 'number' | 'date' | 'currency' | 'badge' | 'actions'; sortable?: boolean; width?: string; format?: string; badgeClass?: (value: any) => string; badgeText?: (value: any) => string; }
export interface ActionDefinition { key: string; label: string; icon: string; color: 'primary' | 'accent' | 'warn'; disabled?: (row: any) => boolean; hidden?: (row: any) => boolean; }

@Component({
  selector: 'app-tabla-generica',
  standalone: true,
  imports: [CommonModule, MatTableModule, MatPaginatorModule, MatSortModule, MatButtonModule, MatIconModule, MatMenuModule],
  templateUrl: './tabla-generica.html',
  styleUrls: ['./tabla-generica.css']
})
export class TablaGenericaComponent<T = any> implements OnInit, AfterViewInit {
  @Input() columns: ColumnDefinition[] = [];
  @Input() data: T[] = [];
  @Input() actions: ActionDefinition[] = [];
  @Input() useActionsMenu = false;
  @Input() showToolbar = true;
  @Input() showPagination = true;
  @Input() rowClickable = false;
  @Input() emptyMessage = 'No hay datos disponibles';
  @Input() pageSize = 10;
  @Input() pageSizeOptions = [5, 10, 20, 50, 100];
  @Input() totalItems = 0;
  @Input() sortActive = '';
  @Input() sortDirection: 'asc' | 'desc' = 'asc';
  
  @Output() onPageChange = new EventEmitter<PageEvent>();
  @Output() onSortChange = new EventEmitter<{ active: string; direction: string }>();
  @Output() onAction = new EventEmitter<{ action: string; row: T }>();
  @Output() onRowClick = new EventEmitter<T>();
  
  dataSource = new MatTableDataSource<T>([]);
  displayedColumns: string[] = [];
  
  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;
  
  ngOnInit(): void { this.displayedColumns = this.columns.map(col => col.key); this.dataSource.data = this.data; }
  ngAfterViewInit(): void { this.dataSource.paginator = this.paginator; this.dataSource.sort = this.sort; }
  ngOnChanges(): void { this.dataSource.data = this.data; }
  
  onPageChange(event: PageEvent): void { this.onPageChange.emit(event); }
  onSortChange(event: any): void { this.onSortChange.emit({ active: event.active, direction: event.direction }); }
  onActionClick(actionKey: string, row: T): void { this.onAction.emit({ action: actionKey, row }); }
  onRowClick(row: T): void { if (this.rowClickable) this.onRowClick.emit(row); }
  getVisibleActions(row: T): ActionDefinition[] { return this.actions.filter(action => !action.hidden || !action.hidden(row)); }
}