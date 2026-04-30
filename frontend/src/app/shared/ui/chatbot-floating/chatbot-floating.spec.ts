import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ChatbotFloatingComponent } from './chatbot-floating';

describe('ChatbotFloatingComponent', () => {
  let component: ChatbotFloatingComponent;
  let fixture: ComponentFixture<ChatbotFloatingComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ChatbotFloatingComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(ChatbotFloatingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should toggle isOpen state when toggle() is called', () => {
    expect(component.isOpen()).toBeFalsy();
    component.toggle();
    expect(component.isOpen()).toBeTruthy();
    component.toggle();
    expect(component.isOpen()).toBeFalsy();
  });
});