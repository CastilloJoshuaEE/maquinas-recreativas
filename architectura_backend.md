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
│   ├──Comercio/
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
│       └── Valueobjects/
│           ├── Email.php
│           └── Uuid.php
├── Infrastructure/
│   ├── Database/
│   │   ├── Database.php
│   │   └── Inserter.php
│   ├── Repositories/
│   │   ├── MySQLAdministradorRepository.php
│   │   ├── MySQLComentarioRepository.php
│   │   ├── MySQLComercioRepository.php
│   │   ├── MySQLComponenteRepository.php
│   │   ├── MySQLDistribucionRepository.php
│   │   ├── MySQLHistorialRepository.php
│   │   ├── MySQLMaquinaRepository.php
│   │   ├── MySQLMontajeRepository.php
│   │   ├── MySQLNotificacionRepository.php
│   │   ├── MySQLRecaudacionRepository.php
│   │   ├── MySQLReporteRepository.php
│   │   └── MySQLUsuarioRepository.php
│   └── Security/
│       ├── BcryptPasswordHasher.php
│       ├── CifradoHelper.php
│       ├── HistorialHelper.php
│       ├── PasswordHasher.php
│       ├── RateLimiter.php
│       ├── UsuarioHelper.php
│       └── ValidationHelper.php
├── Application/
│   ├── Commands/Command.php y CommandHandler.php
│   │   ├── Comentario/
│   │   │   ├── CrearComentarioCommand.php
│   │   │   └── CrearComentarioHandler.php
│   │   ├── Comercio/

│   │   │   ├── ActualizarComercioCommand.php
│   │   │   └── ActualizarComercioHandler.php

│   │   │   ├── EliminarComercioCommand.php
│   │   │   └── EliminarComercioHandler.php

│   │   │   ├── RegistrarComercioCommand.php
│   │   │   └── RegistrarComercioHandler.php
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
│   │   │   ├── DarMantenimientoCommand.php
│   │   │   ├── DarMantenimientoHandler.php
│   │   │   ├── FinalizarMantenimientoCommand.php
│   │   │   ├── FinalizarMantenimientoHandler.php
│   │   │   ├── GenerarPlacaCommand.php
│   │   │   ├── GenerarPlacaHandler.php
│   │   │   ├── MandarAComprobacionCommand.php
│   │   │   ├── MandarAComprobacionHandler.php
│   │   │   ├── MandarADistribucionCommand.php
│   │   │   ├── MandarADistribucionHandler.php
│   │   │   ├── MandarAReensamblarCommand.php
│   │   │   ├── MandarAReensamblarHandler.php
│   │   │   ├── PonerOperativaCommand.php
│   │   │   ├── PonerOperativaHandler.php
│   │   │   ├── RegistrarMaquinaCommand.php
│   │   │   ├── RegistrarMaquinaHandler.php
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
│   │   │   ├── ActualizarRecaudacionCommand.php
│   │   │   ├── ActualizarRecaudacionHandler.php
│   │   │   ├── EliminarRecaudacionCommand.php
│   │   │   ├── EliminarRecaudacionHandler.php
│   │   │   ├── GuardarInformeCommand.php
│   │   │   ├── GuardarInformeHandler.php
│   │   │   ├── RegistrarRecaudacionCommand.php
│   │   │   └── RegistrarRecaudacionHandler.php
│   │   ├── Reporte/
│   │   │   ├── ActualizarEstadoReporteCommand.php
│   │   │   ├── ActualizarEstadoReporteHandler.php
│   │   │   ├── CrearReporteCommand.php
│   │   │   └── CrearReporteHandler.php
│   │   └── Usuario/
│   │       ├── ActualizarPerfilCommand.php
│   │       ├── ActualizarPerfilHandler.php
│   │       ├── ActualizarUsuarioAsignadoCommand.php
│   │       ├── ActualizarUsuarioAsignadoHandler.php
│   │       ├── ActualizarUsuarioCommand.php
│   │       ├── ActualizarUsuarioHandler.php
│   │       ├── CambiarEstadoUsuarioCommand.php
│   │       ├── CambiarEstadoUsuarioHandler.php
│   │       ├── EliminarUsuarioCommand.php
│   │       ├── EliminarUsuarioHandler.php
│   │       ├── LoginCommand.php
│   │       ├── LoginHandler.php
│   │       ├── LogoutCommand.php
│   │       ├── LogoutHandler.php
│   │       ├── RecuperarContrasenaCommand.php
│   │       ├── RecuperarContrasenaHandler.php
│   │       ├── RegistrarActividadCommand.php
│   │       ├── RegistrarActividadHandler.php
│   │       ├── RegistrarUsuarioAdminCommand.php
│   │       ├── RegistrarUsuarioAdminHandler.php
│   │       ├── RegistrarUsuarioCommand.php
│   │       └── RegistrarUsuarioHandler.php
│   └── Queries/Query.php y QueryHandler.php
│       ├── Comentario/
│       │   ├── ObtenerComentariosPorReporteQuery.php
│       │   └── ObtenerComentariosPorReporteHandler.php
│       ├── Comercio/
│       │   ├── ObtenerComerciosHandler.php
│       │   └── ObtenerComerciosQuery.php
│       ├── Componente/
│       │   ├── ObtenerComponentes.php
│       │   ├── ObtenerComponentesDisponiblesHandler.php
│       │   ├── ObtenerComponentesDisponiblesQuery.php
│       │   ├── ObtenerComponentesEnUsoHandler.php
│       │   ├── ObtenerComponentesEnUsoQuery.php
│       │   ├── ObtenerComponentesHandler.php
│       │   └── ObtenerComponentesQuery.php
│       ├── Distribucion/
│       │   ├── ObtenerInformesDistribucionHandler.php
│       │   └── ObtenerInformesDistribucionQuery.php
│       ├── Historial/
│       │   ├── ObtenerHistorialGeneralHandler.php
│       │   ├── ObtenerHistorialGeneralQuery.php
│       │   ├── ObtenerHistorialPorMaquinaHandler.php
│       │   ├── ObtenerHistorialPorMaquinaQuery.php
│       │   ├── ObtenerHistorialPorUsuarioHandler.php
│       │   ├── ObtenerHistorialPorUsuarioQuery.php
│       │   ├── ObtenerResumenRecienteHandler.php
│       │   └── ObtenerResumenRecienteQuery.php
│       ├── Maquina/
│       │   ├── ObtenerComponentesMaquinaHandler.php
│       │   ├── ObtenerComponentesMaquinaQuery.php
│       │   ├── ObtenerMaquinasParaDistribucionHandler.php
│       │   ├── ObtenerMaquinasParaDistribucionQuery.php
│       │   ├── ObtenerMaquinasPorEstadoHandler.php
│       │   ├── ObtenerMaquinasPorEstadoQuery.php
│       │   ├── ObtenerMaquinasPorEtapaHandler.php
│       │   ├── ObtenerMaquinasPorEtapaQuery.php
│       │   ├── ObtenerMaquinasPorTecnicoComprobadorHandler.php
│       │   ├── ObtenerMaquinasPorTecnicoComprobadorQuery.php
│       │   ├── ObtenerMaquinasPorTecnicoEnsambladorHandler.php
│       │   ├── ObtenerMaquinasPorTecnicoEnsambladorQuery.php
│       │   ├── ObtenerMaquinasPorTecnicoMantenimientoHandler.php
│       │   └── ObtenerMaquinasPorTecnicoMantenimientoQuery.php
│       ├── Notificacion/
│       │   ├── ObtenerCantidadNoLeidasHandler.php
│       │   ├── ObtenerCantidadNoLeidasQuery.php
│       │   ├── ObtenerNoLeidasHandler.php
│       │   ├── ObtenerNoLeidasQuery.php
│       │   ├── ObtenerNotificacionesMaquinaHandler.php
│       │   ├── ObtenerNotificacionesMaquinaQuery.php
│       │   ├── ObtenerNotificacionesReporteHandler.php
│       │   └── ObtenerNotificacionesReporteQuery.php
│       ├── Recaudacion/
│       │   ├── ObtenerComercioRecaudacionHandler.php
│       │   ├── ObtenerComercioRecaudacionQuery.php
│       │   ├── ObtenerInformePorRecaudacionHandler.php
│       │   ├── ObtenerInformePorRecaudacionQuery.php
│       │   ├── ObtenerMaquinasOperativasPorComercioHandler.php
│       │   ├── ObtenerMaquinasOperativasPorComercioQuery.php
│       │   ├── ObtenerMaquinasRecaudacionHandler.php
│       │   ├── ObtenerMaquinasRecaudacionQuery.php
│       │   ├── ObtenerRecaudacionesHandler.php
│       │   ├── ObtenerRecaudacionesQuery.php
│       │   ├── ObtenerRecaudacionPorIdHandler.php
│       │   ├── ObtenerRecaudacionPorIdQuery.php
│       │   ├── ObtenerResumenRecaudacionesHandler.php
│       │   └── ObtenerResumenRecaudacionesQuery.php
│       └── Usuario/
│           ├── BuscarPorEmailHandler.php
│           ├── BuscarPorEmailQuery.php
│           ├── ObtenerHistorialActividadesHandler.php
│           ├── ObtenerHistorialActividadesQuery.php
│           ├── ObtenerTecnicosPorEspecialidadHandler.php
│           ├── ObtenerTecnicosPorEspecialidad.php
│           ├── ObtenerTodosUsuariosHandler.php
│           ├── ObtenerTodosUsuarios.php
│           ├── ObtenerUsuarioPorIdHandler.php
│           ├── ObtenerUsuarioPorIdQuery.php
│           ├── ObtenerUsuariosPorTipoHandler.php
│           └── ObtenerUsuariosPorTipoQuery.php
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
│   │   └── UsuarioController.php
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

├── public/docs/index.html y swagger-initializer.js
├── Scripts/
│   └── hash.php
└── Storage/
    ├── Cache/
    ├── Logs/
    └── rate_limits.json

backend/Swagger/SwaggerConfig.php

`BrevoEmailService.php``backend/Infrastructure/Services/Email/``EnviarEmailCommand.php``backend/Application/Commands/Email/``EnviarEmailHandler.php``backend/Application/Commands/Email/``EmailController.php``backend/Interfaces/Http/Controllers/`


```
├── Application/
│   ├── Commands/Comentario/
│   │   ├── EditarComentarioCommand.php (nuevo)
│   │   ├── EditarComentarioHandler.php (nuevo)
│   │   ├── EliminarComentarioCommand.php (nuevo)
│   │   └── EliminarComentarioHandler.php (nuevo)
```
