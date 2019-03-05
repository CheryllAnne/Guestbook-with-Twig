<?php
error_reporting(E_ALL ^ E_NOTICE);
require "vendor/autoload.php";
//Twig_Autoload::register();
$loader = new Twig_Loader_Filesystem('views');
$twig = new Twig_Environment($loader);

//connect to db
$conn = mysqli_connect("localhost:3306", "root", "root") or die(mysqli_error($conn));
mysqli_select_db($conn, "guestbook") or die(mysqli_error($conn));


//check if Update btn has been pressed
// updatebtn was an unset value - runtime error
if (isset($_POST['postbtn'])) { //isset= test for the existence of a variable or array element without actually trying to access it
    $name = strip_tags($_POST['name']); //striptags to avoid cross site scripting
    $email = strip_tags($_POST['email']);
    $comment = strip_tags($_POST['comment']);

    if ($name && $email && $comment) {
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $time = date("h:i:s A");
        $date = date("F d,Y");
        //$idString = implode(',', $_POST['comment']);
        //add to the db ( guestbook )
        mysqli_query($conn, "INSERT INTO guestList (name, email, comment, time, date) VALUES 
                    ('$name', '$email', '$comment', '$time', '$date')") or die(mysqli_error($conn));
        echo "<h1> Guest Book Updated!</h1>";
    } else
        echo "<h1>You have not completed the required information!</h1>";
}

if (isset($_POST['deletebtn'])) {
    //if id array is not empty
    //get all selected id and convert to string
    $idStr = implode(',', $_POST['num']);
    //$id = ($_POST['id']);
    //delete records from database
    $delete = mysqli_query($conn, "DELETE FROM `guestList` WHERE `id` = ('$idStr') ") or die(mysqli_error($conn)) ;
    //if delete is successful
    if ($delete == true) {
        $statusMsg = 'Selected comments have been deleted successfully!';
    } else {
        $statusMsg = 'Error occurred, please try again.';
    }
}

if (isset($_POST['editbtn'])) {
    //foreach ($_POST['hid'] AS $id) {
        $id = implode(',', $_POST['hid']);
        $comment = strip_tags($_POST['comment']);
        $update = mysqli_query($conn, "UPDATE `guestList` SET `comment` = '$comment' WHERE `id`= ('$id')") or die(mysqli_error($conn));
    //}
    if ($update == true) {
        $statusMsg = 'Edit successful!';
    } else {
        $statusMsg = 'Error occurred, please try again.';
    }
}
//echo "<h4>$statusMsg</h4>";

$query = mysqli_query($conn, "SELECT * FROM guestList ORDER BY id DESC") or mysqli_error($conn);
$numrows = mysqli_num_rows($query);
if ($numrows > 0) {

    $comments = array();
    while ($row = mysqli_fetch_assoc($query)) {
        $id = $row['id'];
        $name = $row['name'];
        $email = $row['email'];
        $comment = $row['comment'];
        $time = $row['time'];
        $date = $row['date'];
        $comment = nl2br($comment);


        $comments[] = array(
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'comment' => $comment,
            'time' => $time,
            'date' => $date,

        );

    }

    echo $twig->render('index.html.twig', array(
        'id' => $id,
        'name' => $name, 'time' => $time,
        'date' => $date, 'email' => $email,
        'comments' => $comments, 'numrows' => $numrows,

    ));
} else {
    echo "<h1>NO POSTS</h1>";
}

mysqli_close($conn);
?>

