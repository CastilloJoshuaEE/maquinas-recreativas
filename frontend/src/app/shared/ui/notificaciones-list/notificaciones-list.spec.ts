import { ComponentFixture, TestBed } from '@angular/core/testing';
import { NotificacionesListComponent } from './notificaciones-list';

describe('NotificacionesListComponent', () => {
  let component: NotificacionesListComponent;
  let fixture: ComponentFixture<NotificacionesListComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [NotificacionesListComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(NotificacionesListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});