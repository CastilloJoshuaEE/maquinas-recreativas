
Basado en TU sistema de máquinas recreativas:
Arquitectura Hexagonal (Ports and Adapters) + DDD + CQRS

"Arquitectura Hexagonal orientada a Domain-Driven Design (DDD) con implementación de CQRS"

backend/
├── .env
├── .htaccess (opcional)
├── Bootstrap/
│   ├── app.php
│   ├── cors.php
│   ├── env.php
│   ├── rate_limit.php
│   ├── security.php
│   └── session.php
├── Config/
│   ├── .usuarios_iniciales.lock
│   ├── app.php
│   ├── constants.php
│   ├── database.php
│   ├── dependencies.php
│   ├── env.php
│   └── Inserter.php
├── Core/
│   ├── App.php
│   ├── MiddlewarePipeline.php
│   ├── Request.php
│   ├── Response.php
│   └── Router.php
├── Domain/
│   ├── Comentario/
│   │   ├── Comentario.php
│   │   └── ComentarioRepository.php
│   ├── Comercio/
│   │   ├── Comercio.php
│   │   └── ComercioRepository.php
│   ├── Componente/
│   │   ├── Componente.php
│   │   ├── ComponenteRepository.php
│   │   ├── ComponenteUsuario.php
│   │   └── TipoComponente.php
│   ├── Distribucion/
│   │   ├── DistribucionRepository.php
│   │   └── InformeDistribucion.php
│   ├── Historial/
│   │   ├── HistorialActividad.php
│   │   ├── HistorialMaquina.php
│   │   └── HistorialRepository.php
│   ├── Maquina/
│   │   ├── EstadoMaquina.php
│   │   ├── EtapaMaquina.php
│   │   ├── MaquinaRecreativa.php
│   │   └── MaquinaRepository.php
│   ├── Montaje/
│   │   ├── Montaje.php
│   │   └── MontajeRepository.php
│   ├── Notificacion/
│   │   ├── NotificacionMaquina.php
│   │   ├── NotificacionReporte.php
│   │   └── NotificacionRepository.php
│   ├── Recaudacion/
│   │   ├── DetalleInforme.php
│   │   ├── InformeRecaudacion.php
│   │   ├── Recaudacion.php
│   │   └── RecaudacionRepository.php
│   ├── Reporte/
│   │   ├── EstadoReporte.php
│   │   ├── Reporte.php
│   │   └── ReporteRepository.php
│   └── Shared/
│       ├── Exceptions/
│       │   └── DomainException.php
│       └── ValueObjects/
│           ├── Email.php
│           └── Uuid.php
├── Infrastructure/
│   ├── Database/
│   │   ├── Database.php
│   │   └── Inserter.php
│   ├── Repositories/
│   │   ├── PDOAdministradorRepository.php
│   │   ├── PDOComentarioRepository.php
│   │   ├── PDOComercioRepository.php
│   │   ├── PDOComponenteRepository.php
│   │   ├── PDODistribucionRepository.php
│   │   ├── PDOHistorialRepository.php
│   │   ├── PDOMaquinaRepository.php
│   │   ├── PDOMontajeRepository.php
│   │   ├── PDONotificacionRepository.php
│   │   ├── PDORecaudacionRepository.php
│   │   ├── PDOReporteRepository.php
│   │   └── PDOUsuarioRepository.php
│   ├── Security/
│   │   ├── BcryptPasswordHasher.php
│   │   ├── CifradoHelper.php
│   │   ├── HistorialHelper.php
│   │   ├── PasswordHasher.php
│   │   ├── RateLimiter.php
│   │   ├── UsuarioHelper.php
│   │   └── ValidationHelper.php
│   └── Services/
│       └── Email/
│           └── BrevoEmailService.php
├── Application/
│   ├── Commands/
│   │   ├── Command.php
│   │   ├── CommandHandler.php
│   │   ├── Comentario/
│   │   │   ├── CrearComentarioCommand.php
│   │   │   ├── CrearComentarioHandler.php
│   │   │   ├── ActualizarComentarioCommand.php
│   │   │   ├── ActualizarComentarioHandler.php
│   │   │   ├── EliminarComentarioCommand.php
│   │   │   ├── EliminarComentarioHandler.php
│   │   │   ├── EditarComentarioCommand.php
│   │   │   └── EditarComentarioHandler.php
│   │   ├── Comercio/
│   │   │   ├── RegistrarComercioCommand.php
│   │   │   ├── RegistrarComercioHandler.php
│   │   │   ├── ActualizarComercioCommand.php
│   │   │   ├── ActualizarComercioHandler.php
│   │   │   ├── EliminarComercioCommand.php
│   │   │   └── EliminarComercioHandler.php
│   │   ├── Componente/
│   │   │   ├── AsignarCarcasaCommand.php
│   │   │   ├── AsignarCarcasaHandler.php
│   │   │   ├── LiberarComponenteCommand.php
│   │   │   ├── LiberarComponenteHandler.php
│   │   │   ├── LiberarComponentesCancelacion.php
│   │   │   ├── LiberarComponentesCancelacionHandler.php
│   │   │   ├── UsarComponenteCommand.php
│   │   │   └── UsarComponenteHandler.php
│   │   ├── Maquina/
│   │   │   ├── RegistrarMaquinaCommand.php
│   │   │   ├── RegistrarMaquinaHandler.php
│   │   │   ├── GenerarPlacaCommand.php
│   │   │   ├── GenerarPlacaHandler.php
│   │   │   ├── PonerOperativaCommand.php
│   │   │   ├── PonerOperativaHandler.php
│   │   │   ├── DarMantenimientoCommand.php
│   │   │   ├── DarMantenimientoHandler.php
│   │   │   ├── FinalizarMantenimientoCommand.php
│   │   │   ├── FinalizarMantenimientoHandler.php
│   │   │   ├── MandarAComprobacionCommand.php
│   │   │   ├── MandarAComprobacionHandler.php
│   │   │   ├── MandarADistribucionCommand.php
│   │   │   ├── MandarADistribucionHandler.php
│   │   │   ├── MandarAReensamblarCommand.php
│   │   │   ├── MandarAReensamblarHandler.php
│   │   │   ├── RegistrarMontajeCommand.php
│   │   │   └── RegistrarMontajeHandler.php
│   │   ├── Notificacion/
│   │   │   ├── CrearNotificacionMaquinaCommand.php
│   │   │   ├── CrearNotificacionMaquinaHandler.php
│   │   │   ├── CrearNotificacionReporteCommand.php
│   │   │   ├── CrearNotificacionReporteHandler.php
│   │   │   ├── MarcarComoLeidaCommand.php
│   │   │   ├── MarcarComoLeidaHandler.php
│   │   │   ├── MarcarTodasComoLeidasCommand.php
│   │   │   └── MarcarTodasComoLeidasHandler.php
│   │   ├── Recaudacion/
│   │   │   ├── RegistrarRecaudacionCommand.php
│   │   │   ├── RegistrarRecaudacionHandler.php
│   │   │   ├── ActualizarRecaudacionCommand.php
│   │   │   ├── ActualizarRecaudacionHandler.php
│   │   │   ├── EliminarRecaudacionCommand.php
│   │   │   ├── EliminarRecaudacionHandler.php
│   │   │   ├── GuardarInformeCommand.php
│   │   │   └── GuardarInformeHandler.php
│   │   ├── Reporte/
│   │   │   ├── CrearReporteCommand.php
│   │   │   ├── CrearReporteHandler.php
│   │   │   ├── ActualizarEstadoReporteCommand.php
│   │   │   └── ActualizarEstadoReporteHandler.php
│   │   ├── Usuario/
│   │   │   ├── LoginCommand.php
│   │   │   ├── LoginHandler.php
│   │   │   ├── LogoutCommand.php
│   │   │   ├── LogoutHandler.php
│   │   │   ├── RegistrarUsuarioCommand.php
│   │   │   ├── RegistrarUsuarioHandler.php
│   │   │   ├── RegistrarUsuarioAdminCommand.php
│   │   │   ├── RegistrarUsuarioAdminHandler.php
│   │   │   ├── ActualizarUsuarioCommand.php
│   │   │   ├── ActualizarUsuarioHandler.php
│   │   │   ├── EliminarUsuarioCommand.php
│   │   │   ├── EliminarUsuarioHandler.php
│   │   │   ├── CambiarEstadoUsuarioCommand.php
│   │   │   ├── CambiarEstadoUsuarioHandler.php
│   │   │   ├── RecuperarContrasenaCommand.php
│   │   │   ├── RecuperarContrasenaHandler.php
│   │   │   ├── RegistrarActividadCommand.php
│   │   │   └── RegistrarActividadHandler.php
│   │   └── Email/
│   │       ├── EnviarEmailCommand.php
│   │       └── EnviarEmailHandler.php
│   └── Queries/
│       ├── Query.php
│       ├── QueryHandler.php
│       └── (todas las queries organizadas por módulo como en tu estructura original)
├── Interfaces/Http/
│   ├── Controllers/
│   │   ├── AdministradorController.php
│   │   ├── ComentarioController.php
│   │   ├── ComercioController.php
│   │   ├── ComponenteController.php
│   │   ├── DistribucionController.php
│   │   ├── HistorialMaquinaController.php
│   │   ├── InformeController.php
│   │   ├── MaquinaController.php
│   │   ├── NotificacionController.php
│   │   ├── ReporteController.php
│   │   ├── TecnicoComprobadorController.php
│   │   ├── TecnicoEnsambladorController.php
│   │   ├── TecnicoMantenimientoController.php
│   │   ├── UsuarioController.php
│   │   └── EmailController.php
│   └── Routes/
│       ├── administrador.routes.php
│       ├── auth.routes.php
│       ├── comentario.routes.php
│       ├── comercio.routes.php
│       ├── componente.routes.php
│       ├── contabilidad.routes.php
│       ├── distribucion.routes.php
│       ├── historial.routes.php
│       ├── index.php
│       ├── maquina.routes.php
│       ├── notificacion.routes.php
│       ├── reporte.routes.php
│       ├── usuario.private.routes.php
│       └── usuario.public.routes.php
├── Middleware/
│   ├── AuthMiddleware.php
│   ├── CorsMiddleware.php
│   ├── JsonResponseMiddleware.php
│   ├── RateLimitMiddleware.php
│   └── RoleMiddleware.php
├── public/
│   ├── index.php
│   ├── robots.txt
│   ├── serve.php
│   └── sitemap.xml
├── public/docs/
│   ├── index.html
│   └── swagger-initializer.js
├── Swagger/
│   └── SwaggerConfig.php
├── Scripts/
│   └── hash.php
└── Storage/
    ├── Cache/
    ├── Logs/
    └── rate_limits.json
