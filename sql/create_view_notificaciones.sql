CREATE VIEW notificaciones AS
SELECT
  r.idreserva,
  r.idsala as sala,
  s.piso,
  u.nombre as sede,
  hash,
  state,
  notify,
  fecha_reserva,
  r.horario_inicio,
  r.horario_final,
  CONVERT(((TO_SECONDS(
      CONCAT(
          DATE(r.fecha_reserva),
          ' ',
          r.horario_inicio,
          ':00')
  ) - TO_SECONDS(NOW()))/60),SIGNED) as diff_min,

  estado,
  fecha_registro,
  nombres_apellidos,
  email
FROM
  reservas_notificaciones rn INNER JOIN reservas r
    ON rn.idreserva = r.idreserva
  INNER JOIN seg_usuarios su
    ON su.idusuario = r.idusuario
  INNER JOIN salas s
      ON s.idsala = r.idsala
  INNER JOIN ubicaciones u
      ON s.idubicacion = u.idubicacion

