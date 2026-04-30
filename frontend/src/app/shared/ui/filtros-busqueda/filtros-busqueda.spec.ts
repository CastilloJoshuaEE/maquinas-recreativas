import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { FiltrosBusquedaComponent } from './filtros-busqueda';

describe('FiltrosBusquedaComponent', () => {
  let component: FiltrosBusquedaComponent;
  let fixture: ComponentFixture<FiltrosBusquedaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({ imports: [FiltrosBusquedaComponent, ReactiveFormsModule, NoopAnimationsModule] }).compileComponents();
    fixture = TestBed.createComponent(FiltrosBusquedaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => { expect(component).toBeTruthy(); });
});