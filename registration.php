<?php
include_once('utils/misc.php');
include_once('utils/storage.php');
include_once('utils/auth.php');

function validate($post, &$data, &$errors)
{
    if (verify_post("fullname", "taj", "address", "email", "password", "confirm-password")) {
        $data = $post;

        if (strlen($data["password"]) <= 8) {
            $errors['registration-error'] = "Password must be at least 8 characters long";
        }

        if (strlen($data["password"]) > 32) {
            $errors['registration-error'] = "Password must be less than 32 characters long";
        }

        if ($data["password"] != $data["confirm-password"]) {
            $errors['registration-error'] = "Passwords do not match";
        }

        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $errors['registration-error'] = "Invalid email format";
        }

        if (strlen($data["taj"]) != 9) {
            $errors['registration-error'] = "Social security number must be 9 characters long";
        }

        if (!ctype_digit($data["taj"])) {
            $errors['registration-error'] = "Social security number must only contain numbers";
        }
    } else {
        $errors['registration-error'] = "You must fill out all of the required fields";
    }

    return count($errors) === 0;
}

session_start();
$user_storage = new UserStorage();
$auth = new Auth($user_storage);
$errors = [];
$data = [];

if ($auth->authenticated_user()) {
    redirectToFrontPage();
}

if (count($_POST) > 0) {
    if (validate($_POST, $data, $errors)) {
        if ($auth->user_exists((int)$data['taj'], $data['email'])) {
            $errors['registration-error'] = "An account with this email or social security number already exists";
        } else {
            $auth->register($data);
            redirect('login.php');
        }
    }
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Covid Busters - Registration</title>
    <link rel="icon" href="assets/favicon.ico">

    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.14.0/css/v4-shims.css" rel="stylesheet">
    <link href="styles/main.css" rel="stylesheet" type="text/css">
    <link href="styles/registration.css" rel="stylesheet" type="text/css">
    <link crossorigin="anonymous" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" rel="stylesheet">
</head>
<body>
<div class="d-flex flex-column min-vh-100">
    <header class="site-header">
          <span class="d-flex justify-content-between">
              <a href="index.php">Covid Busters</a>

              <a href="login.php">Login</a>
          </span>
    </header>

    <main class="main-container">
        <div class="registration-container">
            <h1>Register Your Account</h1>

            <div class="registration-main-components">
                <?php if (isset($errors['registration-error'])) : ?>
                    <p class="error form-text"><?= $errors['registration-error'] ?></p>
                <?php endif; ?>
                <form action="" method="post" novalidate>
                    <div class="form-group">
                        <label for="fullname" class="required-label">Full Name</label><br>
                        <input type="text" class="form-control" name="fullname" id="fullname"
                               placeholder="Your full name.." value="<?= $_POST['fullname'] ?? "" ?>">
                    </div>
                    <div class="form-group">
                        <label for="taj" class="required-label">Social Security Number</label><br>
                        <input type="text" class="form-control" name="taj" id="taj"
                               placeholder="Your social security number.." value="<?= $_POST['taj'] ?? "" ?>">
                    </div>
                    <div class="form-group">
                        <label for="address" class="required-label">Mailing Address</label><br>
                        <input type="text" class="form-control" name="address" id="address"
                               placeholder="Your mailing address.." value="<?= $_POST['address'] ?? "" ?>">
                    </div>
                    <div class="form-group">
                        <label for="email" class="required-label">Email Address</label><br>
                        <input type="email" class="form-control" name="email" id="email"
                               placeholder="Your email address.." value="<?= $_POST['email'] ?? "" ?>">
                    </div>
                    <div class="form-group">
                        <label for="password" class="required-label">Password</label><br>
                        <input type="password" class="form-control" name="password" id="password"
                               placeholder="Your password..">
                    </div>
                    <div class="form-group">
                        <label for="confirm-password" class="required-label">Confirm Password</label><br>
                        <input type="password" class="form-control" name="confirm-password" id="confirm-password"
                               placeholder="Your password..">
                    </div>
                    <div class="form-group d-flex justify-content-center">
                        <button type="submit" class="button-submit btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="site-footer mt-auto text-center">
        <p>Made by Zoltan Kuli</p>
    </footer>
</div>

<script crossorigin="anonymous" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script crossorigin="anonymous" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script crossorigin="anonymous" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@svgdotjs/svg.js@3.0/dist/svg.min.js"></script>
</body>
</html>
