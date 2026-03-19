Basado en TU sistema de máquinas recreativas:
backend/
│
├── public/                              ← entrada pública
│   ├── index.php                         ← solo arranque mínimo
│   └── serve.php
│
├── bootstrap/                           ← inicialización general
│   ├── app.php                          ← carga todo el sistema
│   ├── env.php                          ← lee .env
│   ├── session.php                      ← sesión/cookies
│   ├── security.php                     ← headers de seguridad
│   ├── cors.php                         ← reglas CORS
│   └── rate_limit.php                   ← rate limiting
│
├── core/                                ← núcleo técnico
│   ├── App.php                          ← orquestación principal
│   ├── Router.php                       ← router central
│   ├── Request.php                      ← request HTTP
│   ├── Response.php                     ← respuesta JSON
│   └── MiddlewarePipeline.php           ← cadena de middlewares
│
├── middleware/                          ← middlewares reutilizables
│   ├── AuthMiddleware.php
│   ├── RoleMiddleware.php
│   ├── CorsMiddleware.php
│   ├── RateLimitMiddleware.php
│   └── JsonResponseMiddleware.php
│
├── domain/                              ← núcleo del negocio
│   ├── shared/
│   │   ├── ValueObjects/
│   │   │   ├── Uuid.php
│   │   │   └── Email.php
│   │   └── Exceptions/
│   │       └── DomainException.php
│   │
│   ├── usuario/
│   │   ├── Usuario.php
│   │   ├── Tecnico.php
│   │   ├── Logistica.php
│   │   ├── TipoUsuario.php
│   │   ├── EstadoUsuario.php
│   │   └── UsuarioRepository.php
│   │
│   ├── comercio/
│   │   ├── Comercio.php
│   │   └── ComercioRepository.php
│   │
│   ├── maquina/
│   │   ├── MaquinaRecreativa.php
│   │   ├── EstadoMaquina.php
│   │   ├── EtapaMaquina.php
│   │   └── MaquinaRepository.php
│   │
│   ├── componente/
│   │   ├── Componente.php
│   │   ├── TipoComponente.php
│   │   ├── ComponenteUsuario.php
│   │   └── ComponenteRepository.php
│   │
│   ├── montaje/
│   │   ├── Montaje.php
│   │   └── MontajeRepository.php
│   │
│   ├── historial/
│   │   ├── HistorialMaquina.php
│   │   ├── HistorialActividad.php
│   │   └── HistorialRepository.php
│   │
│   ├── distribucion/
│   │   ├── InformeDistribucion.php
│   │   └── DistribucionRepository.php
│   │
│   ├── recaudacion/
│   │   ├── Recaudacion.php
│   │   ├── InformeRecaudacion.php
│   │   ├── DetalleInforme.php
│   │   └── RecaudacionRepository.php
│   │
│   ├── notificacion/
│   │   ├── NotificacionMaquina.php
│   │   ├── NotificacionReporte.php
│   │   └── NotificacionRepository.php
│   │
│   ├── reporte/
│   │   ├── Reporte.php
│   │   ├── EstadoReporte.php
│   │   └── ReporteRepository.php
│   │
│   └── comentario/
│       ├── Comentario.php
│       └── ComentarioRepository.php
│
├── application/                         ← casos de uso
│   ├── commands/
│   │   ├── usuario/
│   │   │   ├── RegistrarUsuario.php
│   │   │   ├── RegistrarUsuarioAdmin.php
│   │   │   ├── ActualizarPerfil.php
│   │   │   ├── ActualizarUsuarioAsignado.php
│   │   │   ├── RecuperarContrasena.php
│   │   │   ├── CambiarEstadoUsuario.php
│   │   │   ├── EliminarUsuario.php
│   │   │   ├── Login.php
│   │   │   ├── Logout.php
│   │   │   └── RegistrarActividad.php
│   │   │
│   │   ├── comercio/
│   │   │   └── RegistrarComercio.php
│   │   │
│   │   ├── maquina/
│   │   │   ├── RegistrarMaquina.php
│   │   │   ├── GenerarPlaca.php
│   │   │   ├── MandarAComprobacion.php
│   │   │   ├── MandarAReensamblar.php
│   │   │   ├── MandarADistribucion.php
│   │   │   ├── PonerOperativa.php
│   │   │   ├── DarMantenimiento.php
│   │   │   ├── FinalizarMantenimiento.php
│   │   │   └── RegistrarMontaje.php
│   │   │
│   │   ├── componente/
│   │   │   ├── UsarComponente.php
│   │   │   ├── LiberarComponente.php
│   │   │   ├── AsignarCarcasa.php
│   │   │   └── LiberarComponentesCancelacion.php
│   │   │
│   │   ├── recaudacion/
│   │   │   ├── RegistrarRecaudacion.php
│   │   │   ├── ActualizarRecaudacion.php
│   │   │   ├── EliminarRecaudacion.php
│   │   │   └── GuardarInforme.php
│   │   │
│   │   ├── notificacion/
│   │   │   ├── CrearNotificacionMaquina.php
│   │   │   ├── CrearNotificacionReporte.php
│   │   │   ├── MarcarComoLeida.php
│   │   │   └── MarcarTodasComoLeidas.php
│   │   │
│   │   ├── reporte/
│   │   │   ├── CrearReporte.php
│   │   │   └── ActualizarEstadoReporte.php
│   │   │
│   │   └── comentario/
│   │       └── CrearComentario.php
│   │
│   └── queries/
│       ├── usuario/
│       │   ├── ObtenerUsuarioPorId.php
│       │   ├── ObtenerTodosUsuarios.php
│       │   ├── ObtenerTecnicosPorEspecialidad.php
│       │   ├── ObtenerUsuariosPorTipo.php
│       │   ├── BuscarPorEmail.php
│       │   └── ObtenerHistorialActividades.php
│       │
│       ├── comercio/
│       │   └── ObtenerComercios.php
│       │
│       ├── maquina/
│       │   ├── ObtenerMaquinasPorTecnicoEnsamblador.php
│       │   ├── ObtenerMaquinasPorTecnicoComprobador.php
│       │   ├── ObtenerMaquinasPorTecnicoMantenimiento.php
│       │   ├── ObtenerMaquinasPorEstado.php
│       │   ├── ObtenerMaquinasPorEtapa.php
│       │   ├── ObtenerMaquinasParaDistribucion.php
│       │   └── ObtenerComponentesMaquina.php
│       │
│       ├── componente/
│       │   ├── ObtenerComponentes.php
│       │   ├── ObtenerComponentesDisponibles.php
│       │   └── ObtenerComponentesEnUso.php
│       │
│       ├── historial/
│       │   ├── ObtenerHistorialPorMaquina.php
│       │   ├── ObtenerHistorialPorUsuario.php
│       │   ├── ObtenerHistorialGeneral.php
│       │   └── ObtenerResumenReciente.php
│       │
│       ├── distribucion/
│       │   └── ObtenerInformesDistribucion.php
│       │
│       ├── recaudacion/
│       │   ├── ObtenerRecaudaciones.php
│       │   ├── ObtenerResumenRecaudaciones.php
│       │   ├── ObtenerRecaudacionPorId.php
│       │   ├── ObtenerInformePorRecaudacion.php
│       │   ├── ObtenerMaquinasRecaudacion.php
│       │   ├── ObtenerMaquinasOperativasPorComercio.php
│       │   └── ObtenerComercioRecaudacion.php
│       │
│       ├── notificacion/
│       │   ├── ObtenerNotificacionesMaquina.php
│       │   ├── ObtenerNotificacionesReporte.php
│       │   ├── ObtenerNoLeidas.php
│       │   └── ObtenerCantidadNoLeidas.php
│       │
│       ├── reporte/
│       │   ├── ObtenerReportesPorUsuario.php
│       │   ├── ObtenerReportePorId.php
│       │   ├── ObtenerChat.php
│       │   ├── ObtenerChatCompleto.php
│       │   └── ObtenerUsuariosChat.php
│       │
│       └── comentario/
│           └── ObtenerComentariosPorReporte.php
│
├── infrastructure/                      ← implementación concreta
│   ├── database/
│   │   ├── Database.php
│   │   └── Inserter.php
│   │
│   ├── repositories/
│   │   ├── MySQLUsuarioRepository.php
│   │   ├── MySQLAdministradorRepository.php
│   │   ├── MySQLComercioRepository.php
│   │   ├── MySQLMaquinaRepository.php
│   │   ├── MySQLComponenteRepository.php
│   │   ├── MySQLMontajeRepository.php
│   │   ├── MySQLHistorialRepository.php
│   │   ├── MySQLDistribucionRepository.php
│   │   ├── MySQLRecaudacionRepository.php
│   │   ├── MySQLNotificacionRepository.php
│   │   ├── MySQLReporteRepository.php
│   │   └── MySQLComentarioRepository.php
│   │
│   └── security/
│       ├── CifradoHelper.php
│       ├── RateLimiter.php
│       ├── UsuarioHelper.php
│       ├── ValidationHelper.php
│       └── HistorialHelper.php
│
├── interfaces/
│   └── http/
│       ├── controllers/
│       │   ├── UsuarioController.php
│       │   ├── AdministradorController.php
│       │   ├── ComercioController.php
│       │   ├── MaquinaController.php
│       │   ├── ComponenteController.php
│       │   ├── HistorialMaquinaController.php
│       │   ├── DistribucionController.php
│       │   ├── InformeController.php
│       │   ├── NotificacionController.php
│       │   ├── ReporteController.php
│       │   └── ComentarioController.php
│       │
│       └── routes/
│           ├── index.php
│           ├── auth.routes.php
│           ├── usuario.public.routes.php
│           ├── usuario.private.routes.php
│           ├── administrador.routes.php
│           ├── comercio.routes.php
│           ├── maquina.routes.php
│           ├── componente.routes.php
│           ├── distribucion.routes.php
│           ├── contabilidad.routes.php
│           ├── historial.routes.php
│           ├── notificacion.routes.php
│           ├── reporte.routes.php
│           └── comentario.routes.php
│
├── config/
│   ├── env.php
│   ├── database.php
│   ├── app.php
│   └── constants.php
│
├── storage/
│   └── rate_limits.json
│
├── tests/
│   ├── Unit/
│   ├── Integration/
│   ├── Functional/
│   ├── Performance/
│   ├── Smoke/
│   └── bootstrap.php
│
└── .env
