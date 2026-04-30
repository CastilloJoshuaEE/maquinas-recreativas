import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { ConsultarRecaudacionesComponent } from './consultar-recaudaciones';

describe('ConsultarRecaudacionesComponent', () => {
  let component: ConsultarRecaudacionesComponent;
  let fixture: ComponentFixture<ConsultarRecaudacionesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ConsultarRecaudacionesComponent, ReactiveFormsModule, NoopAnimationsModule]
    }).compileComponents();

    fixture = TestBed.createComponent(ConsultarRecaudacionesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});