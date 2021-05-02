<?php
include_once('utils/misc.php');
include_once('utils/storage.php');
include_once('utils/auth.php');

session_start();
$user_storage = new UserStorage();
$appointment_storage = new AppointmentStorage();
$auth = new Auth($user_storage);

if ($auth->is_authenticated() && verify_get("cancel-appointment") && $_GET["cancel-appointment"] == "true") {
    $appointment = $appointment_storage->findById($auth->authenticated_user()["vaccination-id"]);
    $appointment_storage->removeUserFromAppointment($appointment, $auth->authenticated_user());
    $auth->updateVaccinationId("");
}

if ($auth->is_authenticated() && verify_get("logout") && $_GET["logout"] == "true") {
    $auth->logout();
}

$year = 0;
$month = 0;
if (!verify_get("year", "month")) {
    $year = (int)date("Y");
    $month = (int)date("n");

    redirect("vaccination-dates.php?year=" . $year . "&month=" . $month);
} else {
    if ($_GET["month"] < 1 || $_GET["month"] > 12 || $_GET["year"] < 2020 || $_GET["year"] > 2030) {
        redirect("vaccination-dates.php");
    }

    $year = (int)$_GET["year"];
    $month = (int)$_GET["month"];
}

$previousYear = $year;
$previousMonth = $month - 1;
if ($month == 1) {
    $previousMonth = 12;
    $previousYear = $year - 1;
}

$nextYear = $year;
$nextMonth = $month + 1;
if ($month == 12) {
    $nextMonth = 1;
    $nextYear = $year + 1;
}

$errors = [];
if (verify_post("date-input", "time-input", "number-input")) {
    if ($_POST["number-input"] < 1) {
        $errors['new-date-error'] = "Number of patients must be at least 1";
    }

    if ($_POST["number-input"] > 8) {
        $errors['new-date-error'] = "Number of patients must be at maximum 8";
    }

    if (strtotime($_POST["date-input"] . $_POST["time-input"]) < strtotime('+2 day', time())) {
        $errors['new-date-error'] = "The date and time must be at least 48 hours from now";
    }

    if (strtotime($_POST["date-input"] . $_POST["time-input"]) >= strtotime('2030/01/01')) {
        $errors['new-date-error'] = "The date and time must be sooner than 2030";
    }

    if (count($errors) === 0) {
        $appointment_storage->addAppointment([
            'year' => (int)date("Y", strtotime($_POST["date-input"])),
            'month' => (int)date("n", strtotime($_POST["date-input"])),
            'day' => (int)date("j", strtotime($_POST["date-input"])),
            'hour' => (int)date("H", strtotime($_POST["time-input"])),
            'minute' => (int)date("i", strtotime($_POST["time-input"])),
            'max-user-num' => (int)$_POST["number-input"]
        ]);

        /*for ($y = 9; $y < 16; $y = $y + 2) {
            $timey = mktime($y, 00, 0, 12,
                1, 2020);

            for ($x = 0; $x < 150; $x = $x + 2) {
                $one = $timey;
                $two = $timey;
                $three = $timey;
                $four = $timey;
                $five = $timey;
                $timey = strtotime('+1 day', $timey);

                $appointment_storage->addAppointment([
                    'year' => (int)date("Y", $one),
                    'month' => (int)date("n",$two),
                    'day' => (int)date("j", $three),
                    'hour' => (int)date("H", $four),
                    'minute' => (int)date("i", $five),
                    'max-user-num' => (int)5
                ]);
            }
        }*/

        redirect("vaccination-dates.php?year=" . date("Y", strtotime($_POST["date-input"])) . "&month=" . date("n", strtotime($_POST["date-input"])));
    }
}

$appointments = array_values($appointment_storage->getAppointmentsByYearAndMonth($year, $month));
usort($appointments, function ($a, $b) {
    return mktime((int)$a["hour"], (int)$a["minute"], 0, (int)$a["month"],
            (int)$a["day"], (int)$a["year"]) <=> mktime((int)$b["hour"], (int)$b["minute"], 0, (int)$b["month"],
            (int)$b["day"], (int)$b["year"]);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Covid Busters - Vaccinations Dates</title>
    <link rel="icon" href="assets/favicon.ico">

    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.14.0/css/v4-shims.css" rel="stylesheet">
    <link href="styles/main.css" rel="stylesheet" type="text/css">
    <link href="styles/vaccination-dates.css" rel="stylesheet" type="text/css">
    <link crossorigin="anonymous" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" rel="stylesheet">
</head>
<body>
<div class="d-flex flex-column min-vh-100">
    <header class="site-header">
          <span class="d-flex justify-content-between">
              <a href="index.php">Covid Busters</a>

              <span class="logging-registration-link-container">
                  <?php if ($auth->authenticated_user()) { ?>
                      <a href="vaccination-dates.php?logout=true">Logout</a>
                  <?php } else { ?>
                      <a class="px-2" href="login.php">Login</a>
                      <a href="registration.php">Register</a>
                  <?php } ?>
              </span>
          </span>
    </header>

    <main class="main-container">
        <?php if ($auth->is_authenticated()) : ?>
            <div class="user-data-container">
                <h1>User Information</h1>

                <div class="user-data-main-components">
                    <div class="table-responsive">
                        <table class="table table-user-information">
                            <tbody>
                            <tr>
                                <td>
                                    <strong>
                                        <i class="fas fa-signature"></i>
                                        &nbsp;Full Name
                                    </strong>
                                </td>
                                <td class="user-text">
                                    <?php print $auth->authenticated_user()["fullname"] ?>
                                </td>
                            </tr>

                            <?php if ($auth->authenticated_user()["role"] == "User") : ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <i class="fas fa-id-card"></i>
                                            &nbsp;Social Security Number
                                        </strong>
                                    </td>
                                    <td class="user-text">
                                        <?php print $auth->authenticated_user()["taj"] ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if ($auth->authenticated_user()["role"] == "User") : ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <i class="fas fa-envelope"></i>
                                            &nbsp;Mailing Address
                                        </strong>
                                    </td>
                                    <td class="user-text">
                                        <?php print $auth->authenticated_user()["address"] ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <tr>
                                <td>
                                    <strong>
                                        <i class="fas fa-at"></i>
                                        &nbsp;Email Address
                                    </strong>
                                </td>
                                <td class="user-text">
                                    <?php print $auth->authenticated_user()["email"] ?>
                                </td>
                            </tr>

                            <?php if ($auth->authenticated_user()["role"] == "Admin") : ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <i class="fas fa-unlock-alt"></i>
                                            &nbsp;Role
                                        </strong>
                                    </td>
                                    <td class="user-text">
                                        <?php print $auth->authenticated_user()["role"] ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if ($auth->hasVaccinationID()) : ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <i class="fas fa-calendar-alt"></i>
                                            &nbsp;Vaccination Date
                                        </strong>
                                    </td>
                                    <td class="user-text">
                                        <?php
                                        $appointment = $appointment_storage->findById($auth->authenticated_user()["vaccination-id"]);

                                        $formattedAppointment = date("m/d/Y h:i A",
                                            mktime((int)$appointment["hour"], (int)$appointment["minute"], 0, (int)$appointment["month"],
                                                (int)$appointment["day"], (int)$appointment["year"]));
                                        print $formattedAppointment;
                                        ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($auth->hasVaccinationID() && strtotime('-1 day', mktime((int)$appointment["hour"], (int)$appointment["minute"], 0, (int)$appointment["month"],
                            (int)$appointment["day"], (int)$appointment["year"])) >= time()) : ?>
                        <div class="form-group d-flex justify-content-center">
                            <a href="vaccination-dates.php?cancel-appointment=true&<?php print "year=" . $year . "&month=" . $month ?>"
                               type="submit" class="button-cancel btn">Cancel My Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($auth->authenticated_user()["role"] == "Admin") : ?>
                <div class="new-vaccination-date-container">
                    <h1>Add a New Vaccination Date</h1>

                    <div class="new-vaccination-date-main-components">
                        <?php if (isset($errors['new-date-error'])) : ?>
                            <p class="error form-text"><?= $errors['new-date-error'] ?></p>
                        <?php endif; ?>
                        <form action="" method="post" novalidate>
                            <div class="form-group">
                                <label for="date-input">Date</label>
                                <input class="form-control" type="date" name="date-input" id="date-input"
                                       value="<?= $_POST['date-input'] ?? "" ?>">
                            </div>
                            <div class="form-group">
                                <label for="time-input">Time</label>
                                <input class="form-control" type="time" name="time-input" id="time-input"
                                       value="<?= $_POST['time-input'] ?? "" ?>">
                            </div>
                            <div class="form-group">
                                <label for="number-input">Number of Patients</label>
                                <input class="form-control" type="number" name="number-input"
                                       placeholder="Number of Patients..." id="number-input"
                                       value="<?= $_POST['number-input'] ?? "" ?>">
                            </div>
                            <div class="form-group d-flex justify-content-center">
                                <button type="submit" class="button-submit btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="vaccination-dates-container">
            <h1>Vaccination Dates</h1>

            <div class="vaccination-dates-main-components">
                <div class="list-group">
                    <?php if (count($appointments) == 0): ?>
                        <p class="form-text"><?= "No data could be found for " . date("F Y", mktime(0, 0, 0, $month,
                                1, $year)) ?></p>
                    <?php endif; ?>

                    <?php foreach ($appointments as $appointment) {
                        print '<a type="button"';
                        (($appointment["max-user-num"] > count($appointment["user-ids"]) && $auth->is_authenticated() && (($auth->is_authorized("User") && !$auth->hasVaccinationID() && strtotime('-1 day', mktime((int)$appointment["hour"], (int)$appointment["minute"], 0, (int)$appointment["month"],
                                    (int)$appointment["day"], (int)$appointment["year"])) >= time())))) ?
                            print ' href="vaccination-date.php?id=' . $appointment["id"] .'&year='.$year.'&month='.$month.'"' : print "";
                        print  'class="d-flex justify-content-between list-group-item list-group-item-action ';
                        if ($auth->is_authenticated() && $auth->is_authorized("User") && !$auth->hasVaccinationID()) {
                            ($appointment["max-user-num"] > count($appointment["user-ids"]) && strtotime('-1 day', mktime((int)$appointment["hour"], (int)$appointment["minute"], 0, (int)$appointment["month"],
                                    (int)$appointment["day"], (int)$appointment["year"])) >= time()) ? print "list-group-item-available" : print "list-group-item-full-no-btn";
                        }/* else if ($auth->is_authenticated() && $auth->is_authorized("Admin")) {
                            ($appointment["max-user-num"] > count($appointment["user-ids"]) && strtotime('-1 day', mktime((int) $appointment["hour"], (int) $appointment["minute"], 0, (int) $appointment["month"],
                                    (int) $appointment["day"], (int) $appointment["year"])) >= time()) ? print "list-group-item-available" : print "list-group-item-full";
                        } */ else {
                            ($appointment["max-user-num"] > count($appointment["user-ids"]) && strtotime('-1 day', mktime((int)$appointment["hour"], (int)$appointment["minute"], 0, (int)$appointment["month"],
                                    (int)$appointment["day"], (int)$appointment["year"])) >= time()) ? print "list-group-item-available-no-btn" : print "list-group-item-full-no-btn";
                        }

                        print '"><span>' .
                            date("m/d/Y h:i A",
                                mktime((int)$appointment["hour"], (int)$appointment["minute"], 0, (int)$appointment["month"],
                                    (int)$appointment["day"], (int)$appointment["year"])) . '</span>';
                        print '<span>[' . count($appointment["user-ids"]) . '/' . $appointment["max-user-num"] . ']</span>';
                        print '</a>';
                    }
                    ?>
                </div>

                <div class="row d-flex">
                    <a href="<?php print "vaccination-dates.php?year=" . $previousYear . "&month=" . $previousMonth ?>"
                       type="button" class="button btn col mr-2">Previous Month</a>
                    <a href="<?php print "vaccination-dates.php?year=" . $nextYear . "&month=" . $nextMonth ?>"
                       type="button" class="button btn col ml-2">Next Month</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer mt-auto text-center">
        <p>Made by Zoltan Kuli</p>
    </footer>
</div>

<script crossorigin="anonymous"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script crossorigin="anonymous"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script crossorigin="anonymous"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@svgdotjs/svg.js@3.0/dist/svg.min.js"></script>
</body>
</html>
