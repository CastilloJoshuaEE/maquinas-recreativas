import { Injectable, signal, computed, effect, Inject, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

export type ThemeMode = 'light' | 'dark' | 'high-contrast';
export type FontSize = 'small' | 'normal' | 'large' | 'x-large';
export type FontFamily = 'default' | 'opendyslexic' | 'arial' | 'verdana';
export type ColorFilter = 'none' | 'protanopia' | 'deuteranopia' | 'tritanopia' | 'grayscale';
export type TextSpacing = 'normal' | 'large' | 'x-large';

export interface AccessibilitySettings {
  themeMode: ThemeMode;
  fontSize: FontSize;
  fontFamily: FontFamily;
  colorFilter: ColorFilter;
  textSpacing: TextSpacing;
  reduceAnimations: boolean;
  highContrast: boolean;
  focusIndicator: boolean;
  lineHeight: number;
  letterSpacing: number;
  wordSpacing: number;
}

@Injectable({ providedIn: 'root' })
export class AccessibilitySettingsService {
  private readonly STORAGE_KEY = 'accessibility_settings';
  private isBrowser: boolean;

  // Señales para estado reactivo
  private settingsSignal = signal<AccessibilitySettings>(this.getDefaultSettings());
  
  // Señales computadas
  public readonly settings = this.settingsSignal.asReadonly();
  public readonly isDarkMode = computed(() => this.settingsSignal().themeMode === 'dark');
  public readonly isHighContrast = computed(() => 
    this.settingsSignal().themeMode === 'high-contrast' || this.settingsSignal().highContrast
  );

  constructor(@Inject(PLATFORM_ID) private platformId: Object) {
    this.isBrowser = isPlatformBrowser(this.platformId);
    if (this.isBrowser) {
      this.loadSettings();
      this.applySettings(this.settingsSignal());
    }
  }

  private getDefaultSettings(): AccessibilitySettings {
    return {
      themeMode: 'light',
      fontSize: 'normal',
      fontFamily: 'default',
      colorFilter: 'none',
      textSpacing: 'normal',
      reduceAnimations: false,
      highContrast: false,
      focusIndicator: true,
      lineHeight: 1.5,
      letterSpacing: 0,
      wordSpacing: 0
    };
  }

  private loadSettings(): void {
    try {
      const saved = localStorage.getItem(this.STORAGE_KEY);
      if (saved) {
        const parsed = JSON.parse(saved);
        this.settingsSignal.set({ ...this.getDefaultSettings(), ...parsed });
      }
    } catch (e) {
      console.warn('Error loading accessibility settings', e);
    }
  }

  private saveSettings(settings: AccessibilitySettings): void {
    if (this.isBrowser) {
      localStorage.setItem(this.STORAGE_KEY, JSON.stringify(settings));
    }
  }

  private applySettings(settings: AccessibilitySettings): void {
  if (!this.isBrowser) return;
  
  const root = document.documentElement;
  
  // Aplicar tema (pero excluyendo el widget)
  this.applyTheme(settings.themeMode);
  
  // Aplicar tamaño de fuente (excluyendo el widget)
  this.applyFontSize(settings.fontSize);
  
  // Aplicar tipo de fuente (excluyendo el widget)
  this.applyFontFamily(settings.fontFamily);
  
  // Aplicar filtro de color (excluyendo el widget)
  this.applyColorFilter(settings.colorFilter);
  
  // Aplicar espaciado de texto (excluyendo el widget)
  this.applyTextSpacing(settings);
  
  // Aplicar reducción de animaciones (excluyendo el widget)
  this.applyReduceAnimations(settings.reduceAnimations);
  
  // Aplicar indicador de enfoque (excluyendo el widget)
  this.applyFocusIndicator(settings.focusIndicator);
  
  // Agregar clase al body para estilos específicos
  // PERO agregar clase para excluir el widget
  if (settings.themeMode === 'dark') {
    document.body.classList.add('dark-theme');
  } else {
    document.body.classList.remove('dark-theme');
  }
  
  if (settings.themeMode === 'high-contrast' || settings.highContrast) {
    document.body.classList.add('high-contrast');
  } else {
    document.body.classList.remove('high-contrast');
  }
  
  if (settings.reduceAnimations) {
    document.body.classList.add('reduce-animations');
  } else {
    document.body.classList.remove('reduce-animations');
  }
}

private applyTheme(theme: ThemeMode): void {
  const root = document.documentElement;
  
  // Aplicar estilos SOLO al contenido principal, no al widget
  switch (theme) {
    case 'dark':
      // Aplicar solo al main-content, no al widget
      root.style.setProperty('--bg-primary', '#1a1a2e');
      root.style.setProperty('--bg-secondary', '#16213e');
      root.style.setProperty('--text-primary', '#ffffff');
      root.style.setProperty('--text-secondary', '#cccccc');
      root.style.setProperty('--border-color', '#333333');
      break;
    case 'high-contrast':
      root.style.setProperty('--bg-primary', '#000000');
      root.style.setProperty('--bg-secondary', '#000000');
      root.style.setProperty('--text-primary', '#ffffff');
      root.style.setProperty('--text-secondary', '#ffff00');
      root.style.setProperty('--border-color', '#ffffff');
      root.style.setProperty('--link-color', '#ffff00');
      root.style.setProperty('--link-hover', '#ffffff');
      break;
    default:
      root.style.removeProperty('--bg-primary');
      root.style.removeProperty('--bg-secondary');
      root.style.removeProperty('--text-primary');
      root.style.removeProperty('--text-secondary');
      root.style.removeProperty('--border-color');
      root.style.removeProperty('--link-color');
      root.style.removeProperty('--link-hover');
      break;
  }
}

  private applyFontSize(size: FontSize): void {
    const root = document.documentElement;
    const sizes = {
      small: '12px',
      normal: '16px',
      large: '20px',
      'x-large': '24px'
    };
    root.style.setProperty('--base-font-size', sizes[size]);
    root.style.fontSize = sizes[size];
  }

  private applyFontFamily(family: FontFamily): void {
    const root = document.documentElement;
    const fonts = {
      default: "'Inter', 'Segoe UI', sans-serif",
      opendyslexic: "'OpenDyslexic', 'Comic Sans MS', sans-serif",
      arial: "Arial, Helvetica, sans-serif",
      verdana: "Verdana, Geneva, sans-serif"
    };
    root.style.setProperty('--font-family', fonts[family]);
    document.body.style.fontFamily = fonts[family];
  }

  private applyColorFilter(filter: ColorFilter): void {
    const filters = {
      none: 'none',
      protanopia: 'url(#protanopia-filter)',
      deuteranopia: 'url(#deuteranopia-filter)',
      tritanopia: 'url(#tritanopia-filter)',
      grayscale: 'grayscale(100%)'
    };
    
    // Crear SVG filters si es necesario
    if (filter !== 'none' && filter !== 'grayscale') {
      this.ensureColorFilters();
    }
    
    document.body.style.filter = filters[filter];
  }

  private ensureColorFilters(): void {
    if (document.querySelector('#color-blind-filters')) return;
    
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('style', 'position: absolute; width: 0; height: 0;');
    svg.innerHTML = `
      <filter id="protanopia-filter">
        <feColorMatrix type="matrix" values="0.567, 0.433, 0, 0, 0  0.558, 0.442, 0, 0, 0  0, 0.242, 0.758, 0, 0  0, 0, 0, 1, 0"/>
      </filter>
      <filter id="deuteranopia-filter">
        <feColorMatrix type="matrix" values="0.625, 0.375, 0, 0, 0  0.7, 0.3, 0, 0, 0  0, 0.3, 0.7, 0, 0  0, 0, 0, 1, 0"/>
      </filter>
      <filter id="tritanopia-filter">
        <feColorMatrix type="matrix" values="0.95, 0.05, 0, 0, 0  0, 0.433, 0.567, 0, 0  0, 0.475, 0.525, 0, 0  0, 0, 0, 1, 0"/>
      </filter>
    `;
    document.body.appendChild(svg);
  }

  private applyTextSpacing(settings: AccessibilitySettings): void {
    const root = document.documentElement;
    const spacingMultiplier = {
      normal: 1,
      large: 1.5,
      'x-large': 2
    };
    const multiplier = spacingMultiplier[settings.textSpacing];
    
    root.style.setProperty('--line-height', `${settings.lineHeight * multiplier}`);
    root.style.setProperty('--letter-spacing', `${settings.letterSpacing * multiplier}px`);
    root.style.setProperty('--word-spacing', `${settings.wordSpacing * multiplier}px`);
    
    document.body.style.lineHeight = String(settings.lineHeight * multiplier);
    document.body.style.letterSpacing = `${settings.letterSpacing * multiplier}px`;
    document.body.style.wordSpacing = `${settings.wordSpacing * multiplier}px`;
  }

  private applyReduceAnimations(reduce: boolean): void {
    if (reduce) {
      const style = document.createElement('style');
      style.id = 'reduce-animations-style';
      style.textContent = `
        *, *::before, *::after {
          animation-duration: 0.01ms !important;
          animation-iteration-count: 1 !important;
          transition-duration: 0.01ms !important;
          scroll-behavior: auto !important;
        }
      `;
      if (!document.querySelector('#reduce-animations-style')) {
        document.head.appendChild(style);
      }
    } else {
      document.querySelector('#reduce-animations-style')?.remove();
    }
  }

  private applyFocusIndicator(enable: boolean): void {
    if (enable) {
      const style = document.createElement('style');
      style.id = 'focus-indicator-style';
      style.textContent = `
        *:focus-visible {
          outline: 3px solid #4f6bed !important;
          outline-offset: 2px !important;
          box-shadow: 0 0 0 4px rgba(79, 107, 237, 0.3) !important;
        }
      `;
      if (!document.querySelector('#focus-indicator-style')) {
        document.head.appendChild(style);
      }
    } else {
      document.querySelector('#focus-indicator-style')?.remove();
    }
  }

  // Métodos públicos para actualizar configuraciones
  updateSettings(partial: Partial<AccessibilitySettings>): void {
    const newSettings = { ...this.settingsSignal(), ...partial };
    this.settingsSignal.set(newSettings);
    this.saveSettings(newSettings);
    this.applySettings(newSettings);
  }

  toggleTheme(): void {
    const current = this.settingsSignal().themeMode;
    const next = current === 'light' ? 'dark' : current === 'dark' ? 'light' : 'light';
    this.updateSettings({ themeMode: next, highContrast: false });
  }

  toggleHighContrast(): void {
    const current = this.settingsSignal();
    this.updateSettings({ 
      highContrast: !current.highContrast,
      themeMode: !current.highContrast ? 'high-contrast' : 'light'
    });
  }

  increaseFontSize(): void {
    const sizes: FontSize[] = ['small', 'normal', 'large', 'x-large'];
    const current = this.settingsSignal().fontSize;
    const index = sizes.indexOf(current);
    if (index < sizes.length - 1) {
      this.updateSettings({ fontSize: sizes[index + 1] });
    }
  }

  decreaseFontSize(): void {
    const sizes: FontSize[] = ['small', 'normal', 'large', 'x-large'];
    const current = this.settingsSignal().fontSize;
    const index = sizes.indexOf(current);
    if (index > 0) {
      this.updateSettings({ fontSize: sizes[index - 1] });
    }
  }

  resetToDefault(): void {
    this.updateSettings(this.getDefaultSettings());
  }
}