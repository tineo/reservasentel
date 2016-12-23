DELIMITER $$
CREATE TRIGGER add_new_notification
AFTER INSERT ON reservas FOR EACH ROW
  BEGIN
    INSERT INTO reservas_notificaciones (idreserva, hash, state, notify)
    VALUES (NEW.idreserva, MD5(CONCAT(NEW.idreserva,'1')), 0, 0);
  END $$

#DROP TRIGGER add_new_notification
