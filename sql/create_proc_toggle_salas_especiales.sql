DELIMITER //
CREATE PROCEDURE toggle_salas_especiales
  (IN in_idsala INT(11), IN in_flag INT(1))
  # in_flag -> 0 : false , 1 :true
  BEGIN
    DECLARE cantidad INT;

    SELECT COUNT(*) as cant FROM salas_especiales WHERE idsala = in_idsala INTO cantidad;

    IF cantidad <= 0 AND in_flag > 0  THEN #empty
      INSERT INTO salas_especiales (idsala) VALUES (in_idsala);
    ELSEIF cantidad > 0 AND in_flag <= 0 THEN
      DELETE FROM salas_especiales WHERE idsala = in_idsala;
    END IF;
  END //
DELIMITER ;