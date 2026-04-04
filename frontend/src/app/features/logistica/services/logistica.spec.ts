import { TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { LogisticaService } from './logistica.service';

describe('LogisticaService', () => {
  let service: LogisticaService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        LogisticaService
      ]
    });
    service = TestBed.inject(LogisticaService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});