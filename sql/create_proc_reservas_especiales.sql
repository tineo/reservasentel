DELIMITER //
CREATE PROCEDURE proc_reservas_especiales
  ( IN in_motivo TEXT,
    IN in_idsala INT(11),
    IN in_reserva INT(11))
  # in_flag -> 0 : false , 1 :true
  BEGIN

      INSERT INTO reservas_motivos
      (descipcion, idsala, idreserva)
      VALUES (in_motivo, in_idsala, in_reserva);

  END //
DELIMITER ;