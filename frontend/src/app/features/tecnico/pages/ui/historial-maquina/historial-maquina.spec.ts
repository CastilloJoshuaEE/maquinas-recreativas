import { ComponentFixture, TestBed } from '@angular/core/testing';
import { HistorialMaquinaComponent } from './historial-maquina';

describe('HistorialMaquinaComponent', () => {
  let component: HistorialMaquinaComponent;
  let fixture: ComponentFixture<HistorialMaquinaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [HistorialMaquinaComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(HistorialMaquinaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});