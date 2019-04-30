<?php
error_reporting(E_ALL ^ E_NOTICE);
require "vendor/autoload.php";

use utility\Session;
use utility\Pagination;
use utility\Media;

include_once('Session.php');
include_once ('Pagination.php');
include_once ('Media.php');

$session = new Session();
$pagination = new Pagination();
$media = new Media($session);

if ($session->check('logged_id') == false) {
    header("location: index.php");
    exit;
}

$loader = new Twig_Loader_Filesystem('views');
$twig = new Twig_Environment($loader);

//connect to db
$conn = mysqli_connect("localhost:3306", "root", "root", "guestbook") or die(mysqli_error($conn));

//check if Update btn has been pressed
// updatebtn was an unset value - runtime error
if (isset($_POST['postbtn'])) { //isset= test for the existence of a variable or array element without actually trying to access it
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
        $media->upload_media();
        header("location: posts.php");
        exit;
    }
}

if (isset($_POST['deletebtn'])) {
    //if id array is not empty get all selected id and convert to string
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
    //if edit successful
    $session->flash('edit', 'Edit successful!');
    header("location: posts.php");
    exit;
}

//............................................................PAGINATION...................................................................
$numrows = $pagination->available_pages();
$number_of_pages = $pagination->total_number();
$page = $pagination->active_page();
$pagenum = $pagination->adjuster();
$this_page_first_result = $pagination->limit();
$result = $pagination->display();


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
