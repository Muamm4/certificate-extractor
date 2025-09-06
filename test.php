<?php
$pfxFile = "C:/Users/muril/Downloads/certificado.pfx";
$pemFile = "C:/Users/muril/Downloads/certificado.pem";
$password = "1234";

$cmd = "openssl pkcs12 -in \"$pfxFile\" -out \"$pemFile\" -nodes -passin pass:$password";

exec($cmd, $output, $returnVar);

if ($returnVar !== 0) {
    echo "❌ Erro ao converter:\n";
    echo implode("\n", $output);
} else {
    echo "✅ .pem gerado com sucesso em $pemFile\n";
}
