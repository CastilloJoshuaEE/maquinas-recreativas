/**
 * @fileoverview Componente de Sección de Perfil
 * @description Muestra la información del perfil del usuario
 * @component ProfileSectionComponent
 */

import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { User } from '@core/models/user.model';

export interface AdditionalField {
  label: string;
  value: string;
}

@Component({
  selector: 'app-profile-section',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatIconModule],
  templateUrl: './profile-section.html',
  styleUrls: ['./profile-section.css']
})
export class ProfileSectionComponent {
  @Input() user: User | null = null;
  @Input() additionalFields: AdditionalField[] = [];
}