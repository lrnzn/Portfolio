<?php
require 'connection.php';

$sql = "
    UPDATE tbl_reservation
    SET status = 'Completed'
    WHERE status = 'Reserved'
    AND manually_completed = 0
    AND NOW() >= ADDTIME(
        CONCAT(date, ' ', time),
        SEC_TO_TIME(duration * 3600)
    )
";

$conn->query($sql);
