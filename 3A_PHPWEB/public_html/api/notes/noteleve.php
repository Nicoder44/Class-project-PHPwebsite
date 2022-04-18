<?php
    require_once('/home/vendor/autoload.php');
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    include_once("../objects/notes.php");

    use Lcobucci\Clock\SystemClock;
    use Lcobucci\JWT\Configuration;
    use Lcobucci\JWT\Signer\Hmac\Sha256;
    use Lcobucci\JWT\Signer\Key\InMemory;
    use Lcobucci\JWT\Validation\Constraint;
    
    $rawbearer = $_SERVER['HTTP_AUTHORIZATION'];
    $token = substr($rawbearer, 8, strlen($rawbearer)-9);

    //echo $token;
    $configuration = Configuration::forSymmetricSigner(
        new Sha256(),
        InMemory::plainText('polytech')
    );
    $configuration->setValidationConstraints(
    new Constraint\LooseValidAt(
    new SystemClock(new DateTimeZone('UTC')),
    new DateInterval('PT30S')
    ),
    new Constraint\SignedWith($configuration->signer(), $configuration->signingKey()),
    );
    $t = $configuration->parser()->parse($token);
    $constraints = $configuration->validationConstraints();
    
    if ($configuration->validator()->validate($t, ...$constraints)) 
    {
        $db = new PDO('mysql:host=localhost;dbname=nicomace2501;charset=utf8mb4', 'nicomace2501', 'PlHdXwmzywIP');
        $notes = new Notes($db);
        $stmt = $notes->readnoteleve($t->claims()->get('uID'));
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
    }}
    else
    {
        echo "Token is not valid.";
    }