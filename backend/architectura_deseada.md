Basado en TU sistema de máquinas recreativas:
backend/
├── .env
├── .htaccess (opcional)
├── bootstrap/
│   ├── app.php
│   ├── cors.php
│   ├── env.php
│   ├── rate_limit.php
│   ├── security.php
│   └── session.php
├── config/
│   ├── .usuarios_iniciales.lock
│   ├── app.php
│   ├── constants.php
│   ├── database.php
│   ├── dependencies.php
│   ├── env.php
│   └── Inserter.php
├── core/
│   ├── App.php
│   ├── MiddlewarePipeline.php
│   ├── Request.php
│   ├── Response.php
│   └── Router.php
├── domain/
│   ├── comentario/
│   │   ├── Comentario.php
│   │   └── ComentarioRepository.php
│   ├── comercio/
│   │   ├── Comercio.php
│   │   └── ComercioRepository.php
│   ├── componente/
│   │   ├── Componente.php
│   │   ├── ComponenteRepository.php
│   │   ├── ComponenteUsuario.php
│   │   └── TipoComponente.php
│   ├── distribucion/
│   │   ├── DistribucionRepository.php
│   │   └── InformeDistribucion.php
│   ├── historial/
│   │   ├── HistorialActividad.php
│   │   ├── HistorialMaquina.php
│   │   └── HistorialRepository.php
│   ├── maquina/
│   │   ├── EstadoMaquina.php
│   │   ├── EtapaMaquina.php
│   │   ├── MaquinaRecreativa.php
│   │   └── MaquinaRepository.php
│   ├── montaje/
│   │   ├── Montaje.php
│   │   └── MontajeRepository.php
│   ├── notificacion/
│   │   ├── NotificacionMaquina.php
│   │   ├── NotificacionReporte.php
│   │   └── NotificacionRepository.php
│   ├── recaudacion/
│   │   ├── DetalleInforme.php
│   │   ├── InformeRecaudacion.php
│   │   ├── Recaudacion.php
│   │   └── RecaudacionRepository.php
│   ├── reporte/
│   │   ├── EstadoReporte.php
│   │   ├── Reporte.php
│   │   └── ReporteRepository.php
│   └── shared/
│       ├── exceptions/
│       │   └── DomainException.php
│       └── valueobjects/
│           ├── Email.php
│           └── Uuid.php
├── infrastructure/
│   ├── database/
│   │   ├── Database.php
│   │   └── Inserter.php
│   ├── repositories/
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
│   └── security/
│       ├── BcryptPasswordHasher.php
│       ├── CifradoHelper.php
│       ├── HistorialHelper.php
│       ├── PasswordHasher.php
│       ├── RateLimiter.php
│       ├── UsuarioHelper.php
│       └── ValidationHelper.php
├── application/
│   ├── commands/
│   │   ├── comentario/
│   │   │   ├── CrearComentarioCommand.php
│   │   │   └── CrearComentarioHandler.php
│   │   ├── comercio/
│   │   │   ├── RegistrarComercioCommand.php
│   │   │   └── RegistrarComercioHandler.php
│   │   ├── componente/
│   │   │   ├── AsignarCarcasaCommand.php
│   │   │   ├── AsignarCarcasaHandler.php
│   │   │   ├── LiberarComponenteCommand.php
│   │   │   ├── LiberarComponenteHandler.php
│   │   │   ├── LiberarComponentesCancelacion.php
│   │   │   ├── LiberarComponentesCancelacionHandler.php
│   │   │   ├── UsarComponenteCommand.php
│   │   │   └── UsarComponenteHandler.php
│   │   ├── maquina/
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
│   │   ├── notificacion/
│   │   │   ├── CrearNotificacionMaquinaCommand.php
│   │   │   ├── CrearNotificacionMaquinaHandler.php
│   │   │   ├── CrearNotificacionReporteCommand.php
│   │   │   ├── CrearNotificacionReporteHandler.php
│   │   │   ├── MarcarComoLeidaCommand.php
│   │   │   ├── MarcarComoLeidaHandler.php
│   │   │   ├── MarcarTodasComoLeidasCommand.php
│   │   │   └── MarcarTodasComoLeidasHandler.php
│   │   ├── recaudacion/
│   │   │   ├── ActualizarRecaudacionCommand.php
│   │   │   ├── ActualizarRecaudacionHandler.php
│   │   │   ├── EliminarRecaudacionCommand.php
│   │   │   ├── EliminarRecaudacionHandler.php
│   │   │   ├── GuardarInformeCommand.php
│   │   │   ├── GuardarInformeHandler.php
│   │   │   ├── RegistrarRecaudacionCommand.php
│   │   │   └── RegistrarRecaudacionHandler.php
│   │   ├── reporte/
│   │   │   ├── ActualizarEstadoReporteCommand.php
│   │   │   ├── ActualizarEstadoReporteHandler.php
│   │   │   ├── CrearReporteCommand.php
│   │   │   └── CrearReporteHandler.php
│   │   └── usuario/
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
│   └── queries/
│       ├── comentario/
│       │   ├── ObtenerComentariosPorReporte.php
│       │   └── ObtenerComentariosPorReporteHandler.php
│       ├── comercio/
│       │   ├── ObtenerComerciosHandler.php
│       │   └── ObtenerComerciosQuery.php
│       ├── componente/
│       │   ├── ObtenerComponentes.php
│       │   ├── ObtenerComponentesDisponiblesHandler.php
│       │   ├── ObtenerComponentesDisponiblesQuery.php
│       │   ├── ObtenerComponentesEnUsoHandler.php
│       │   ├── ObtenerComponentesEnUsoQuery.php
│       │   ├── ObtenerComponentesHandler.php
│       │   └── ObtenerComponentesQuery.php
│       ├── distribucion/
│       │   ├── ObtenerInformesDistribucionHandler.php
│       │   └── ObtenerInformesDistribucionQuery.php
│       ├── historial/
│       │   ├── ObtenerHistorialGeneralHandler.php
│       │   ├── ObtenerHistorialGeneralQuery.php
│       │   ├── ObtenerHistorialPorMaquinaHandler.php
│       │   ├── ObtenerHistorialPorMaquinaQuery.php
│       │   ├── ObtenerHistorialPorUsuarioHandler.php
│       │   ├── ObtenerHistorialPorUsuarioQuery.php
│       │   ├── ObtenerResumenRecienteHandler.php
│       │   └── ObtenerResumenRecienteQuery.php
│       ├── maquina/
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
│       ├── notificacion/
│       │   ├── ObtenerCantidadNoLeidasHandler.php
│       │   ├── ObtenerCantidadNoLeidasQuery.php
│       │   ├── ObtenerNoLeidasHandler.php
│       │   ├── ObtenerNoLeidasQuery.php
│       │   ├── ObtenerNotificacionesMaquinaHandler.php
│       │   ├── ObtenerNotificacionesMaquinaQuery.php
│       │   ├── ObtenerNotificacionesReporteHandler.php
│       │   └── ObtenerNotificacionesReporteQuery.php
│       ├── recaudacion/
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
│       └── usuario/
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
├── interfaces/http/
│   ├── controllers/
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
│   └── routes/
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
├── middleware/
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
├── scripts/
│   └── hash.php
└── storage/
    ├── cache/
    ├── logs/
    └── rate_limits.json
