<?php
/**
 * Created by PhpStorm.
 * User: CHERYLLANNE
 * Date: 3/13/2019
 * Time: 2:44 PM
 */
use utility\Session;
error_reporting(E_ALL ^ E_NOTICE);
require "vendor/autoload.php";
//Twig_Autoload::register();
$loader = new Twig_Loader_Filesystem('views');
$twig = new Twig_Environment($loader);

include_once('Session.php');

$session = new Session();

//initialize variable


//connect to db
$conn = mysqli_connect("localhost:3306", "root", "root") or die(mysqli_error($conn));
mysqli_select_db($conn, "guestbook") or die(mysqli_error($conn));

if (isset($_POST['registerbtn'])) { //isset= test for the existence of a variable or array element without actually trying to access it
    $username = strip_tags($_POST['username']);
    $email = strip_tags($_POST['email']);
    $password1 = strip_tags($_POST['password1']);
    $password2 = strip_tags($_POST['password2']);

//form validation

    if(empty($username)){
        $session->flash('name_error', "Username is required!");
    }
    if(empty($email)){
        $session->flash('email_error', "Email is required!");

    }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email = false;
        $session->flash('v_error', "Email invalid!");
    }
    if(empty($password1)){
        $session->flash('p1_error', "Password is required!");
    }
    if($password1 != $password2){
        $session->flash('p2_error', "Passwords does not match!");
    }

    /*if(isset($_POST['username']) && $_POST['username'] != "") {
        $username = $_POST['username'];
    }
    if(isset($_POST['email']) && $_POST['email'] != "") {
        $email = $_POST['email'];
    }
    if(isset($_POST['password1']) && $_POST['password1'] != "") {
        $password1= $_POST['password1'];
    }
    if($password1 != $password2){
        $errors = "Passwords does not match!";
    }*/

//check db for existing users with same username
    else {
        $results = mysqli_query($conn, "SELECT * FROM `user` WHERE username = '$username'  or email = '$email' LIMIT 1") or die(mysqli_error($conn));
        $_user = mysqli_fetch_assoc($results);

        if ($_user) {
            if ($_user['username'] === $username) {
                $session->flash('un_error', "Username already exists!");

            }
            if ($_user['email'] === $email) {
                $session->flash('mail_error', "This email address already has a registered user!");

            }

        }elseif($email != false){
        //if(count($errors) == 0){

            $password =password_hash($password1, PASSWORD_DEFAULT); //this will encrypt the password
            $query = mysqli_query($conn, "INSERT INTO `user` (username, email, password) VALUES ('$username', '$email', '$password')");

            //$_SESSION['username'] = $username;
            //$_SESSION['success'] = "You are now logged in!";
            $session->flash('success', 'Successfully Registered!');
            header('location: index.php');
            exit;
        }
    }
//register user if no error

}


echo $twig->render('index.twig', array(
    'errors' => $errors, 'username' => $username, 'email' => $email, 'password1' => $password1,
    'password2' => $password2, 'password' => $password, 'session'=> $session,
    'name_error' => $session->get('name_error'),
    'email_error' => $session->get('email_error'),'v_error' => $session->get('v_error'),
    'p1_error' => $session->get('p1_error'),'p2_error' => $session->get('p2_error'),
    'un_error' => $session->get('un_error'), 'mail_error' => $session->get('mail_error'),
    'success' => $session->get('success'),
));



mysqli_close($conn);
?>
