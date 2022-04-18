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
        $url = 'https://devbox.u-angers.fr/~nicomace2501/api/login/';
        
        $params = array(
            "username" => $_POST['username'],
            "password" => $_POST['password'],
        );
        
        $response = json_decode(httpPost($url, $params));
        if($response->result === 1) 
        {
            $token = $response -> token;
            $url = 'https://devbox.u-angers.fr/~nicomace2501/api/notes/noteleve.php';
            echo httpPost($url, [], ["Authorization: Bearer <{$token}>"]);
        }
        else
        {
            echo 'Bad authentication';
        } 
    } 
    else 
    {
?>
        <form method="post">
            <input required type="text" name="username"><br/>
            <input required type="password" name="password"><br/>
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