import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ChecklistComprobacionComponent } from './checklist-comprobacion';

describe('ChecklistComprobacionComponent', () => {
  let component: ChecklistComprobacionComponent;
  let fixture: ComponentFixture<ChecklistComprobacionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ChecklistComprobacionComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(ChecklistComprobacionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});