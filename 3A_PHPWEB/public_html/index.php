<?php
require_once('include/config.php');

$action = 'login';
if(isset($_SESSION['data']['user_login'])) 
{
        if($_SESSION['data']['Factor2'] == null || isset($_SESSION['data']['ok']))
        {
                $action = 'show';
        }
        else
        {
                $action = 'login';
        }
}
if(isset($_GET['a']) && !($_GET['a'] == 'validation2' && $action == 'show')) {
        $action = $_GET['a'];
}

switch($action) {
        case 'form-login':
                $stmt = $db->prepare('SELECT COUNT(*) as nb FROM students WHERE login = :login AND pass = SHA2(CONCAT(SHA1(login),:pass), 512)');
                $stmt->bindParam(':login', $_POST['user']);
                $stmt->bindParam(':pass', $_POST['pass']);
                $stmt->execute();
                $row = $stmt->fetch();
                if($row['nb'] != 1) 
                {
                        $action = 'login';
                }
                else
                {
                        $_SESSION['data']['user_login'] = $_POST['user'];

                        //Savoir si le double facteur est actif
                        $askfactor = $db->prepare('SELECT students.Factor2, students.secours, students.role
                                                   FROM students
                                                   WHERE students.login = :factor');
                        $askfactor->bindParam(':factor',$_SESSION['data']['user_login']);
                        $askfactor->execute();
                        $Factor2 = $askfactor -> fetch();
                        $_SESSION['data']['Factor2'] = $Factor2[0];
                        $_SESSION['data']['role'] = $Factor2[2];
                        if($Factor2[1] != null)
                        {
                                $_SESSION['data']['secours'] = $Factor2[1];
                        }
                        if($_SESSION['data']['Factor2'] == null)
                        {
                                header('Location: https://devbox.u-angers.fr/~nicomace2501/');exit;
                        }
                        else
                        {
                                $action = 'validation';
                        }  
                }
                break;
        case 'logout':
                unset($_SESSION['data']);
                header('Location: https://devbox.u-angers.fr/~nicomace2501/');exit;
                break;
        case 'administrateur':
                if(isset($_POST['user2']))
                {
                        if(!empty($_POST['name2']) AND !empty($_POST['firstname2']) AND !empty($_POST['pass2']))
                        {
                                $name2 = htmlspecialchars($_POST['name2']);
                                $firstname2 = htmlspecialchars($_POST['firstname2']);
                                $myuser2 = htmlspecialchars($_POST['user2']);
                                $pass2 = htmlspecialchars($_POST['pass2']);

                                $insertmembre2 = $db -> prepare('INSERT INTO students(firstname, lastname , login, pass, role ) VALUES (?, ?, ?, SHA2(CONCAT(SHA1(?),?),512), ?)');
                                
                                $insertmembre2 -> execute(array($firstname2,$name2,$myuser2,$myuser2,$pass2,$_POST['role2']));
                                $_SESSION['data']['ajout'] = "Membre ajouté !";
                        }
                        else
                        {
                                $_SESSION['data']['ajout'] = "Membre non ajouté...";
                        }
                }
                break;
        case 'administrateur2':
                if(isset($_POST['usersearch']))
                {
                        $usersearch = htmlspecialchars($_POST['usersearch']);
                        $searchmembre = $db -> prepare('SELECT students.id, students.login, students.firstname, students.lastname FROM students WHERE login LIKE "%'.$usersearch.'%" OR lastname LIKE "%'.$usersearch.'%" OR firstname LIKE "%'.$usersearch.'%"');
                        $searchmembre -> execute();
                        $searchresult = $searchmembre -> fetchall();
                        $_SESSION['data']['membres'] = $searchresult;
                        /*echo $_POST['usersearch'];
                        echo $usersearch;
                        var_dump($searchresult);
                        echo ('SELECT login FROM students WHERE login LIKE \"%'.$usersearch.'%\"');*/
                        
                }
                else
                {
                        //echo "test";
                }
                $action = "administrateur";
                break;
        case 'uploadrole':
                if(isset($_POST['changemembre']) AND isset($_POST['role3']) AND !empty($_POST['changemembre']))
                {
                        //echo('UPDATE INTO students SET role = '.$_POST['role3'].' WHERE login = '.$_POST['changemembre']'');
                        $roleajour = $db -> prepare('UPDATE students SET role = :role WHERE login = :login');
                        $roleajour->bindParam(':role', $_POST['role3']);
                        $roleajour->bindParam(':login', $_POST['changemembre']);
                        $roleajour -> execute();
                        $_SESSION['data']['messagerole'] = 'Mise à jour effectuée';
                        $action = 'administrateur';
                }
                else
                {
                        $_SESSION['data']['messagerole'] = 'Erreur...';
                        $action = 'administrateur';
                }
                break;
        case 'show':
                if(isset($_SESSION['data']['user_login']) || ($_SESSION['data']['Factor2'] == null || $_SESSION['data']['ok'] == 1)) 
                {
                        $stmt = $db->prepare('SELECT DISTINCT notes.note, subjects.subjectname
                                                FROM students 
                                                INNER JOIN notes
                                                ON students.id = notes.id_student AND students.login = :login
                                                INNER JOIN subjects 
                                                ON subjects.id = notes.id_subject');
                        $stmt->bindParam(':login', $_SESSION['data']['user_login']);
                        $stmt->execute();
                        $notes = $stmt->fetchall();
                        $_SESSION['data']['notes'] = $notes;
                        $moyenne = 0;
                        $nb = 0;
                        foreach($notes as $note)
                        {
                                $moyenne += $note[0]; 
                                $nb++;
                        }
                        $_SESSION['data']['moyenne'] =  $moyenne/$nb;
                } 
                else 
                {
                        $action = 'login';
                }
                break;
        case 'factor2':
                if(isset($_SESSION['data']['user_login'])) 
                {
                        if($_SESSION['data']['Factor2'] == null)
                        { 
                                if(isset($_POST['usercode']))
                                {
                                        $ga = new PHPGangsta_GoogleAuthenticator();
                                        $usercode = htmlspecialchars($_POST['usercode']);
                                        $_SESSION['data']['QRcode'] = null;
                                        $checkResult = $ga->verifyCode($_SESSION['data']['secret'], $usercode, 1);
                                        $_SESSION['data']['secours'] = md5(uniqid(rand(), true));
                                       

                                        if($checkResult == 1)
                                        {
                                                $_SESSION['data']['error'] = "Félicitations, votre 2FA est actif ! Si vous égarez votre téléphone, vous pourrez vous connecter avec ce code : ".$_SESSION['data']['secours'].". Vous devez le retenir, mais pas le communiquer.";
                                                $_SESSION['data']['Factor2'] = $_SESSION['data']['secret'];
                                                $req = $db->prepare('UPDATE students SET students.Factor2 = :secret, students.secours = :secours where login = :login');
                                                $req -> bindParam(':secret', $_SESSION['data']['secret']);
                                                $req -> bindParam(':login',$_SESSION['data']['user_login']);
                                                $req -> bindParam(':secours',$_SESSION['data']['secours']);
                                                $req->execute();
                                        }
                                        else
                                        {
                                                $_SESSION['data']['error'] = "La manipulation n'a pas fonctionné, le code est faux ou le délai dépassé";
                                        }
                                }
                                elseif($_POST['hidden']==1)
                                {
                                        $_SESSION['data']['error'] = null;
                                        $ga = new PHPGangsta_GoogleAuthenticator();
                                        $secret = $ga->createSecret();
                                        //echo "Secret is: ".$secret."<br>\n";
                                        $qrCodeUrl = $ga->getQRCodeGoogleUrl('GreenRoseSchool', $secret);
                                        $_SESSION['data']['QRcode'] = $qrCodeUrl;
                                        $_SESSION['data']['secret'] = $secret;
                                        //echo "URL for the QR-Code: ".$qrCodeUrl."<br>\n";
                                        //$oneCode = $ga->getCode($secret);
                                        $checkResult = $ga->verifyCode($secret, $oneCode, 1);
                                        //echo $oneCode;
                                } 
                        }
                        else
                        {
                                if($_POST['hiddenx']==1)
                                {
                                        $req2 = $db->prepare('UPDATE students SET students.Factor2 = NULL where login = :login');
                                        $req2 -> bindParam(':login',$_SESSION['data']['user_login']);
                                        $req2->execute();
                                        $_SESSION['data']['Factor2'] = NULL;
                                }
                        }
                }
                else
                {
                        $action = 'login'; 
                        
                }
                break;
        case 'register':
                if(isset($_POST['user']))
                {
                        if(!empty($_POST['name']) AND !empty($_POST['firstname']) AND !empty($_POST['user']) AND !empty($_POST['pass']))
                        {
                                $name = htmlspecialchars($_POST['name']);
                                $firstname = htmlspecialchars($_POST['firstname']);
                                $myuser = htmlspecialchars($_POST['user']);
                                $pass1 = htmlspecialchars($_POST['pass']);

                                $insertmembre = $db -> prepare('INSERT INTO students(firstname, lastname , login, pass ) VALUES (?, ?, ?, SHA2(CONCAT(SHA1(?),?),512))');
                                
                                $insertmembre -> execute(array($firstname,$name,$myuser,$myuser,$pass1));
                        }
                }
                else
                {

                }
                break;
        case 'enseignant':
                if(isset($_POST['usersearch']))
                {
                        $usersearch = htmlspecialchars($_POST['usersearch']);
                        $searchmembre = $db -> prepare('SELECT students.id, students.login, students.firstname, students.lastname FROM students WHERE login LIKE "%'.$usersearch.'%" OR lastname LIKE "%'.$usersearch.'%" OR firstname LIKE "%'.$usersearch.'%"');
                        $searchmembre -> execute();
                        $searchresult = $searchmembre -> fetchall();
                        $_SESSION['data']['membres'] = $searchresult; 
                }
                if(isset($_POST['eleve']) AND isset($_POST['matiere']) AND isset($_POST['Note']))
                {
                        $Note = htmlspecialchars($_POST['Note']);
                        $eleve = htmlspecialchars($_POST['eleve']);
                        $matiere = htmlspecialchars($_POST['matiere']);
                        $ajoutnote = $db -> prepare('INSERT INTO notes(id_student, id_subject, note) VALUES(?, ?, ?)');
                        $ajoutnote -> execute(array($eleve, $matiere, $Note));
                        //echo 'INSERT INTO notes(id_student, id_subject, note) VALUES(?, ?, ?)';
                }
                if(isset($_POST['eleve']))
                {
                        $eleve2 = htmlspecialchars($_POST['eleve']);
                        $stmt2 = $db->prepare('SELECT DISTINCT notes.note, notes.id, subjects.subjectname
                                                FROM students 
                                                INNER JOIN notes
                                                ON students.id = notes.id_student AND students.id = :id
                                                INNER JOIN subjects 
                                                ON subjects.id = notes.id_subject');
                        $stmt2->bindParam(':id', $eleve2);
                        $stmt2->execute();
                        $notes2 = $stmt2->fetchall();
                        $_SESSION['data']['notes2'] = $notes2;
                        //var_dump($_SESSION['data']['notes2']);
                        $moyenne2 = 0;
                        $nb2 = 0;
                        foreach($notes2 as $note2)
                        {
                                $moyenne2 += $note2[0]; 
                                $nb2++;
                        }
                        $_SESSION['data']['moyenne2'] =  $moyenne2/$nb2;
                }
                if(isset($_POST['supprime']))
                {
                        echo "test";
                        $delta = $db -> prepare('DELETE FROM notes WHERE id = :ID');
                        $delta -> bindParam(':ID',$_POST['supprime']);
                        $delta -> execute();
                }
                break;
        case 'validation2':
                $_GET['a'] = null;
                if(isset($_SESSION['data']['user_login']))
                {
                        if(isset($_POST['code6']))
                        {
                                $ga = new PHPGangsta_GoogleAuthenticator();
                                $usercode = htmlspecialchars($_POST['code6']);
                                $checkResult2 = $ga->verifyCode($_SESSION['data']['Factor2'], $usercode, 1); 

                                if($checkResult2 == 1)
                                {
                                        $_SESSION['data']['ok'] = 1;
                                        $action = 'show';
                                        header('Location: https://devbox.u-angers.fr/~nicomace2501?a=show');exit;
                                }
                                else if($_POST['code6'] == $_SESSION['data']['secours'])
                                {
                                        $reset2FA = $db -> prepare('UPDATE students SET students.Factor2 = NULL, students.secours = NULL where login = :login');
                                        $reset2FA -> bindParam(':login',$_SESSION['data']['user_login']);
                                        $reset2FA -> execute();

                                        $_SESSION['data']['Factor2'] = null;
                                        $_SESSION['data']['secours'] = null;

                                        $_SESSION['data']['ok'] = 1;
                                        $action = 'show';
                                        header('Location: https://devbox.u-angers.fr/~nicomace2501?a=show');exit;
                                }
                                else
                                {
                                        $action = 'logout';
                                        header('Location: https://devbox.u-angers.fr/~nicomace2501?a=logout');exit;
                                }
                        }
                }
                else
                {
                        $action = 'login';
                }
                break;
}

$template = $twig->load($action.'.twig');
  echo $template->render(array(
  'data' => $_SESSION['data'],
));

