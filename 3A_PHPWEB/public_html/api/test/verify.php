<?php
require_once('/home/vendor/autoload.php');
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
    echo "Token is valid, user is ".$t->claims()->get('ulogin');
} 
else 
{
    echo "Token is NOT valid.";
}  