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

    if((strlen($email) >= 7) && (strstr($email, "@")) && (strstr($email, ".")) && $name && $comment){
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $time = date("h:i:s A");
        $date = date("F d,Y");

        $newpost = mysqli_query($conn, "INSERT INTO guestList (name, email, comment, time, date) VALUES 
                    ('$name', '$email', '$comment', '$time', '$date')") or die(mysqli_error($conn));

        $validation = 'GUESTBOOK UPDATED';

    }else{
        $error = 'Invalid input! (Please enter a valid email)';
    }


}

if (isset($_POST['deletebtn'])) {
    //if id array is not empty
    //get all selected id and convert to string
    $idStr = implode(',', $_POST['num']);
    $delete = mysqli_query($conn, "DELETE FROM `guestList` WHERE `id` = ('$idStr') ") or die(mysqli_error($conn)) ;
    //if delete is successful
    if ($delete == true) {
        $statusMsg = 'Selected comment has been deleted successfully!';
    } else if($delete == false) {
        $statusMsg = 'Error occurred, please try again.';
    }
}

if (isset($_POST['editbtn'])) {
        $id = implode(',', $_POST['hid']);
        $comment = strip_tags($_POST['comment']);
        $update = mysqli_query($conn, "UPDATE `guestList` SET `comment` = '$comment' WHERE `id`= ('$id')") or die(mysqli_error($conn));
    if ($update == true) {
        $statusMsg = 'Edit successful!';
    } else {
        $statusMsg = 'Error occurred, please try again.';
    }
}

// Pagination
$page = 1;
//number of results per page
$results_per_page = 5;

$query = mysqli_query($conn, "SELECT * FROM guestList ORDER BY id DESC") or mysqli_error($conn);
$numrows = mysqli_num_rows($query);

//determine total number of pages available
$number_of_pages = ceil($numrows/$results_per_page);

//determine which page the user is currently on
if(!isset($_GET['page'])) {
    $page = 1;
}else{
    $page = $_GET['page'];
}

// pagination adjustment started from here
$pagenum = 1;

if($pagenum < 1 ){
    $pagenum = 1;
} else if($pagenum > $number_of_pages) {
    $pagenum = $number_of_pages;
}

$this_page_first_result = 0;
//determine the sql LIMIT starting number for the result on the displaying page
$this_page_first_result .= ($page-1)*($results_per_page);
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
        'page' => $page, 'number_of_pages' => $number_of_pages,
        'results_per_page' => $results_per_page, 'this_page_first_result' => $this_page_first_result,
        'result' => $result, 'statusMsg' => $statusMsg, 'validation' => $validation, 'error' => $error,
    ));

}

mysqli_close($conn);
?>

