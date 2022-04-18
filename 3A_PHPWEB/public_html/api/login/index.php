<?php
    require_once('/home/vendor/autoload.php');
    use Lcobucci\JWT\Configuration;
    use Lcobucci\JWT\Signer\Hmac\Sha256;
    use Lcobucci\JWT\Signer\Key\InMemory;
    if(isset($_GET['username']) && isset($_GET['password']))
    {
        echo login($_GET['username'], $_GET['password']);
    }
    else if(isset($_POST['username']) && isset($_POST['password']))
    {
        echo login($_POST['username'], $_POST['password']);
    }
    function login($user, $psswd) 
    {
        $username = htmlspecialchars($user);
        $password = htmlspecialchars($psswd);

        $db = new PDO('mysql:host=localhost;dbname=nicomace2501;charset=utf8mb4', 'nicomace2501', 'PlHdXwmzywIP');

        $stmt = $db->prepare('SELECT * FROM students WHERE login = :login AND pass = SHA2(CONCAT(SHA1(login),:pass), 512)');
        $stmt->bindParam(':login', $username);
        $stmt->bindParam(':pass', $password);
        $stmt->execute();
        $row = $stmt->fetch();
        $rowcount = $stmt -> rowCount();
        if($rowcount === 1) 
        {
            $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('polytech')
            );
            $now = new DateTimeImmutable();
            $token = $configuration->builder()
                ->issuedBy('https://devbox.u-angers.fr')
                ->permittedFor('https://devbox.u-angers.fr')
                ->issuedAt($now)
                ->expiresAt($now->modify('+1 hour'))
                ->withClaim('ulogin', $username)
                ->withClaim('uID',$row[0])
                ->getToken($configuration->signer(), $configuration->signingKey());
            return json_encode(
                ['result' => 1,
                'message' => 'Token generated successfully',
                'token' => '' . $token->toString(),]);
        }
        else 
        {
            return json_encode(
            ['result' => 0,
            'message' => 'Invalid username and/or password']);
        }
    }
    