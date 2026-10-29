# Laboratorio: Seguridad en PHP con OpenSSL

Desarrollo Web VII: PHP y MySQL, Universidad Tecnológica de Panamá, II Semestre 2026.
Estudiantes: David Moreno (8-1039-2423) y Erick Elizondro (8-1046-1786), grupo 1S3122.

## Ejecución

```
php -S 127.0.0.1:8000
```

Abrir `http://127.0.0.1:8000/`. Requiere PHP con las extensiones `openssl` y `sodium`.
Las llaves se generan en `keys/` y `keysCert/` (no se suben al repositorio).

## Contenido

| Archivo | Descripción |
|---|---|
| `generar_llaves.php` | Par de llaves RSA y certificado X.509 |
| `firma7.php` | Firma y verificación con llaves en disco |
| `firmaOtra.php` | Firma con SHA-256/384/512 y verificación con el certificado |
| `FirmarMensaje.php` | Emisión y verificación de mensajes firmados |
| `Encriptacion.php` | AES-256-GCM y RSA-OAEP |
| `sodium_ejemplo.php` | Libsodium: secretbox, Ed25519 y Argon2id |
| `informe/` | Informe en PDF, fuente HTML y capturas |
