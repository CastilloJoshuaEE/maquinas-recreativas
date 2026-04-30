

## Arquitectura Modular + Feature-Based + Clean Architecture (adaptada)

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
        └── app.config.server.ts
        │
        ├── core/
        │   ├── constants/
        │   │   └── app.constants.ts
        │   ├── models/
        │   │   ├── user.model.ts
        │   │   ├── maquina.model.ts
        │   │   ├── recaudacion.model.ts
        │   │   ├── reporte.model.ts
        │   │   └── componente.model.ts
        │   ├── services/
        │   │   ├── api.ts
        │   │   ├── auth.ts
        │   │   ├── user.ts
        │   │   ├── notification.ts
        │   │   └── report.ts
        │   ├── guards/
        │   │   ├── auth.guard.ts
        │   │   ├── auth.guard.spec.ts
        │   │   ├── role.guard.ts
        │   │   └── role.guard.spec.ts
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
        │   │   ├── modal/
        │   │   │   ├── modal.ts
        │   │   │   ├── modal.html
        │   │   │   ├── modal.css
        │   │   │   └── modal.spec.ts
        │   │   ├── tabla-generica/
        │   │   │   ├── tabla-generica.ts
        │   │   │   ├── tabla-generica.html
        │   │   │   ├── tabla-generica.css
        │   │   │   └── tabla-generica.spec.ts
        │   │   ├── filtros-busqueda/
        │   │   │   ├── filtros-busqueda.ts
        │   │   │   ├── filtros-busqueda.html
        │   │   │   ├── filtros-busqueda.css
        │   │   │   └── filtros-busqueda.spec.ts
        │   │   ├── chat/
        │   │   │   ├── chat.ts
        │   │   │   ├── chat.html
        │   │   │   ├── chat.css
        │   │   │   └── chat.spec.ts
        │   │   ├── maquina-list/
        │   │   │   ├── maquina-list.ts
        │   │   │   ├── maquina-list.html
        │   │   │   ├── maquina-list.css
        │   │   │   └── maquina-list.spec.ts
        │   │   ├── profile-section/
        │   │   │   ├── profile-section.ts
        │   │   │   ├── profile-section.html
        │   │   │   ├── profile-section.css
        │   │   │   └── profile-section.spec.ts
        │   │   └── chatbot/
        │   │       ├── chatbot.ts
        │   │       ├── chatbot.html
        │   │       ├── chatbot.css
        │   │       └── chatbot.spec.ts
        │   ├── directives/
        │   │   └── has-role.directive.ts
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
        │
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
        │   │       ├── gestion-usuarios/
        │   │       ├── consultar-usuarios/
        │   │       ├── registrar-usuario/
        │   │       ├── editar-usuario/
        │   │       └── enviar-email/
        │   │           ├── enviar-email.ts
        │   │           ├── enviar-email.html
        │   │           ├── enviar-email.css
        │   │           └── enviar-email.spec.ts
        │
        ├── shared/
        │   ├── ui/
        │   │   ├── loading-spinner/
        │   │   │   ├── loading-spinner.ts
        │   │   │   ├── loading-spinner.html
        │   │   │   ├── loading-spinner.css
        │   │   │   └── loading-spinner.spec.ts
        │   │   ├── maquinas-dashboard/
        │   │   │   ├── maquinas-dashboard.ts
        │   │   │   ├── maquinas-dashboard.html
        │   │   │   ├── maquinas-dashboard.css
        │   │   │   └── maquinas-dashboard.spec.ts
        │   │   ├── maquina-modal/
        │   │   │   ├── maquina-modal.ts
        │   │   │   ├── maquina-modal.html
        │   │   │   ├── maquina-modal.css
        │   │   │   └── maquina-modal.spec.ts
        │   │   ├── historial-maquina-viewer/
        │   │   │   ├── historial-maquina-viewer.ts
        │   │   │   ├── historial-maquina-viewer.html
        │   │   │   ├── historial-maquina-viewer.css
        │   │   │   └── historial-maquina-viewer.spec.ts
        │   │   ├── accessibility-widget/
        │   │   │   ├── accessibility-widget.ts
        │   │   │   ├── accessibility-widget.html
        │   │   │   ├── accessibility-widget.css
        │   │   │   └── accessibility-widget.spec.ts
        │   │   └── accessibility-settings/
        │   │       └── accessibility-settings.ts



Cómo instalar el proyecto (correcto)

1. Ir al proyecto

Ubícate en la carpeta:

C:\ruta\de\tu\proyecto\frontend

2. Limpiar entorno (muy recomendado)

Eliminar dependencias instaladas previamente y limpiar caché:

rm -r -fo node_modules

rm package-lock.json

npm cache clean --force

3. Instalar todo automáticamente (forma correcta)

npm install

Esto instala exactamente las versiones definidas en package.json, que es la mejor práctica.

Instalación manual (opcional)

4. Angular (mismas versiones del proyecto)

npm install @angular/animations@20.3.18 @angular/common@20.3.0 @angular/compiler@20.3.0 @angular/core@20.3.0 @angular/forms@20.3.0 @angular/platform-browser@20.3.0 @angular/router@20.3.0

5. Angular Material + CDK

npm install @angular/material@20.2.14 @angular/cdk@20.2.14

6. UI (Bootstrap + Popper)

npm install bootstrap@5.3.3 @popperjs/core@2.11.8

Luego en angular.json:

"styles": [

  "node_modules/bootstrap/dist/css/bootstrap.min.css",

  "src/styles.css"

]

7. Librerías funcionales

npm install chart.js@4.4.0 moment@2.30.1 ngx-pagination@6.0.3 ngx-toastr@19.0.0

8. Core interno

npm install rxjs@7.8.0 tslib@2.3.0 zone.js@0.15.0

Dependencias de desarrollo

9. Angular CLI y build

npm install -D @angular/cli@20.3.6 @angular/build@20.3.6 @angular/compiler-cli@20.3.0

10. TypeScript

npm install -D typescript@5.9.2

11. Testing (Karma + Jasmine + Jest)

npm install -D @types/jasmine@5.1.0 jasmine-core@5.6.0 karma@6.4.0 karma-chrome-launcher@3.2.0 karma-coverage@2.2.0 karma-jasmine@5.1.0 karma-jasmine-html-reporter@2.1.0

npm install -D jest@30.3.0 @types/jest@30.0.0 ts-jest@29.4.9

12. Tipos de Node

npm install -D @types/node@20.0.0

13. Documentación (Compodoc)

npm install -D @compodoc/compodoc@1.1.25

14. ESLint (opcional)

npm install -D eslint@9.0.0 @typescript-eslint/eslint-plugin@8.0.0 @typescript-eslint/parser@8.0.0

Angular CLI global

npm install -g @angular/cli@20.3.6

Verificar instalación

npm list --depth=0

Ejecutar proyecto

npm start

Generar documentación

npx compodoc -p tsconfig.json

Script completo de instalación

rm -r -fo node_modules

rm package-lock.json

npm cache clean --force

npm install

npm install -g @angular/cli@20.3.6

npm install bootstrap@5.3.3 @popperjs/core@2.11.8

npm install chart.js@4.4.0 moment@2.30.1 ngx-pagination@6.0.3 ngx-toastr@19.0.0

npm install -D @compodoc/compodoc@1.1.25

npm install -D eslint@9.0.0 @typescript-eslint/eslint-plugin@8.0.0 @typescript-eslint/parser@8.0.0

npm list --depth=0

**Tu proyecto es un ERP Vertical (Software de planificación de recursos para máquinas recreativas) con arquitectura y despliegue de tipo SaaS.**
