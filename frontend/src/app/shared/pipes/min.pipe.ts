// shared/pipes/min.pipe.ts
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'min',
  standalone: true
})
export class MinPipe implements PipeTransform {
  transform(value: number[]): number {
    if (!value || value.length === 0) return 0;
    return Math.min(...value);
  }
}