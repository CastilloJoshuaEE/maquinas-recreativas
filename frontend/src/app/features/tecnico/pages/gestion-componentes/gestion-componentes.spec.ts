import { ComponentFixture, TestBed } from '@angular/core/testing';
import { GestionComponentesComponent } from './gestion-componentes';

describe('GestionComponentesComponent', () => {
  let component: GestionComponentesComponent;
  let fixture: ComponentFixture<GestionComponentesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [GestionComponentesComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(GestionComponentesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});