<?php
namespace App\Helpers;
use Illuminate\Support\Str;

class JWT
{
    const _KEY = 'nguyenpv^&phamvan@2022^))(((^@hl#$.CoDecDotVN:';
    const _ALGO = 'HS256';


    public static function decode($jwt, $key = self::_KEY, $verify = true){
        try {
            $rs = self::__decode($jwt, $key, $verify);
            return [
                'status'    =>  1,
                'message'   =>  'Success',
                'data'      =>  $rs,
            ];
        }catch (\Exception $ex){
            return [
                'status'    =>  -1,
                'message'   =>  $ex->getMessage(),
                'data'      =>  []
            ];
        }
    }


    public static function encode($payload, $key=self::_KEY, $algo = self::_ALGO)
    {
        $header = array('typ' => 'JWT', 'alg' => $algo);
        $segments = array();
        $segments[] = self::__urlsafeB64Encode(self::__jsonEncode($header));
        $segments[] = self::__urlsafeB64Encode(self::__jsonEncode($payload));
        $signing_input = implode('.', $segments);
        $signature = self::__sign($signing_input, $key, $algo);
        $segments[] = self::__urlsafeB64Encode($signature);
        return implode('.', $segments);
    }


    public static function createAccessTokenId()
    {
        return self::genUuid().".".self::genRandomString();
    }

    public static function checkAccessToken($token){
        if (!empty($token)) {
            $access_token = self::__bearerToken($token);
            if($access_token){
                try{
                    $data = JWT::decode($access_token, self::_KEY);
                    if(isset($data['status']) && $data['status']==1){
                        $data = $data['data']??[];
                        return $data;
                    }else{
                        return false;
                    }
                }catch (\Exception $ex){
                    return false;
                }
            }
        }
        return false;
    }

    private static function __bearerToken($authorization_token){
        $header = $authorization_token;
        $p1 = strrpos($header, 'Bearer ');
        $p2 = strrpos($header, 'bearer ');
        if($p1!==false){
            $position = $p1;
        }else if($p2!=false){
            $position = $p2;
        }else{
            $position = false;
        }

        if ($position !== false) {
            $header = substr($header, $position + 7);
            return strpos($header, ',') !== false ? strstr($header, ',', true) : $header;
        }else{
            return $header;
        }
    }


    private static function genRandomString($length=17){
        return Str::lower(Str::random($length));
    }

    private static function genUuid(){
        $uuid = (string) Str::uuid();
        $uuid = Str::replace("-","", $uuid);
        return $uuid;
    }

    private static function __decode($jwt, $key = self::_KEY, $verify = true)
    {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new \UnexpectedValueException('Wrong number of segments');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = self::__jsonDecode(self::__urlsafeB64Decode($headb64)))) {
            throw new \UnexpectedValueException('Invalid segment encoding');
        }
        if (null === $payload = self::__jsonDecode(self::__urlsafeB64Decode($bodyb64))) {
            throw new \UnexpectedValueException('Invalid segment encoding');
        }
        $sig = self::__urlsafeB64Decode($cryptob64);
        if ($verify) {
            if (empty($header->alg)) {
                throw new \DomainException('Empty algorithm');
            }
            if ($sig != self::__sign("$headb64.$bodyb64", $key, $header->alg)) {
                throw new \UnexpectedValueException('Signature verification failed');
            }
        }
        return $payload;
    }

    private static function __sign($msg, $key, $method = self::_ALGO)
    {
        $methods = array(
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        );
        if (empty($methods[$method])) {
            throw new \DomainException('Algorithm not supported');
        }
        return hash_hmac($methods[$method], $msg, $key, true);
    }

    private static function __jsonDecode($input)
    {
        $obj = json_decode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            self::__handleJsonError($errno);
        } else if ($obj === null && $input !== 'null') {
            throw new \DomainException('Null result with non-null input');
        }
        return $obj;
    }

    private static function __jsonEncode($input)
    {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            JWT::__handleJsonError($errno);
        } else if ($json === 'null' && $input !== null) {
            throw new \DomainException('Null result with non-null input');
        }
        return $json;
    }

    private static function __urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        $string = strtr($input, '-_', '+/');
        return base64_decode($string);
    }

    private static function __urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    private static function __handleJsonError($errno)
    {
        $messages = array(
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
        );
        throw new \DomainException(
            isset($messages[$errno])
                ? $messages[$errno]
                : 'Unknown JSON error: ' . $errno
        );
    }
}

