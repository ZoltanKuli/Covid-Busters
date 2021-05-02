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

$appointment = $appointment_storage->findById($auth->authenticated_user()["vaccination-id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Covid Busters</title>
    <link rel="icon" href="assets/favicon.ico">

    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.14.0/css/v4-shims.css" rel="stylesheet">
    <link href="styles/main.css" rel="stylesheet" type="text/css">
    <link href="styles/index.css" rel="stylesheet" type="text/css">
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
                      <a href="index.php?logout=true">Logout</a>
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

                            <?php if (!is_null($appointment)) : ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <i class="fas fa-calendar-alt"></i>
                                            &nbsp;Vaccination Date
                                        </strong>
                                    </td>
                                    <td class="user-text">
                                        <?php
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

                    <?php if (!is_null($appointment) && strtotime('-1 day', mktime((int)$appointment["hour"], (int)$appointment["minute"], 0, (int)$appointment["month"],
                            (int)$appointment["day"], (int)$appointment["year"])) >= time()) : ?>
                        <div class="form-group d-flex justify-content-center">
                            <a href="index.php?cancel-appointment=true" type="submit" class="button-cancel btn">Cancel
                                My Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="disclaimer-container">
            <h1>Please be advised!</h1>

            <div class="disclaimer-main-components">
                <p>Our main goal is to protect you from Covid-19.</p>

                <p>And to do that, we need you to apply for vaccination and come to our health department at the
                    appropriate date and time.</p>

                <p>But before that, please understand that we try do our best to ensure your health and safety, but
                    there
                    might be some side effects to the vaccine.</p>

                <p>So, proceed with caution and at your own risk.</p>

                <p class="check-out-vaccination-dates"><a href="vaccination-dates.php">Check Out Our Vaccination
                        Dates</a></p>
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
