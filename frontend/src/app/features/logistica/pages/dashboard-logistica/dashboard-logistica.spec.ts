import { ComponentFixture, TestBed } from '@angular/core/testing';
import { DashboardLogisticaComponent } from './dashboard-logistica';

describe('DashboardLogisticaComponent', () => {
  let component: DashboardLogisticaComponent;
  let fixture: ComponentFixture<DashboardLogisticaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DashboardLogisticaComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(DashboardLogisticaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});