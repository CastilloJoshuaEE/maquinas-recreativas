import { ComponentFixture, TestBed } from '@angular/core/testing';
import { DashboardComprobadorComponent } from './dashboard-comprobador';

describe('DashboardComprobadorComponent', () => {
  let component: DashboardComprobadorComponent;
  let fixture: ComponentFixture<DashboardComprobadorComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DashboardComprobadorComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(DashboardComprobadorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});