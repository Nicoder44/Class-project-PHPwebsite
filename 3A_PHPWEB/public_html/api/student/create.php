<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include_once '../objects/student.php';
$db = new PDO('mysql:host=localhost;dbname=nicomace2501;charset=utf8mb4', 'nicomace2501', 'PlHdXwmzywIP');
$student = new Student($db);
// get posted data
$data = json_decode(file_get_contents("php://input"));
$student->firstname = $data->firstname;
$student->lastname = $data->lastname;
$student->login = $data->login;
$student->pass = $data->pass;
if($student->create()){
echo '{';
echo '"message": "Student was created."';
echo '}';
} else {
echo '{';
echo '"message": "Unable to create student."';
echo '}';
}
