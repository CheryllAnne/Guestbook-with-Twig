<?php
/**
 * Created by PhpStorm.
 * User: CHERYLLANNE
 * Date: 3/29/2019
 * Time: 10:18 AM
 */
namespace utility;

use Twig\Cache\NullCache;

class Media
{
    private $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    protected function connect(){
        $conn = mysqli_connect("localhost:3306", "root", "root", "guestbook") or die(mysqli_error($conn));
        return $conn;
    }

    public function upload($file_type){
        $name = strip_tags($_POST['name']); //striptags to avoid cross site scripting
        $email = strip_tags($_POST['email']);
        $comment = strip_tags($_POST['comment']);
        $image = $_FILES["file"]["name"];
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $time = date("h:i:s A");
        $date = date("F d,Y");
        $conn = $this->connect();
        $newpost = mysqli_query($conn, "INSERT INTO guestList (name, email, comment, image, type, time, date) VALUES 
                    ('$name', '$email', '$comment', '$image', '$file_type', '$time', '$date')") or die(mysqli_error($conn));
        return $newpost;
    }

    public function upload_media()
    {
        $image_ext = array("jpg", "jpeg", "gif", "png");
        $video_ext = array("mp4", "wma");
        $audio_ext = "mp3";
        $target = "uploads/" . basename($_FILES["file"]["name"]);
        $imageFileType = strtolower(pathinfo($target, PATHINFO_EXTENSION));

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target)) {
            if (in_array($imageFileType, $image_ext)) {
                $file_type = "image";
                $this->upload($file_type);
                $this->session->flash('success', 'POST SUCCESSFUL!');
                $this->session->flash('uploaded', 'The file ' . basename($_FILES["file"]["name"]) . ' has been successfully uploaded.');

            } elseif (in_array($imageFileType, $video_ext)) {
                $file_type = "video";
                $this->upload($file_type);
                $this->session->flash('success', 'POST SUCCESSFUL!');
                $this->session->flash('uploaded', 'The file ' . basename($_FILES["file"]["name"]) . ' has been successfully uploaded.');

            } elseif (in_array($imageFileType, $audio_ext)) {
                $file_type = "audio";
                $this->upload($file_type);
                $this->session->flash('success', 'POST SUCCESSFUL!');
                $this->session->flash('uploaded', 'The file ' . basename($_FILES["file"]["name"]) . ' has been successfully uploaded.');

            }

        }else {

           $file_type = NULL;
           $this->upload($file_type);
            $this->session->flash('success', 'POST SUCCESSFUL!');

        }
    }

}