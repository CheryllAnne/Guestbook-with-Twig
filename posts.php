<?php

use utility\Session;
error_reporting(E_ALL ^ E_NOTICE);
require "vendor/autoload.php";
//Twig_Autoload::register();

include_once('Session.php');

$session = new Session();

if ($session->check('logged_id')) {
    header("location: index.php");
}

$loader = new Twig_Loader_Filesystem('views');
$twig = new Twig_Environment($loader);


//connect to db
$conn = mysqli_connect("localhost:3306", "root", "root") or die(mysqli_error($conn));
mysqli_select_db($conn, "guestbook") or die(mysqli_error($conn));

//check if Update btn has been pressed
// updatebtn was an unset value - runtime error
if (isset($_POST['postbtn'])) { //isset= test for the existence of a variable or array element without actually trying to access it
    $image_ext = array("jpg", "jpeg", "gif", "png");
    $video_ext = array("mp4", "wma");
    $audio_ext = "mp3";
    $target = "uploads/" . basename($_FILES["file"]["name"]);
    $imageFileType = strtolower(pathinfo($target, PATHINFO_EXTENSION));

    $name = strip_tags($_POST['name']); //striptags to avoid cross site scripting
    $email = strip_tags($_POST['email']);
    $comment = strip_tags($_POST['comment']);
    $image = $_FILES["file"]["name"];

    if (empty($name)) {
        $session->flash('error', "Please fill in your name!");
    }
    if (empty($email)) {
        $session->flash('error', "Please fill in your email!");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email = false;
        $session->flash('error', "Invalid Email!");
    }
    if (empty($comment)) {
        $session->flash('error', "Please do leave a comment.");;
    }

    if ($name && $email && $comment) {
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $time = date("h:i:s A");
        $date = date("F d,Y");

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target)) {
            if (in_array($imageFileType, $image_ext)) {
                $file_type = "image";
                $newpost = mysqli_query($conn, "INSERT INTO guestList (name, email, comment, image, type, time, date) VALUES 
                    ('$name', '$email', '$comment', '$image', '$file_type', '$time', '$date')") or die(mysqli_error($conn));
                $session->flash('uploaded', 'The file ' . basename($_FILES["file"]["name"]) . ' has been uploaded.');

            } elseif (in_array($imageFileType, $video_ext)) {
                $file_type = "video";
                $newpost = mysqli_query($conn, "INSERT INTO guestList (name, email, comment, image, type, time, date) VALUES 
                    ('$name', '$email', '$comment', '$image', '$file_type', '$time', '$date')") or die(mysqli_error($conn));
                $session->flash('uploaded', 'The file ' . basename($_FILES["file"]["name"]) . ' has been uploaded.');

            } elseif (in_array($imageFileType, $audio_ext)) {
                $file_type = "audio";
                $newpost = mysqli_query($conn, "INSERT INTO guestList (name, email, comment, image, type, time, date) VALUES 
                    ('$name', '$email', '$comment', '$image', '$file_type', '$time', '$date')") or die(mysqli_error($conn));
                $session->flash('uploaded', 'The file ' . basename($_FILES["file"]["name"]) . ' has been uploaded.');
            }

        } elseif (!$file_type && $email != false) {
            $newpost = mysqli_query($conn, "INSERT INTO guestList (name, email, comment, image, type, time, date) VALUES 
                    ('$name', '$email', '$comment', '$image', '$file_type', '$time', '$date')") or die(mysqli_error($conn));

        }

        $session->flash('success', 'Successfully updated!');
        header("location: posts.php");
        exit;
    }
}

if (isset($_POST['deletebtn'])) {
    //if id array is not empty
    //get all selected id and convert to string
    $idStr = implode(',', $_POST['num']);
    $delete = mysqli_query($conn, "DELETE FROM `guestList` WHERE `id` = ('$idStr') ") or die(mysqli_error($conn));
    //if delete is successful
    $session->flash('delete', 'Successfully deleted!');
    header("location: posts.php");
    exit;
}

if (isset($_POST['editbtn'])) {
    $id = implode(',', $_POST['hid']);
    $comment = strip_tags($_POST['comment']);
    $update = mysqli_query($conn, "UPDATE `guestList` SET `comment` = '$comment' WHERE `id`= ('$id')") or die(mysqli_error($conn));

    $session->flash('edit', 'Edit successful!');
    header("location: posts.php");
    exit;
}

//............................................................PAGINATION...................................................................
$page = 1;
//number of results per page
$results_per_page = 5;

$query = mysqli_query($conn, "SELECT * FROM guestList ORDER BY id DESC") or mysqli_error($conn);
$numrows = mysqli_num_rows($query);

//determine total number of pages available
$number_of_pages = ceil($numrows / $results_per_page);

//determine which page the user is currently on
if (!isset($_GET['page'])) {
    $page = 1;
} else {
    $page = $_GET['page'];
}

// pagination adjustment started from here
$pagenum = 1;

if ($pagenum < 1) {
    $pagenum = 1;
} else if ($pagenum > $number_of_pages) {
    $pagenum = $number_of_pages;
}

$this_page_first_result = 0;
//determine the sql LIMIT starting number for the result on the displaying page
$this_page_first_result .= ($page - 1) * ($results_per_page);
// retrieve selected results from database and display them on page
$query_pag = ("SELECT * FROM guestList ORDER BY id DESC LIMIT " . $this_page_first_result . ',' . $results_per_page);
$result = mysqli_query($conn, $query_pag);


if ($numrows > 0) {
    $comments = array();
    while ($row = mysqli_fetch_array($result)) {
        $id = $row['id'];
        $name = $row['name'];
        $email = $row['email'];
        $comment = $row['comment'];
        $time = $row['time'];
        $date = $row['date'];
        $image = $row['image'];
        $comment = nl2br($comment);
        $image_ext = $row['image_ext'];
        $video_ext = $row['video_ext'];
        $audio_ext = $row['audio_ext'];
        $file_type = $row['type'];

        $comments[] = array(
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'comment' => $comment,
            'time' => $time,
            'date' => $date,
            'image' => $image,
            'type' => $file_type,
        );
    }

    echo $twig->render('posts.twig', array(

        'id' => $id, 'name' => $name, 'time' => $time, 'date' => $date, 'email' => $email,
        'comments' => $comments, 'numrows' => $numrows, 'page' => $page, 'number_of_pages' => $number_of_pages,
        'results_per_page' => $results_per_page, 'this_page_first_result' => $this_page_first_result,
        'result' => $result, 'image' => $image, 'image_ext' => $image_ext, 'video_ext' => $video_ext,
        'audio_ext' => $audio_ext, 'type' => $file_type,

        'username' => $_SESSION['username'], 'session' => $_SESSION, 'success' => $session->get('success'),
        'delete' => $session->get('delete'), 'edit' => $session->get('edit'), 'error' => $session->get('error'),
        'uploaded' => $session->get('uploaded')

    ));


}


mysqli_close($conn);


?>

