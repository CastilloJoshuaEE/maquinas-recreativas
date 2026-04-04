/**
 * @fileoverview Configuración del módulo de autenticación
 * @description Configuración específica para el módulo de autenticación
 */

import { EnvironmentProviders, makeEnvironmentProviders } from '@angular/core';

export function provideAuthConfig(): EnvironmentProviders {
  return makeEnvironmentProviders([]);
}