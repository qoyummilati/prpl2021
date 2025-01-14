<?php

class Auth
{
    private $db;
    private $error;

    function __construct($db_conn)
    {

        $this->db = $db_conn;

        session_start();
    }

    public function register($nama, $email, $password)
    {
        try {

            $hashPasswd = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("INSERT INTO tbauth(nama, email, password) VALUES(:nama, :email, :pass)");

            $stmt->bindParam(":nama", $nama);

            $stmt->bindParam(":email", $email);

            $stmt->bindParam(":pass", $hashPasswd);

            $stmt->execute();

            return true;
        } catch (PDOException $e) {

            if ($e->errorInfo[0] == 23000) {

                $this->error = "Email sudah digunakan!";

                return false;
            } else {

                echo $e->getMessage();

                return false;
            }
        }
    }


    public function login($email, $password)

    {

        try {

            $stmt = $this->db->prepare("SELECT * FROM tbauth WHERE email = :email");

            $stmt->bindParam(":email", $email);

            $stmt->execute();

            $data = $stmt->fetch();


            if ($stmt->rowCount() > 0) {

                if (password_verify($password, $data['password'])) {

                    $_SESSION['user_session'] = $data['id'];

                    return true;
                } else {

                    $this->error = "Email atau Password Salah";

                    return false;
                }
            } else {

                $this->error = "Email atau Password Salah";

                return false;
            }
        } catch (PDOException $e) {

            echo $e->getMessage();

            return false;
        }
    }

    public function isLoggedIn()
    {


        if (isset($_SESSION['user_session'])) {

            return true;
        }
    }

    public function getUser()
    {

        if (!$this->isLoggedIn()) {

            return false;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM tbauth WHERE id = :id");

            $stmt->bindParam(":id", $_SESSION['user_session']);

            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {

            echo $e->getMessage();

            return false;
        }
    }

    public function logout()
    {
        session_destroy();

        unset($_SESSION['user_session']);

        return true;
    }

    public function getLastError()
    {

        return $this->error;
    }
    public function sender($email)
    {
        $to      = $email; // Send email to our user
        $subject = 'Signup | Verification'; // Give the email a subject 
        $message = '
        
        Hi.$email

        Your account is successfully created

        Qoyum Milati Tri Rejeki';

        mail($to, $subject, $message); // Send our email
    }
}
