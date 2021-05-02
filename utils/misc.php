<?php
function redirect($page)
{
    header("Location: ${page}");
    exit();
}

function redirectToFrontPage()
{
    redirect("index.php");
}

function verify_post(...$inputs)
{
    foreach ($inputs as $input) {
        if (!isset($_POST[$input]) || $_POST[$input] == '' || !preg_match('/.*[^ ].*/', $_POST[$input])) {
            return FALSE;
        }
    }

    return TRUE;
}

function verify_get(...$inputs)
{
    foreach ($inputs as $input) {
        if (!isset($_GET[$input]) || $_GET[$input] == '' || !preg_match('/.*[^ ].*/', $_GET[$input])) {
            return FALSE;
        }
    }

    return TRUE;
}
