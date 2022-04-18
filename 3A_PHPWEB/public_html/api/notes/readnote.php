<?php
    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    include_once '../objects/notes.php';
    $db = new PDO('mysql:host=localhost;dbname=nicomace2501;charset=utf8mb4', 'nicomace2501', 'PlHdXwmzywIP');
    $notes = new Notes($db);
    $stmt = $notes->readnote();
    $num = $stmt->rowCount();
    if($num > 0) 
    {
        $notes_arr=array();
        $notes_arr["records"]=array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            extract($row);
            $notes_item=array(
                "id" => $id,
                "id_student" => $id_student,
                "id_subject" => $id_subject,
                "note" => $note
            );
            array_push($notes_arr["records"], $notes_item);
        }
        echo json_encode($notes_arr);
    } 
    else 
    {
        echo json_encode(array("message" => "No notes found."));
    }

    