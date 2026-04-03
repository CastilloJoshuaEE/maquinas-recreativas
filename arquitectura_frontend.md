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

<pre class="overflow-visible! px-0!" data-start="496" data-end="541"><div class="relative w-full mt-4 mb-1"><div class=""><div class="relative"><div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0"><div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback"><div class="pointer-events-none absolute inset-x-4 top-12 bottom-4"><div class="pointer-events-none sticky z-40 shrink-0 z-1!"><div class="sticky bg-token-border-light"></div></div></div><div class="w-full overflow-x-hidden overflow-y-auto"><div class="relative z-0 flex max-w-full"><div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller"><div class="cm-content q9tKkq_readonly"><span class="ͼd">npm</span><span> install </span><span class="ͼf">-g</span><span> @compodoc/compodoc</span></div></div></div></div></div></div></div></div></div><div class=""><div class=""></div></div></div></div></div></pre>

O como dependencia del proyecto:

<pre class="overflow-visible! px-0!" data-start="577" data-end="630"><div class="relative w-full mt-4 mb-1"><div class=""><div class="relative"><div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0"><div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback"><div class="pointer-events-none absolute inset-x-4 top-12 bottom-4"><div class="pointer-events-none sticky z-40 shrink-0 z-1!"><div class="sticky bg-token-border-light"></div></div></div><div class="w-full overflow-x-hidden overflow-y-auto"><div class="relative z-0 flex max-w-full"><div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller"><div class="cm-content q9tKkq_readonly"><span class="ͼd">npm</span><span> install </span><span class="ͼf">--save-dev</span><span> @compodoc/compodoc</span></div></div></div></div></div></div></div></div></div><div class=""><div class=""></div></div></div></div></div></pre>

---

## 🔹 Cómo generar la documentación

<pre class="overflow-visible! px-0!" data-start="674" data-end="715"><div class="relative w-full mt-4 mb-1"><div class=""><div class="relative"><div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0"><div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback"><div class="pointer-events-none absolute inset-x-4 top-12 bottom-4"><div class="pointer-events-none sticky z-40 shrink-0 z-1!"><div class="sticky bg-token-border-light"></div></div></div><div class="w-full overflow-x-hidden overflow-y-auto"><div class="relative z-0 flex max-w-full"><div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller"><div class="cm-content q9tKkq_readonly"><span>npx compodoc </span><span class="ͼf">-p</span><span> tsconfig.json</span></div></div></div></div></div></div></div></div></div></div></div></div></pre>
