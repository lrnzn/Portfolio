function confirmCancel(reservationId) {
    if (confirm("Are you sure you want to cancel this reservation?\n\nThis action cannot be undone.")) {
        window.location.href = "api/cancel_reservation.php?id=" + reservationId;
    }
}

function confirmArchive(reservationID) {
    if (confirm("Archive this reservation? You can still restore it later.")) {
        window.location.href = "api/archive_reservation.php?id=" + reservationID;
    }
}

function restoreReservation(id) {
    if (confirm("Restore this reservation?\n\nIt will reappear in your reservation history.")) {
        window.location.href = "api/restore_reservation.php?id=" + id;
    }
}