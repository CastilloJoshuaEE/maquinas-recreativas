import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ChatUsuariosComponent } from './chat-usuarios';

describe('ChatUsuariosComponent', () => {
  let component: ChatUsuariosComponent;
  let fixture: ComponentFixture<ChatUsuariosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ChatUsuariosComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(ChatUsuariosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});