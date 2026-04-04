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

export interface ColumnDefinition {
  key: string;
  header: string;
  type?: 'text' | 'number' | 'date' | 'currency' | 'badge' | 'actions';
  sortable?: boolean;
  width?: string;
  format?: string;
  badgeClass?: (value: any) => string;
  badgeText?: (value: any) => string;
}

export interface ActionDefinition {
  key: string;
  label: string;
  icon: string;
  color: 'primary' | 'accent' | 'warn';
  disabled?: (row: any) => boolean;
  hidden?: (row: any) => boolean;
}

@Component({
  selector: 'app-tabla-generica',
  standalone: true,
  imports: [
    CommonModule,
    MatTableModule,
    MatPaginatorModule,
    MatSortModule,
    MatButtonModule,
    MatIconModule,
    MatMenuModule
  ],
  template: `
    <div class="tabla-generica-container">
      <!-- Barra de herramientas -->
      <div class="toolbar" *ngIf="showToolbar">
        <div class="toolbar-left">
          <ng-content select="[toolbar-left]"></ng-content>
        </div>
        <div class="toolbar-right">
          <ng-content select="[toolbar-right]"></ng-content>
        </div>
      </div>
      
      <!-- Tabla -->
      <div class="table-responsive">
        <table mat-table [dataSource]="dataSource" matSort [matSortActive]="sortActive" [matSortDirection]="sortDirection" (matSortChange)="onSortChange($event)">
          
          <!-- Columnas dinámicas -->
          <ng-container *ngFor="let col of columns" [matColumnDef]="col.key">
            <th mat-header-cell *matHeaderCellDef [style.width]="col.width" mat-sort-header *ngIf="col.sortable !== false">
              {{ col.header }}
            </th>
            <th mat-header-cell *matHeaderCellDef [style.width]="col.width" *ngIf="col.sortable === false">
              {{ col.header }}
            </th>
            <td mat-cell *matCellDef="let row">
              <!-- Texto -->
              <ng-container *ngIf="col.type === 'text' || !col.type">
                {{ row[col.key] }}
              </ng-container>
              
              <!-- Número -->
              <ng-container *ngIf="col.type === 'number'">
                {{ row[col.key] | number }}
              </ng-container>
              
              <!-- Fecha -->
              <ng-container *ngIf="col.type === 'date'">
                {{ row[col.key] | date: (col.format || 'dd/MM/yyyy HH:mm') }}
              </ng-container>
              
              <!-- Moneda -->
              <ng-container *ngIf="col.type === 'currency'">
                {{ row[col.key] | currency: 'USD':'symbol':'1.2-2' }}
              </ng-container>
              
              <!-- Badge -->
              <ng-container *ngIf="col.type === 'badge'">
                <span class="badge" [ngClass]="col.badgeClass ? col.badgeClass(row[col.key]) : ''">
                  {{ col.badgeText ? col.badgeText(row[col.key]) : row[col.key] }}
                </span>
              </ng-container>
              
              <!-- Acciones -->
              <ng-container *ngIf="col.type === 'actions'">
                <div class="actions-cell">
                  <!-- Botones individuales -->
                  <ng-container *ngIf="!useActionsMenu">
                    <button mat-icon-button 
                            *ngFor="let action of actions"
                            [matTooltip]="action.label"
                            [color]="action.color"
                            (click)="onActionClick(action.key, row)"
                            [disabled]="action.disabled ? action.disabled(row) : false"
                            *ngIf="!action.hidden || !action.hidden(row)">
                      <mat-icon>{{ action.icon }}</mat-icon>
                    </button>
                  </ng-container>
                  
                  <!-- Menú de acciones -->
                  <ng-container *ngIf="useActionsMenu">
                    <button mat-icon-button [matMenuTriggerFor]="actionsMenu" [disabled]="getVisibleActions(row).length === 0">
                      <mat-icon>more_vert</mat-icon>
                    </button>
                    <mat-menu #actionsMenu="matMenu">
                      <button mat-menu-item 
                              *ngFor="let action of getVisibleActions(row)"
                              (click)="onActionClick(action.key, row)">
                        <mat-icon [color]="action.color">{{ action.icon }}</mat-icon>
                        <span>{{ action.label }}</span>
                      </button>
                    </mat-menu>
                  </ng-container>
                </div>
              </ng-container>
            </td>
          </ng-container>
          
          <tr mat-header-row *matHeaderRowDef="displayedColumns"></tr>
          <tr mat-row *matRowDef="let row; columns: displayedColumns;" [class.clickable]="rowClickable" (click)="onRowClick(row)"></tr>
          
          <!-- Fila sin datos -->
          <tr class="mat-row" *matNoDataRow>
            <td class="mat-cell" [attr.colspan]="displayedColumns.length">
              <div class="no-data">
                <mat-icon>info</mat-icon>
                <p>{{ emptyMessage }}</p>
              </div>
            </td>
          </tr>
        </table>
      </div>
      
      <!-- Paginación -->
      <mat-paginator *ngIf="showPagination"
        [length]="totalItems"
        [pageSize]="pageSize"
        [pageSizeOptions]="pageSizeOptions"
        (page)="onPageChange($event)"
        showFirstLastButtons>
      </mat-paginator>
    </div>
  `,
  styles: [`
    .tabla-generica-container {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .toolbar {
      padding: 1rem;
      border-bottom: 1px solid #e0e0e0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .table-responsive {
      overflow-x: auto;
    }
    
    table {
      width: 100%;
    }
    
    .clickable tr.mat-row {
      cursor: pointer;
    }
    
    .clickable tr.mat-row:hover {
      background: #f5f5f5;
    }
    
    .badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .actions-cell {
      display: flex;
      gap: 0.25rem;
      flex-wrap: wrap;
    }
    
    .no-data {
      text-align: center;
      padding: 2rem;
      color: #999;
    }
    
    .no-data mat-icon {
      font-size: 3rem;
      width: auto;
      height: auto;
      margin-bottom: 0.5rem;
    }
    
    @media (max-width: 768px) {
      .toolbar {
        flex-direction: column;
        align-items: stretch;
      }
    }
  `]
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
  
  ngOnInit(): void {
    this.displayedColumns = this.columns.map(col => col.key);
    this.dataSource.data = this.data;
  }
  
  ngAfterViewInit(): void {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }
  
  ngOnChanges(): void {
    this.dataSource.data = this.data;
  }
  
  onPageChange(event: PageEvent): void {
    this.onPageChange.emit(event);
  }
  
  onSortChange(event: any): void {
    this.onSortChange.emit({ active: event.active, direction: event.direction });
  }
  
  onActionClick(actionKey: string, row: T): void {
    this.onAction.emit({ action: actionKey, row });
  }
  
  onRowClick(row: T): void {
    if (this.rowClickable) {
      this.onRowClick.emit(row);
    }
  }
  
  getVisibleActions(row: T): ActionDefinition[] {
    return this.actions.filter(action => !action.hidden || !action.hidden(row));
  }
}