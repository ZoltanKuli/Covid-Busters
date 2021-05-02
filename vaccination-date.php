<?php
include_once('utils/misc.php');
include_once('utils/storage.php');
include_once('utils/auth.php');

session_start();
$user_storage = new UserStorage();
$appointment_storage = new AppointmentStorage();
$auth = new Auth($user_storage);

if ($auth->is_authenticated() && verify_get("id", "year", "month") && !$auth->hasVaccinationID()) {
    $appointment = $appointment_storage->findById($_GET["id"]);
    $appointment_storage->addUserToAppointment($appointment, $auth->authenticated_user());
    $auth->updateVaccinationId($appointment["id"]);
    redirect("vaccination-dates.php?year=" . $_GET["year"] . "&month=" . $_GET["month"]);
} else {
    redirect("index.php");
}
