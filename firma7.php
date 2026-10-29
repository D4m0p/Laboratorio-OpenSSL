<?php
require __DIR__ . '/comun.php';
cabecera('firma7: firma digital con llaves en disco');

$resultado = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = $_POST['datos'] ?? '';
    $aVerificar = $_POST['verificar'] ?? $datos;

    [$privadaPem, $publicaPem] = cargar_llaves();
    // Obtengo la clave privada como resource desde el string guardado en disco.
    $recursoPrivada = openssl_pkey_get_private($privadaPem);
    if (!openssl_sign($datos, $firma, $recursoPrivada, OPENSSL_ALGO_SHA256)) {
        exit(e((string) openssl_error_string()));
    }
    $recursoPublica = openssl_pkey_get_public($publicaPem);
    $r = openssl_verify($aVerificar, $firma, $recursoPublica, OPENSSL_ALGO_SHA256);

    $resultado = '<div class="caja"><h2>Resultado</h2>'
        . '<p><b>Mensaje firmado:</b> ' . e($datos) . '</p>'
        . '<p><b>Mensaje presentado para verificar:</b> ' . e($aVerificar) . '</p>'
        . '<p><b>Algoritmo:</b> RSA con SHA-256 (' . openssl_pkey_get_details($recursoPublica)['bits'] . ' bits)</p>'
        . '<h3>Firma (Base64)</h3><pre>' . e(base64_encode($firma)) . '</pre>'
        . '<h3>Llave pública usada</h3><pre>' . e($publicaPem) . '</pre>'
        . '<p><b>Verificación:</b> ' . resultado_verificacion($r) . '</p></div>';
}
?>
<form method="post">
  <label>Mensaje a firmar</label>
  <textarea name="datos" rows="3"><?= e($_POST['datos'] ?? 'Este documento fue firmado por David Moreno') ?></textarea>
  <label>Mensaje que se presenta para verificar (cámbielo para simular una alteración)</label>
  <textarea name="verificar" rows="2"><?= e($_POST['verificar'] ?? 'Este documento fue firmado por David Moreno') ?></textarea>
  <button>Firmar y verificar</button>
</form>
<?= $resultado ?>
<?php pie();
