<?php
include_once('utils/misc.php');
include_once('utils/storage.php');
include_once('utils/auth.php');

function validate($post, &$data, &$errors)
{
    if (verify_post("email", "password")) {
        $data = $post;

        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $errors['login-error'] = "Invalid email format";
        }
    } else {
        $errors['login-error'] = "You must fill out all of the required fields";
    }

    return count($errors) === 0;
}

session_start();
$user_storage = new UserStorage();
$auth = new Auth($user_storage);
$data = [];
$errors = [];

if ($auth->authenticated_user()) {
    redirectToFrontPage();
}

if ($_POST) {
    if (validate($_POST, $data, $errors)) {
        $auth_user = $auth->authenticate($data['email'], $data['password']);
        if (!$auth_user) {
            $errors['login-error'] = "Wrong email or password";
        } else {
            $auth->login($auth_user);
            redirect('index.php');
        }
    }
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Covid Busters - Login</title>
    <link rel="icon" href="assets/favicon.ico">

    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.14.0/css/v4-shims.css" rel="stylesheet">
    <link href="styles/main.css" rel="stylesheet" type="text/css">
    <link href="styles/login.css" rel="stylesheet" type="text/css">
    <link crossorigin="anonymous" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" rel="stylesheet">
</head>
<body>
<div class="d-flex flex-column min-vh-100">
    <header class="site-header">
          <span class="d-flex justify-content-between">
              <a href="index.php">Covid Busters</a>

              <a href="registration.php">Register</a>
          </span>
    </header>

    <main class="main-container">
        <div class="login-container">
            <h1>Log into Your Account</h1>

            <div class="login-main-components">
                <?php if (isset($errors['login-error'])) : ?>
                    <p class="error form-text"><?= $errors['login-error'] ?></p>
                <?php endif; ?>
                <form action="" method="post" novalidate>
                    <div class="form-group">
                        <label for="email" class="required-label">Email</label><br>
                        <input type="text" class="form-control" name="email" id="email"
                               placeholder="Your email address.." value="<?= $_POST['email'] ?? "" ?>">
                    </div>
                    <div class="form-group">
                        <label for="password" class="required-label">Password</label><br>
                        <input type="password" class="form-control" name="password" id="password"
                               placeholder="Your password..">
                    </div>
                    <div class="form-group d-flex justify-content-center">
                        <button type="submit" class="button-submit btn btn-primary">Login</button>
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
