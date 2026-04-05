import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { ConsultarInformeDistribucionLogisticaComponent } from './consultar-informe-distribucion';

describe('ConsultarInformeDistribucionLogisticaComponent', () => {
  let component: ConsultarInformeDistribucionLogisticaComponent;
  let fixture: ComponentFixture<ConsultarInformeDistribucionLogisticaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ConsultarInformeDistribucionLogisticaComponent, ReactiveFormsModule, NoopAnimationsModule]
    }).compileComponents();

    fixture = TestBed.createComponent(ConsultarInformeDistribucionLogisticaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});