<html><head></head><body>
<?php
    require_once('/home/vendor/autoload.php');
    use Lcobucci\Clock\SystemClock;
    use Lcobucci\JWT\Configuration;
    use Lcobucci\JWT\Signer\Hmac\Sha256;
    use Lcobucci\JWT\Signer\Key\InMemory;
    use Lcobucci\JWT\Validation\Constraint;

    if(isset($_POST['username'])) 
    {
        $opts = array(
            'http'=>array(
            'method'=>'POST',
            'header'=>'Content-type: application/x-www-form-urlencoded',
            )
            );
            $opts['http']['content'] = json_encode(array(
            'firstname' => $_POST['firstname'],
            'lastname' => $_POST['lastname'],
            'login'=> $_POST['username'],
            'pass'=> $_POST['password']
            ));
            $context = stream_context_create($opts);
            $st = file_get_contents('https://devbox.u-angers.fr/~nicomace2501/api/student/create.php', false, $context);            
    } 
    else 
    {
?>
        <form method="post">
            <input required type="text" name="username" placeholder = "username"><br/>
            <input required type="password" name="password" placeholder = "password"><br/>
            <input required type="text" name="firstname" placeholder = "firstname"><br/>
            <input required type="text" name="lastname" placeholder = "lastname"><br/>
            <input type="submit" value="Se connecter"><br/>
        </form>       
<?php
     } 
     function httpPost($url, $data = [], $headers = [])
     {
        $options = [
        'http' => [
        'method' => 'POST',
        'header' => array_merge(['Content-type: application/x-www-form-urlencoded'], $headers),
        'content' => http_build_query($data),
        ],
        ];
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }
        
?>
</body></html>