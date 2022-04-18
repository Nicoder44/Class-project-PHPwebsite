<?php
    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    include_once '../objects/student.php';
    $db = new PDO('mysql:host=localhost;dbname=nicomace2501;charset=utf8mb4', 'nicomace2501', 'PlHdXwmzywIP');
    $student = new Student($db);
    $stmt = $student->read();
    $num = $stmt->rowCount();
    if($num > 0) 
    {
        $students_arr=array();
        $students_arr["records"]=array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            extract($row);
            $student_item=array(
                "id" => $id,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "login" => $login,
                "pass" => $pass,
            );
            array_push($students_arr["records"], $student_item);
        }
        echo json_encode($students_arr);
    } 
    else 
    {
        echo json_encode(array("message" => "No students found."));
    }