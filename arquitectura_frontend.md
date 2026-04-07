## **Arquitectura Modular + Feature-Based + Clean Architecture (adaptada)**

frontend/
├── angular.json
├── package.json
├── tsconfig.json
├── tsconfig.app.json
├── tsconfig.spec.json
├── index.html
├── main.ts
├── styles.css
│
├── environments/
│   ├── environment.ts
│   └── environment.prod.ts
│
├── assets/
│   └── imagenes/
│       └── bg1.jpg
│
├── styles/
│   ├── variables.scss
│   ├── mixins.scss
│   └── global.scss
│
└── src/
    └── app/
        ├── app.ts
        ├── app.html
        ├── app.css
        ├── app.routes.ts
        ├── app.config.ts
        └── app.config.server.ts   # opcional si usas SSR
        │
        ├── core/
        │   ├── constants/
        │   │   └── app.constants.ts
        │   │
        │   ├── models/
        │   │   ├── user.model.ts
        │   │   ├── maquina.model.ts
        │   │   ├── recaudacion.model.ts
        │   │   ├── reporte.model.ts
        │   │   └── componente.model.ts
        │   │
        │   ├── services/
        │   │   ├── api.ts
        │   │   ├── auth.ts
        │   │   ├── user.ts
        │   │   ├── notification.ts
        │   │   └── report.ts
        │   │
        │   ├── guards/
        │   │   ├── auth.guard.ts
        │   │   ├── auth.guard.spec.ts
        │   │   ├── role.guard.ts
        │   │   └── role.guard.spec.ts
        │   │
        │   └── interceptors/
        │       ├── auth.interceptor.ts
        │       └── auth.interceptor.spec.ts
        │
        ├── shared/
        │   ├── ui/
        │   │   ├── admin-header/
        │   │   │   ├── admin-header.ts
        │   │   │   ├── admin-header.html
        │   │   │   ├── admin-header.css
        │   │   │   └── admin-header.spec.ts
        │   │   │
        │   │   ├── modal/
        │   │   │   ├── modal.ts
        │   │   │   ├── modal.html
        │   │   │   ├── modal.css
        │   │   │   └── modal.spec.ts
        │   │   │
        │   │   ├── tabla-generica/
        │   │   │   ├── tabla-generica.ts
        │   │   │   ├── tabla-generica.html
        │   │   │   ├── tabla-generica.css
        │   │   │   └── tabla-generica.spec.ts
        │   │   │
        │   │   ├── filtros-busqueda/
        │   │   │   ├── filtros-busqueda.ts
        │   │   │   ├── filtros-busqueda.html
        │   │   │   ├── filtros-busqueda.css
        │   │   │   └── filtros-busqueda.spec.ts
        │   │   │
        │   │   ├── chat/
        │   │   │   ├── chat.ts
        │   │   │   ├── chat.html
        │   │   │   ├── chat.css
        │   │   │   └── chat.spec.ts
        │   │   │
        │   │   ├── maquina-list/
        │   │   │   ├── maquina-list.ts
        │   │   │   ├── maquina-list.html
        │   │   │   ├── maquina-list.css
        │   │   │   └── maquina-list.spec.ts
        │   │   │
        │   │   ├── notificaciones-list/
        │   │   │   ├── notificaciones-list.ts
        │   │   │   ├── notificaciones-list.html
        │   │   │   ├── notificaciones-list.css
        │   │   │   └── notificaciones-list.spec.ts
        │   │   │
        │   │   ├── profile-section/
        │   │   │   ├── profile-section.ts
        │   │   │   ├── profile-section.html
        │   │   │   ├── profile-section.css
        │   │   │   └── profile-section.spec.ts
        │   │   │
        │   │   └── chatbot/
        │   │       ├── chatbot.ts
        │   │       ├── chatbot.html
        │   │       ├── chatbot.css
        │   │       └── chatbot.spec.ts
        │   │
        │   ├── directives/
        │   │   └── has-role.directive.ts
        │   │
        │   └── pipes/
        │       ├── safe-html.pipe.ts
        │       └── fecha-relativa.pipe.ts
        │
        ├── layouts/
        │   ├── main-layout/
        │   │   ├── main-layout.ts
        │   │   ├── main-layout.html
        │   │   ├── main-layout.css
        │   │   └── main-layout.spec.ts
        │   │
        │   └── auth-layout/
        │       ├── auth-layout.ts
        │       ├── auth-layout.html
        │       ├── auth-layout.css
        │       └── auth-layout.spec.ts
        │
        ├── features/
        │   ├── auth/
        │   │   ├── auth.routes.ts
        │   │   ├── auth.config.ts
        │   │   └── pages/
        │   │       ├── login/
        │   │       │   ├── login.ts
        │   │       │   ├── login.html
        │   │       │   ├── login.css
        │   │       │   └── login.spec.ts
        │   │       ├── registro/
        │   │       │   ├── registro.ts
        │   │       │   ├── registro.html
        │   │       │   ├── registro.css
        │   │       │   └── registro.spec.ts
        │   │       ├── recuperar-contrasena/
        │   │       │   ├── recuperar-contrasena.ts
        │   │       │   ├── recuperar-contrasena.html
        │   │       │   ├── recuperar-contrasena.css
        │   │       │   └── recuperar-contrasena.spec.ts
        │   │       ├── recuperar-usuario/
        │   │       │   ├── recuperar-usuario.ts
        │   │       │   ├── recuperar-usuario.html
        │   │       │   ├── recuperar-usuario.css
        │   │       │   └── recuperar-usuario.spec.ts
        │   │       └── actualizar-usuario/
        │   │           ├── actualizar-usuario.ts
        │   │           ├── actualizar-usuario.html
        │   │           ├── actualizar-usuario.css
        │   │           └── actualizar-usuario.spec.ts
        │   │
        │   ├── admin/
        │   │   ├── admin.routes.ts
        │   │   ├── services/
        │   │   │   ├── admin.ts
        │   │   │   └── admin.spec.ts
        │   │   ├── ui/
        │   │   │   ├── user-form/
        │   │   │   │   ├── user-form.ts
        │   │   │   │   ├── user-form.html
        │   │   │   │   ├── user-form.css
        │   │   │   │   └── user-form.spec.ts
        │   │   │   └── user-table/
        │   │   │       ├── user-table.ts
        │   │   │       ├── user-table.html
        │   │   │       ├── user-table.css
        │   │   │       └── user-table.spec.ts
        │   │   └── pages/
        │   │       ├── dashboard-admin/
        │   │       │   ├── dashboard-admin.ts
        │   │       │   ├── dashboard-admin.html
        │   │       │   ├── dashboard-admin.css
        │   │       │   └── dashboard-admin.spec.ts
        │   │       ├── gestion-usuarios/
        │   │       │   ├── gestion-usuarios.ts
        │   │       │   ├── gestion-usuarios.html
        │   │       │   ├── gestion-usuarios.css
        │   │       │   └── gestion-usuarios.spec.ts
        │   │       ├── consultar-usuarios/
        │   │       │   ├── consultar-usuarios.ts
        │   │       │   ├── consultar-usuarios.html
        │   │       │   ├── consultar-usuarios.css
        │   │       │   └── consultar-usuarios.spec.ts
        │   │       ├── registrar-usuario/
        │   │       │   ├── registrar-usuario.ts
        │   │       │   ├── registrar-usuario.html
        │   │       │   ├── registrar-usuario.css
        │   │       │   └── registrar-usuario.spec.ts
        │   │       └── editar-usuario/
        │   │           ├── editar-usuario.ts
        │   │           ├── editar-usuario.html
        │   │           ├── editar-usuario.css
        │   │           └── editar-usuario.spec.ts
        │   │
        │   ├── contabilidad/
        │   │   ├── contabilidad.routes.ts
        │   │   ├── services/
        │   │   │   ├── contabilidad.ts
        │   │   │   └── contabilidad.spec.ts
        │   │   ├── ui/
        │   │   │   ├── recaudacion-form/
        │   │   │   │   ├── recaudacion-form.ts
        │   │   │   │   ├── recaudacion-form.html
        │   │   │   │   ├── recaudacion-form.css
        │   │   │   │   └── recaudacion-form.spec.ts
        │   │   │   └── informe-recaudacion/
        │   │   │       ├── informe-recaudacion.ts
        │   │   │       ├── informe-recaudacion.html
        │   │   │       ├── informe-recaudacion.css
        │   │   │       └── informe-recaudacion.spec.ts
        │   │   └── pages/
        │   │       ├── dashboard-contabilidad/
        │   │       │   ├── dashboard-contabilidad.ts
        │   │       │   ├── dashboard-contabilidad.html
        │   │       │   ├── dashboard-contabilidad.css
        │   │       │   └── dashboard-contabilidad.spec.ts
        │   │       ├── gestion-recaudacion/
        │   │       │   ├── gestion-recaudacion.ts
        │   │       │   ├── gestion-recaudacion.html
        │   │       │   ├── gestion-recaudacion.css
        │   │       │   └── gestion-recaudacion.spec.ts
        │   │       ├── registrar-recaudacion/
        │   │       │   ├── registrar-recaudacion.ts
        │   │       │   ├── registrar-recaudacion.html
        │   │       │   ├── registrar-recaudacion.css
        │   │       │   └── registrar-recaudacion.spec.ts
        │   │       ├── consultar-recaudaciones/
        │   │       │   ├── consultar-recaudaciones.ts
        │   │       │   ├── consultar-recaudaciones.html
        │   │       │   ├── consultar-recaudaciones.css
        │   │       │   └── consultar-recaudaciones.spec.ts
        │   │       ├── actualizar-recaudacion/
        │   │       │   ├── actualizar-recaudacion.ts
        │   │       │   ├── actualizar-recaudacion.html
        │   │       │   ├── actualizar-recaudacion.css
        │   │       │   └── actualizar-recaudacion.spec.ts
        │   │       ├── levantar-informe/
        │   │       │   ├── levantar-informe.ts
        │   │       │   ├── levantar-informe.html
        │   │       │   ├── levantar-informe.css
        │   │       │   └── levantar-informe.spec.ts
        │   │       ├── ver-informe/
        │   │       │   ├── ver-informe.ts
        │   │       │   ├── ver-informe.html
        │   │       │   ├── ver-informe.css
        │   │       │   └── ver-informe.spec.ts
        │   │       └── consultar-informe-distribucion/
        │   │           ├── consultar-informe-distribucion.ts
        │   │           ├── consultar-informe-distribucion.html
        │   │           ├── consultar-informe-distribucion.css
        │   │           └── consultar-informe-distribucion.spec.ts
        │   │
        │   ├── logistica/
        │   │   ├── logistica.routes.ts
        │   │   ├── services/
        │   │   │   ├── logistica.ts
        │   │   │   └── logistica.spec.ts
        │   │   ├── ui/
        │   │   │   ├── comercio-form/
        │   │   │   │   ├── comercio-form.ts
        │   │   │   │   ├── comercio-form.html
        │   │   │   │   ├── comercio-form.css
        │   │   │   │   └── comercio-form.spec.ts
        │   │   │   └── maquina-form/
        │   │   │       ├── maquina-form.ts
        │   │   │       ├── maquina-form.html
        │   │   │       ├── maquina-form.css
        │   │   │       └── maquina-form.spec.ts
        │   │   └── pages/
        │   │       └── dashboard-logistica/
        │   │           ├── dashboard-logistica.ts
        │   │           ├── dashboard-logistica.html
        │   │           ├── dashboard-logistica.css
        │   │           └── dashboard-logistica.spec.ts
        │   │
        │   ├── tecnico/
        │   │   ├── tecnico.routes.ts
        │   │   ├── services/
        │   │   │   ├── tecnico.ts
        │   │   │   └── tecnico.spec.ts
        │   │   ├── ui/
        │   │   │   ├── checklist-comprobacion/
        │   │   │   │   ├── checklist-comprobacion.ts
        │   │   │   │   ├── checklist-comprobacion.html
        │   │   │   │   ├── checklist-comprobacion.css
        │   │   │   │   └── checklist-comprobacion.spec.ts
        │   │   │   └── historial-maquina/
        │   │   │       ├── historial-maquina.ts
        │   │   │       ├── historial-maquina.html
        │   │   │       ├── historial-maquina.css
        │   │   │       └── historial-maquina.spec.ts
        │   │   └── pages/
        │   │       ├── dashboard-ensamblador/
        │   │       │   ├── dashboard-ensamblador.ts
        │   │       │   ├── dashboard-ensamblador.html
        │   │       │   ├── dashboard-ensamblador.css
        │   │       │   └── dashboard-ensamblador.spec.ts
        │   │       ├── dashboard-comprobador/
        │   │       │   ├── dashboard-comprobador.ts
        │   │       │   ├── dashboard-comprobador.html
        │   │       │   ├── dashboard-comprobador.css
        │   │       │   └── dashboard-comprobador.spec.ts
        │   │       ├── dashboard-mantenimiento/
        │   │       │   ├── dashboard-mantenimiento.ts
        │   │       │   ├── dashboard-mantenimiento.html
        │   │       │   ├── dashboard-mantenimiento.css
        │   │       │   └── dashboard-mantenimiento.spec.ts
        │   │       └── gestion-componentes/
        │   │           ├── gestion-componentes.ts
        │   │           ├── gestion-componentes.html
        │   │           ├── gestion-componentes.css
        │   │           └── gestion-componentes.spec.ts
        │   │
        │   ├── reportes/
        │   │   ├── reportes.routes.ts
        │   │   ├── services/
        │   │   │   ├── reportes.ts
        │   │   │   └── reportes.spec.ts
        │   │   ├── ui/
        │   │   │   ├── chat-usuarios/
        │   │   │   │   ├── chat-usuarios.ts
        │   │   │   │   ├── chat-usuarios.html
        │   │   │   │   ├── chat-usuarios.css
        │   │   │   │   └── chat-usuarios.spec.ts
        │   │   │   └── notificaciones-panel/
        │   │   │       ├── notificaciones-panel.ts
        │   │   │       ├── notificaciones-panel.html
        │   │   │       ├── notificaciones-panel.css
        │   │   │       └── notificaciones-panel.spec.ts
        │   │   └── pages/
        │   │       ├── gestion-reportes/
        │   │       │   ├── gestion-reportes.ts
        │   │       │   ├── gestion-reportes.html
        │   │       │   ├── gestion-reportes.css
        │   │       │   └── gestion-reportes.spec.ts
        │   │       └── chat-view/
        │   │           ├── chat-view.ts
        │   │           ├── chat-view.html
        │   │           ├── chat-view.css
        │   │           └── chat-view.spec.ts
        │   │
        │   ├── usuario/
        │   │   ├── usuario.routes.ts
        │   │   ├── pages/
        │   │   │   ├── perfil/
        │   │   │   │   ├── perfil.ts
        │   │   │   │   ├── perfil.html
        │   │   │   │   ├── perfil.css
        │   │   │   │   └── perfil.spec.ts
        │   │   │   └── actualizar-perfil/
        │   │   │       ├── actualizar-perfil.ts
        │   │   │       ├── actualizar-perfil.html
        │   │   │       ├── actualizar-perfil.css
        │   │   │       └── actualizar-perfil.spec.ts
        │   │
        │   └── pages/
        │       ├── informacion/
        │       │   ├── informacion.ts
        │       │   ├── informacion.html
        │       │   ├── informacion.css
        │       │   └── informacion.spec.ts
        │       ├── no-autorizado/
        │       │   ├── no-autorizado.ts
        │       │   ├── no-autorizado.html
        │       │   ├── no-autorizado.css
        │       │   └── no-autorizado.spec.ts
        │       ├── acceso-restringido/
        │       │   ├── acceso-restringido.ts
        │       │   ├── acceso-restringido.html
        │       │   ├── acceso-restringido.css
        │       │   └── acceso-restringido.spec.ts
        │       └── historial-general/
        │           ├── historial-general.ts
        │           ├── historial-general.html
        │           ├── historial-general.css
        │           └── historial-general.spec.ts

## Cómo instalarlo

Navegar a la carpeta del proyecto
powershell
cd C:\ruta\de\tu\proyecto\frontend
3. Limpiar caché de npm (recomendado)
powershell
npm cache clean --force
4. Instalar dependencias principales
powershell
npm install
5. Instalar Angular CLI globalmente (si no lo tienes)
powershell
npm install -g @angular/cli@20.3.6     7. Instalar Bootstrap y dependencias de UI
powershell
npm install bootstrap@5.3.3 @popperjs/core@2.11.8 y en angular json poner:           "node_modules/bootstrap/dist/css/bootstrap.min.css",
8. Instalar Chart.js y Moment.js
powershell
npm install chart.js@4.4.0 moment@2.30.1
9. Instalar ngx-toastr y ngx-pagination
powershell
npm install ngx-toastr@19.0.0 ngx-pagination@6.0.3 10. Instalar dependencias de desarrollo  npm install -D
@angular-devkit/build-angular@20.3.18
@angular-eslint/builder@20.3.18
@angular-eslint/eslint-plugin@20.3.18
@angular-eslint/eslint-plugin-template@20.3.18
@angular-eslint/schematics@20.3.18
@angular-eslint/template-parser@20.3.18
@angular/cli@20.3.18
@angular/compiler-cli@20.3.18  11. Instalar Compodoc para documentación
powershell
npm install -D @compodoc/compodoc@1.1.25  12. Instalar herramientas de testing
powershell
npm install -D @types/jasmine@5.1.0 jasmine-core@5.6.0 karma@6.4.0 karma-chrome-launcher@3.2.0 karma-coverage@2.2.0 karma-jasmine@5.1.0 karma-jasmine-html-reporter@2.1.0  npm install -D eslint@9.0.0 @typescript-eslint/eslint-plugin@8.0.0 @typescript-eslint/parser@8.0.0
15. Verificar instalación
powershell
npm list --depth=0
📜 Script de Instalación Completo (copiar y pegar en PowerShell)
powershell

# Script de instalación completo para Windows

Write-Host "=== INSTALANDO RECREA SYS FRONTEND ===" -ForegroundColor Green

# Limpiar caché

npm cache clean --force

# Instalar dependencias principales

npm install @angular/animations@20.3.18 --legacy-peer-deps

npm install @angular/cdk@20.0.0 @angular/common@20.0.0 @angular/compiler@20.0.0 @angular/core@20.0.0 @angular/forms@20.0.0 @angular/material@20.0.0 @angular/platform-browser@20.0.0 @angular/platform-browser-dynamic@20.0.0 @angular/router@20.0.0

# Instalar Bootstrap

npm install bootstrap@5.3.3 @popperjs/core@2.11.8

# Instalar utilidades

npm install chart.js@4.4.0 moment@2.30.1

# Instalar ngx-toastr

npm install ngx-toastr@19.0.0 ngx-pagination@6.0.3

npm install zone.js@0.15.0 --legacy-peer-deps

# Instalar dependencias de desarrollo

npm install -D @angular-devkit/build-angular@20.0.0 @angular-eslint/builder@20.0.0 @angular-eslint/eslint-plugin@20.0.0 @angular-eslint/eslint-plugin-template@20.0.0 @angular-eslint/schematics@20.0.0 @angular-eslint/template-parser@20.0.0

# Instalar Compodoc

npm install -D @compodoc/compodoc@1.1.25

# Instalar herramientas de testing

npm install -D @types/jasmine@5.1.0 jasmine-core@5.6.0 karma@6.4.0 karma-chrome-launcher@3.2.0 karma-coverage@2.2.0 karma-jasmine@5.1.0 karma-jasmine-html-reporter@2.1.0

# Instalar ESLint

npm install -D eslint@9.0.0 @typescript-eslint/eslint-plugin@8.0.0 @typescript-eslint/parser@8.0.0 typescript@5.8.0

# Instalar tipos de Node

npm install -D @types/node@20.0.0
Verificar instalación
powershell
npm list --depth=0

angular.json:

**"styles"**: [
"node_modules/bootstrap/dist/css/bootstrap.min.css",
"src/styles.css"
],
"scripts": []

npm install --save-dev jest @types/jest ts-jest

## Cómo generar la documentación

npx compodoc -p tsconfig.json
