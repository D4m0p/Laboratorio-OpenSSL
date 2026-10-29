<?php
require __DIR__ . '/comun.php';
cabecera('FirmarMensaje: emitir y verificar un mensaje firmado');

[$privadaPem, $publicaPem] = cargar_llaves();
$firmado = '';
$verificado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['accion'] ?? '') === 'firmar') {
        $mensaje = $_POST['mensaje'] ?? '';
        openssl_sign($mensaje, $firma, openssl_pkey_get_private($privadaPem), OPENSSL_ALGO_SHA256);
        $firmado = '<div class="caja"><h2>Mensaje firmado</h2><p><b>Mensaje:</b> ' . e($mensaje)
            . '</p><h3>Firma (Base64): cópiela en el segundo formulario</h3><pre>'
            . e(base64_encode($firma)) . '</pre></div>';
    } else {
        $mensaje = $_POST['mensaje_v'] ?? '';
        $firma = base64_decode($_POST['firma_v'] ?? '', true);
        if ($firma === false || $firma === '') {
            $verificado = '<p class="mal">La firma no es Base64 válido.</p>';
        } else {
            $r = openssl_verify($mensaje, $firma, openssl_pkey_get_public($publicaPem), OPENSSL_ALGO_SHA256);
            $verificado = '<div class="caja"><h2>Verificación</h2><p>' . resultado_verificacion($r) . '</p></div>';
        }
    }
}
?>
<h2>1. Firmar</h2>
<form method="post">
  <input type="hidden" name="accion" value="firmar">
  <label>Mensaje</label>
  <textarea name="mensaje" rows="2"><?= e($_POST['mensaje'] ?? 'Apruebo la transferencia de 100 dólares') ?></textarea>
  <button>Firmar mensaje</button>
</form>
<?= $firmado ?>
<h2>2. Verificar</h2>
<form method="post">
  <input type="hidden" name="accion" value="verificar">
  <label>Mensaje recibido</label>
  <textarea name="mensaje_v" rows="2"><?= e($_POST['mensaje_v'] ?? '') ?></textarea>
  <label>Firma (Base64)</label>
  <textarea name="firma_v" rows="3"><?= e($_POST['firma_v'] ?? '') ?></textarea>
  <button>Verificar firma</button>
</form>
<?= $verificado ?>
<?php pie();
