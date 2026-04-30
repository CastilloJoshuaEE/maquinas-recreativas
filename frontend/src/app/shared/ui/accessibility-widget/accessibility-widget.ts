import { Component, signal, computed, inject, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSliderModule } from '@angular/material/slider';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSelectModule } from '@angular/material/select';
import { MatTooltipModule } from '@angular/material/tooltip';
import { AccessibilitySettingsService , FontSize, FontFamily, ColorFilter } from '../accessibility-settings/accessibility-settings';
import { TextSpacing } from '../accessibility-settings/accessibility-settings';
@Component({
  selector: 'app-accessibility-widget',
  standalone: true,
  imports: [
    CommonModule, FormsModule , MatButtonModule, MatIconModule,
    MatSliderModule, MatSlideToggleModule, MatSelectModule, MatTooltipModule
  ],
  templateUrl: './accessibility-widget.html',
  styleUrls: ['./accessibility-widget.css']
})
export class AccessibilityWidgetComponent implements OnInit, OnDestroy {
  private accessibilityService = inject(AccessibilitySettingsService);
  
  isOpen = signal(false);
  activeTab = signal<'visual' | 'reading' | 'navigation'>('visual');
  
  settings = this.accessibilityService.settings;
  
  // Opciones para selects
  fontSizeOptions = [
    { value: 'small', label: 'Pequeño', icon: 'format_size' },
    { value: 'normal', label: 'Normal', icon: 'format_size' },
    { value: 'large', label: 'Grande', icon: 'format_size' },
    { value: 'x-large', label: 'Muy grande', icon: 'format_size' }
  ];
  
  fontFamilyOptions = [
    { value: 'default', label: 'Predeterminada', example: 'Aa' },
    { value: 'opendyslexic', label: 'OpenDyslexic', example: 'Aa' },
    { value: 'arial', label: 'Arial', example: 'Aa' },
    { value: 'verdana', label: 'Verdana', example: 'Aa' }
  ];
  
  colorFilterOptions = [
    { value: 'none', label: 'Normal', icon: 'visibility' },
    { value: 'protanopia', label: 'Protanopia (Rojo-Verde)', icon: 'color_lens' },
    { value: 'deuteranopia', label: 'Deuteranopia (Rojo-Verde)', icon: 'color_lens' },
    { value: 'tritanopia', label: 'Tritanopia (Azul-Amarillo)', icon: 'color_lens' },
    { value: 'grayscale', label: 'Escala de grises', icon: 'tonality' }
  ];
  
  textSpacingOptions = [
    { value: 'normal', label: 'Normal', spacing: 1 },
    { value: 'large', label: 'Grande', spacing: 1.5 },
    { value: 'x-large', label: 'Muy grande', spacing: 2 }
  ];
  
  private keyboardHandler = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && this.isOpen()) {
      this.isOpen.set(false);
    }
  };

  ngOnInit(): void {
    document.addEventListener('keydown', this.keyboardHandler);
  }

  ngOnDestroy(): void {
    document.removeEventListener('keydown', this.keyboardHandler);
  }

  toggleWidget(): void {
    this.isOpen.update(v => !v);
  }

  setActiveTab(tab: 'visual' | 'reading' | 'navigation'): void {
    this.activeTab.set(tab);
  }

  // Acciones visuales
  toggleTheme(): void {
    this.accessibilityService.toggleTheme();
  }

  toggleHighContrast(): void {
    this.accessibilityService.toggleHighContrast();
  }

  increaseFontSize(): void {
    this.accessibilityService.increaseFontSize();
  }

  decreaseFontSize(): void {
    this.accessibilityService.decreaseFontSize();
  }

  setFontSize(size: FontSize): void {
    this.accessibilityService.updateSettings({ fontSize: size });
  }

  setFontFamily(family: FontFamily): void {
    this.accessibilityService.updateSettings({ fontFamily: family });
  }

  setColorFilter(filter: ColorFilter): void {
    this.accessibilityService.updateSettings({ colorFilter: filter });
  }

setTextSpacing(spacing: TextSpacing): void {
  this.accessibilityService.updateSettings({ textSpacing: spacing });
}

  toggleReduceAnimations(): void {
    this.accessibilityService.updateSettings({ 
      reduceAnimations: !this.settings().reduceAnimations 
    });
  }

  toggleFocusIndicator(): void {
    this.accessibilityService.updateSettings({ 
      focusIndicator: !this.settings().focusIndicator 
    });
  }

  updateLineHeight(value: number): void {
    this.accessibilityService.updateSettings({ lineHeight: value });
  }

  updateLetterSpacing(value: number): void {
    this.accessibilityService.updateSettings({ letterSpacing: value });
  }

  updateWordSpacing(value: number): void {
    this.accessibilityService.updateSettings({ wordSpacing: value });
  }

  resetSettings(): void {
    this.accessibilityService.resetToDefault();
  }

  getFontSizeLabel(size: FontSize): string {
    const option = this.fontSizeOptions.find(o => o.value === size);
    return option?.label || 'Normal';
  }

  getFontFamilyLabel(family: FontFamily): string {
    const option = this.fontFamilyOptions.find(o => o.value === family);
    return option?.label || 'Predeterminada';
  }

  getColorFilterLabel(filter: ColorFilter): string {
    const option = this.colorFilterOptions.find(o => o.value === filter);
    return option?.label || 'Normal';
  }

  getTextSpacingLabel(spacing: TextSpacing): string {
    const option = this.textSpacingOptions.find(o => o.value === spacing);
    return option?.label || 'Normal';
  }
  setTextSpacingFromString(spacing: string): void {
  this.setTextSpacing(spacing as TextSpacing);
}
}