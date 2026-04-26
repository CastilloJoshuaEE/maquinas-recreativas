/**
 * @fileoverview Pruebas del componente LoadingSpinner
 */

import { ComponentFixture, TestBed } from '@angular/core/testing';
import { LoadingSpinnerComponent } from './loading-spinner';
import { By } from '@angular/platform-browser';

describe('LoadingSpinnerComponent', () => {
  let component: LoadingSpinnerComponent;
  let fixture: ComponentFixture<LoadingSpinnerComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [LoadingSpinnerComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(LoadingSpinnerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should show default message', () => {
    const messageElement = fixture.debugElement.query(By.css('.spinner-message'));
    expect(messageElement.nativeElement.textContent).toContain('Cargando...');
  });

  it('should show custom message when provided', () => {
    component.message = 'Guardando datos...';
    fixture.detectChanges();
    const messageElement = fixture.debugElement.query(By.css('.spinner-message'));
    expect(messageElement.nativeElement.textContent).toContain('Guardando datos...');
  });

  it('should hide message when showMessage is false', () => {
    component.showMessage = false;
    fixture.detectChanges();
    const messageElement = fixture.debugElement.query(By.css('.spinner-message'));
    expect(messageElement).toBeNull();
  });

  it('should apply fullscreen class when fullScreen is true', () => {
    component.fullScreen = true;
    fixture.detectChanges();
    const container = fixture.debugElement.query(By.css('.spinner-container'));
    expect(container.classes['fullscreen']).toBeTruthy();
  });

  it('should set spinner diameter correctly', () => {
    component.diameter = 80;
    fixture.detectChanges();
    const spinner = fixture.debugElement.query(By.css('mat-spinner'));
    expect(spinner.componentInstance.diameter).toBe(80);
  });
});