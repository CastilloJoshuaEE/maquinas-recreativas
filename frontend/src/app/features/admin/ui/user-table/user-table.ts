/**
 * @fileoverview Tabla de Usuarios
 * @description Componente reutilizable para mostrar lista de usuarios con acciones
 * @component UserTableComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTableModule, MatTableDataSource } from '@angular/material/table';
import { MatPaginatorModule, MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSortModule, MatSort } from '@angular/material/sort';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatMenuModule } from '@angular/material/menu';
import { User } from '@core/models/user.model';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
@Component({
  selector: 'app-user-table',
  standalone: true,
  imports: [
    CommonModule,
    MatTableModule,
    MatPaginatorModule,
    MatSortModule,
    MatButtonModule,
    MatIconModule,
    MatTooltipModule,
    MatMenuModule,MatProgressSpinnerModule
  ],
  templateUrl: './user-table.html',
  styleUrls: ['./user-table.css']
})
export class UserTableComponent implements OnInit, AfterViewInit {
  @Input() users: User[] = [];
  @Input() loading = false;
  @Input() totalItems = 0;
  @Input() pageSize = 10;
  @Input() pageSizeOptions = [5, 10, 20, 50];
  @Input() showActions = true;
  @Input() showHistorial = true;
  @Input() showEdit = true;
  @Input() showEstado = true;
  @Input() showDelete = true;

  @Output() onPageChange = new EventEmitter<PageEvent>();
  @Output() onViewHistorial = new EventEmitter<User>();
  @Output() onEdit = new EventEmitter<User>();
  @Output() onChangeEstado = new EventEmitter<User>();
  @Output() onDelete = new EventEmitter<User>();

  dataSource = new MatTableDataSource<User>([]);
  displayedColumns: string[] = ['ci', 'nombre', 'email', 'usuario_asignado', 'estado', 'tipo'];

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;

  ngOnInit(): void {
    this.dataSource.data = this.users;
    if (this.showActions) {
      this.displayedColumns.push('acciones');
    }
  }

  ngAfterViewInit(): void {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  ngOnChanges(): void {
    this.dataSource.data = this.users;
  }

  onPageChangeEvent(event: PageEvent): void {
    this.onPageChange.emit(event);
  }

  getEstadoClass(estado: string): string {
    switch (estado) {
      case 'Activo': return 'estado-activo';
      case 'Inhabilitado': return 'estado-inhabilitado';
      case 'Pendiente de asignacion': return 'estado-pendiente';
      default: return '';
    }
  }

  getTipoClass(tipo: string): string {
    switch (tipo) {
      case 'Administrador': return 'tipo-admin';
      case 'Contabilidad': return 'tipo-contabilidad';
      case 'Logistica': return 'tipo-logistica';
      case 'Tecnico': return 'tipo-tecnico';
      default: return '';
    }
  }

  viewHistorial(user: User): void {
    this.onViewHistorial.emit(user);
  }

  editUser(user: User): void {
    this.onEdit.emit(user);
  }

  changeEstado(user: User): void {
    this.onChangeEstado.emit(user);
  }

  deleteUser(user: User): void {
    this.onDelete.emit(user);
  }
}