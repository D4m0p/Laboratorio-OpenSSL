<?php
require __DIR__ . '/comun.php';
cabecera('firmaOtra: firma con algoritmo a elección y certificado');

$algoritmos = [
    'SHA256' => OPENSSL_ALGO_SHA256,
    'SHA384' => OPENSSL_ALGO_SHA384,
    'SHA512' => OPENSSL_ALGO_SHA512,
];
$resultado = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = $_POST['datos'] ?? '';
    $nombreAlgo = array_key_exists($_POST['algoritmo'] ?? '', $algoritmos) ? $_POST['algoritmo'] : 'SHA256';

    [$privadaPem] = cargar_llaves();
    if (!is_file(DIR_CERT . '/certificado.crt')) {
        $resultado = '<p class="mal">Primero genere el certificado en <a href="generar_llaves.php">generar_llaves.php</a>.</p>';
    } else {
        $certPem = file_get_contents(DIR_CERT . '/certificado.crt');
        // La verificación se hace con la llave pública contenida en el certificado.
        $publicaDelCert = openssl_pkey_get_public(openssl_x509_read($certPem));
        openssl_sign($datos, $firma, openssl_pkey_get_private($privadaPem), $algoritmos[$nombreAlgo]);
        $r = openssl_verify($datos, $firma, $publicaDelCert, $algoritmos[$nombreAlgo]);
        $huella = openssl_x509_fingerprint($certPem, 'sha256');

        $resultado = '<div class="caja"><h2>Resultado</h2>'
            . '<p><b>Mensaje:</b> ' . e($datos) . '</p>'
            . '<p><b>Algoritmo de hash:</b> ' . e($nombreAlgo) . ' con RSA</p>'
            . '<p><b>Huella SHA-256 del certificado:</b> ' . e((string) $huella) . '</p>'
            . '<h3>Firma (hexadecimal)</h3><pre>' . e(bin2hex($firma)) . '</pre>'
            . '<p><b>Verificación con el certificado:</b> ' . resultado_verificacion($r) . '</p></div>';
    }
}
?>
<form method="post">
  <label>Mensaje</label>
  <textarea name="datos" rows="3"><?= e($_POST['datos'] ?? 'Contrato de prueba No. 001') ?></textarea>
  <label>Algoritmo de hash</label>
  <select name="algoritmo">
    <?php foreach ($algoritmos as $n => $_): ?>
      <option <?= (($_POST['algoritmo'] ?? '') === $n) ? 'selected' : '' ?>><?= e($n) ?></option>
    <?php endforeach; ?>
  </select>
  <button>Firmar y verificar</button>
</form>
<?= $resultado ?>
<?php pie();
