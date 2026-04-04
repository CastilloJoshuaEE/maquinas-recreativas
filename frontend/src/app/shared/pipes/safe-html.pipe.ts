/**
 * @fileoverview Pipe Safe HTML
 * @description Sanitiza HTML para prevenir XSS
 * @pipe SafeHtmlPipe
 */

import { Pipe, PipeTransform, inject } from '@angular/core';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

@Pipe({
  name: 'safeHtml',
  standalone: true
})
export class SafeHtmlPipe implements PipeTransform {
  private sanitizer = inject(DomSanitizer);
  
  transform(value: string): SafeHtml {
    return this.sanitizer.sanitize(8, value) || '';
  }
}