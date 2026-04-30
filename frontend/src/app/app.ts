/**
 * @fileoverview Componente raíz de la aplicación
 * @description Componente principal que contiene el router outlet y la estructura base
 * @component AppComponent
 */

import { Component, OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  templateUrl: './app.html',
  styleUrls: ['./app.css']
})
export class AppComponent implements OnInit {
  title = 'Recrea Sys';
  
  ngOnInit(): void {
    // Inicialización de la aplicación
    console.log('Recrea Sys - Aplicación iniciada');
  }
}