import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { ConsultarInformeDistribucionComponent } from './consultar-informe-distribucion';

describe('ConsultarInformeDistribucionComponent', () => {
  let component: ConsultarInformeDistribucionComponent;
  let fixture: ComponentFixture<ConsultarInformeDistribucionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ConsultarInformeDistribucionComponent, ReactiveFormsModule, NoopAnimationsModule]
    }).compileComponents();

    fixture = TestBed.createComponent(ConsultarInformeDistribucionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});