/**
 * @fileoverview Pipe Fecha Relativa
 * @description Muestra fechas en formato relativo (hace 5 minutos, ayer, etc.)
 * @pipe FechaRelativaPipe
 */

import { Pipe, PipeTransform } from '@angular/core';
import * as moment from 'moment';

@Pipe({
  name: 'fechaRelativa',
  standalone: true
})
export class FechaRelativaPipe implements PipeTransform {
  
  transform(value: string | Date): string {
    if (!value) return '';
    
    const fecha = moment(value);
    const ahora = moment();
    
    const minutos = ahora.diff(fecha, 'minutes');
    const horas = ahora.diff(fecha, 'hours');
    const dias = ahora.diff(fecha, 'days');
    
    if (minutos < 1) return 'Justo ahora';
    if (minutos < 60) return `Hace ${minutos} minuto${minutos !== 1 ? 's' : ''}`;
    if (horas < 24) return `Hace ${horas} hora${horas !== 1 ? 's' : ''}`;
    if (dias === 1) return 'Ayer';
    if (dias < 7) return `Hace ${dias} día${dias !== 1 ? 's' : ''}`;
    
    return fecha.format('DD/MM/YYYY HH:mm');
  }
}