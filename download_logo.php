<?php
$context = stream_context_create([
    "http" => [
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
    ]
]);
$data = file_get_contents("https://upload.wikimedia.org/wikipedia/id/8/8c/Logo_Politeknik_Negeri_Bengkalis.png", false, $context);
if ($data !== false) {
    file_put_contents("public/logo-polbeng.png", $data);
    echo "Success";
} else {
    echo "Failed";
}
?>
