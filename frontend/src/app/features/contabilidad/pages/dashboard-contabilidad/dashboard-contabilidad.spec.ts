import { ComponentFixture, TestBed } from '@angular/core/testing';
import { DashboardContabilidadComponent } from './dashboard-contabilidad';

describe('DashboardContabilidadComponent', () => {
  let component: DashboardContabilidadComponent;
  let fixture: ComponentFixture<DashboardContabilidadComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DashboardContabilidadComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(DashboardContabilidadComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});