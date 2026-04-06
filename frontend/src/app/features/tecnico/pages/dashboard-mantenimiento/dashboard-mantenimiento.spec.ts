import { ComponentFixture, TestBed } from '@angular/core/testing';
import { DashboardMantenimientoComponent } from './dashboard-mantenimiento';

describe('DashboardMantenimientoComponent', () => {
  let component: DashboardMantenimientoComponent;
  let fixture: ComponentFixture<DashboardMantenimientoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DashboardMantenimientoComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(DashboardMantenimientoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});