<?php

class Auth
{
    private $user_storage;
    private $user = NULL;

    public function __construct(IStorage $user_storage)
    {
        $this->user_storage = $user_storage;

        if (isset($_SESSION["user"])) {
            $this->user = $_SESSION["user"];
        }
    }

    public function register($data)
    {
        $user = [
            'fullname' => $data['fullname'],
            'taj' => (int)$data['taj'],
            'address' => $data['address'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            "role" => "User",
            "vaccination-id" => ""
        ];

        return $this->user_storage->add($user);
    }

    public function updateVaccinationId($id = "")
    {
        $this->user['vaccination-id'] = $id;
        $this->user_storage->update($this->user['id'], $this->user);
        $_SESSION["user"] = $this->user;
    }

    public function hasVaccinationID()
    {
        return !is_null($this->user['vaccination-id']) && $this->user['vaccination-id'] !== "";
    }

    public function user_exists($taj, $email)
    {
        $usersByTaj = $this->user_storage->findOne(['taj' => $taj]);

        $usersByEmail = $this->user_storage->findOne(['email' => $email]);
        return !is_null($usersByTaj) || !is_null($usersByEmail);
    }

    public function authenticate($email, $password)
    {
        $users = $this->user_storage->findMany(function ($user) use ($email, $password) {
            return $user["email"] === $email &&
                password_verify($password, $user["password"]);
        });

        return count($users) === 1 ? array_shift($users) : NULL;
    }

    public function is_authorized($role = "")
    {
        if (!$this->is_authenticated()) {
            return FALSE;
        }

        if ($role == $this->user["role"]) {
            return TRUE;
        }

        return FALSE;
    }

    public function is_authenticated()
    {
        return !is_null($this->user);
    }

    public function login($user)
    {
        $this->user = $user;
        $_SESSION["user"] = $user;
    }

    public function logout()
    {
        $this->user = NULL;
        unset($_SESSION["user"]);
    }

    public function authenticated_user()
    {
        return $this->user;
    }
}
