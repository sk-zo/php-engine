<?php
$code = '<?php $name = "테스트"; echo $name; ?>';
$tokens = token_get_all($code);

foreach ($tokens as $token) {
    if (is_array($token)) {
        echo "타입: " . token_name($token[0]) . " 값: " . $token[1] . "<br>";
    } else {
        echo "심볼: " . $token . "<br>";
    }
}
?>