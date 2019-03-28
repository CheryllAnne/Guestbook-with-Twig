<?php
/**
 * Created by PhpStorm.
 * User: CHERYLLANNE
 * Date: 3/13/2019
 * Time: 2:43 PM
 */
error_reporting(E_ALL ^ E_NOTICE);
require "vendor/autoload.php";
//Twig_Autoload::register();
$loader = new Twig_Loader_Filesystem('views');
$twig = new Twig_Environment($loader);

session_start();


$db = mysqli_connect("localhost:3306", "root", "root") or die(mysqli_error($db));
mysqli_select_db($db, "guestbook") or die(mysqli_error($db));


if(isset($_POST['loginbtn'])) {
    $username = strip_tags($_POST['username']);
    $password1 = strip_tags($_POST['password1']);

    if(empty($username)){
        $errors = "Username is required!";
    }
    if(empty($password1)){
        $errors = "Password is required!";
    }

    else{
        //$password =password_hash($password1, PASSWORD_DEFAULT);

        //$_password = md5($password1); //this will encrypt the password
        $query = "SELECT * FROM `user` WHERE username = '$username' "; //take out password from here
        $result =  mysqli_query($db, $query) or die(mysqli_error($db));

        //$num = mysqli_num_rows($result);

        /*if($num < 1){
            header("location: login.php");
        }else{
            if($row = mysqli_fetch_assoc($result)){
                //de-hashing the password
                $passwordCheck = password_verify($password1, $row['password']);
                if($passwordCheck == false){
                    $errors = "Wrong password!";
                    header("location: login.php");
                } elseif($passwordCheck == true){
                    //login the user
                    header("location: posts.php");
                }
            }

        }*/

        if(mysqli_num_rows($result) > 0)
        {
            while($row=mysqli_fetch_array($result))
            {
                if(password_verify($password1, $row['password']))
                {
                    $_SESSION["username"]= $username;
                    $_SESSION["logged_in"] = true;
                    header("location: posts.php");
                }else
                {
                    $errors = "Wrong password!";
                    header("location: login.php");
                }
            }
        }
    }
}

echo $twig->render('login.twig', array(
    'errors' => $errors, 'username' => $username,
    'password1' => $password1,

));



mysqli_close($db);
?>