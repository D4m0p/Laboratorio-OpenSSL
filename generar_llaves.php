<?php
require __DIR__ . '/comun.php';
cabecera('Generar llaves RSA y certificado X.509');

$salida = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bits = (int) ($_POST['bits'] ?? 2048);
    $dn = [
        'countryName' => substr(trim($_POST['pais'] ?? 'PA'), 0, 2),
        'organizationName' => trim($_POST['organizacion'] ?? 'UTP'),
        'commonName' => trim($_POST['cn'] ?? 'localhost'),
        'emailAddress' => trim($_POST['correo'] ?? ''),
    ];
    $cfg = config_openssl($bits);
    $llave = openssl_pkey_new($cfg);
    if ($llave === false) {
        $salida = '<p class="mal">Error: ' . e((string) openssl_error_string()) . '</p>';
    } else {
        openssl_pkey_export($llave, $privada, null, $cfg);
        $publica = openssl_pkey_get_details($llave)['key'];
        $csr = openssl_csr_new($dn, $llave, $cfg);
        $x509 = openssl_csr_sign($csr, null, $llave, 365, $cfg);
        openssl_x509_export($x509, $certPem);
        if (!is_dir(DIR_LLAVES)) mkdir(DIR_LLAVES, 0700, true);
        if (!is_dir(DIR_CERT)) mkdir(DIR_CERT, 0755, true);
        file_put_contents(DIR_LLAVES . '/private_key.pem', $privada);
        file_put_contents(DIR_LLAVES . '/public_key.pem', $publica);
        file_put_contents(DIR_CERT . '/certificado.crt', $certPem);
        $info = openssl_x509_parse($x509);
        $salida = '<div class="caja"><h2>Resultado</h2>'
            . '<p class="ok">Llaves y certificado generados y guardados en disco.</p>'
            . '<h3>Llave pública (public_key.pem)</h3><pre>' . e($publica) . '</pre>'
            . '<h3>Llave privada (primeras líneas; el archivo completo queda en keys/)</h3><pre>'
            . e(implode("\n", array_slice(explode("\n", $privada), 0, 4))) . "\n[...]</pre>"
            . '<h3>Certificado (certificado.crt)</h3><pre>' . e($certPem) . '</pre>'
            . '<h3>Datos del certificado</h3><pre>Sujeto: ' . e(json_encode($info['subject'], JSON_UNESCAPED_UNICODE))
            . "\nVálido desde: " . e(date('Y-m-d H:i', $info['validFrom_time_t']))
            . "\nVálido hasta: " . e(date('Y-m-d H:i', $info['validTo_time_t']))
            . "\nSerie: " . e((string) $info['serialNumberHex'])
            . "\nAlgoritmo de firma: " . e($info['signatureTypeSN']) . '</pre></div>';
    }
}
?>
<form method="post">
  <label>Tamaño de la llave RSA</label>
  <select name="bits"><option>2048</option><option>3072</option><option>4096</option></select>
  <label>País (2 letras)</label><input type="text" name="pais" value="PA">
  <label>Organización</label><input type="text" name="organizacion" value="Universidad Tecnológica de Panamá">
  <label>Nombre común (CN)</label><input type="text" name="cn" value="localhost">
  <label>Correo</label><input type="text" name="correo" value="estudiante@utp.ac.pa">
  <button>Generar llaves y certificado</button>
</form>
<?= $salida ?>
<?php pie();
