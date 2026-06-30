<?php
/**
 * Criptografia - Wrapper de openssl_encrypt
 *
 * Usado pra criptografar arquivos de certificado (.pfx) e senhas em disco.
 *
 * Algoritmo: AES-256-CBC com chave derivada de uma chave mestra (env).
 *
 * Formato do blob criptografado (base64):
 *   <8 bytes IV base64><dados criptografados>
 *
 * A chave mestra vem da env NFSE_MASTER_KEY (32 bytes em base64 ou hex).
 * Se nao estiver configurada, gera um aviso e usa uma chave padrao (apenas dev).
 */

declare(strict_types=1);

final class Criptografia
{
    private const ALGO = 'aes-256-cbc';
    private const TAM_IV = 16;

    /**
     * Retorna a chave mestra de 32 bytes.
     * Prioridade: env NFSE_MASTER_KEY (base64/hex) > fallback dev.
     */
    public static function getChaveMestra(): string
    {
        $env = getenv('NFSE_MASTER_KEY') ?: '';
        if ($env === '') {
            error_log('[NFSe Cripto] AVISO: NFSE_MASTER_KEY nao configurada. Usando fallback de DESENVOLVIMENTO.');
            // Hash determinístico do hostname (apenas dev). Em prod isto eh INACEITAVEL.
            $fallback = hash('sha256', 'nfse-dev-' . gethostname(), true);
            return $fallback; // 32 bytes
        }
        // Tenta base64 primeiro
        $decoded = base64_decode($env, true);
        if ($decoded !== false && strlen($decoded) === 32) {
            return $decoded;
        }
        // Tenta hex
        if (strlen($env) === 64 && ctype_xdigit($env)) {
            return hex2bin($env);
        }
        // Fallback: hash da string
        return hash('sha256', $env, true);
    }

    /**
     * Criptografa dados (retorna string base64: IV + ciphertext).
     */
    public static function criptografar(string $dados): string
    {
        $iv = random_bytes(self::TAM_IV);
        $cipher = openssl_encrypt(
            $dados,
            self::ALGO,
            self::getChaveMestra(),
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($cipher === false) {
            throw new RuntimeException('Falha ao criptografar: ' . openssl_error_string());
        }
        return base64_encode($iv . $cipher);
    }

    /**
     * Descriptografa (espera formato: base64 de IV + ciphertext).
     */
    public static function descriptografar(string $blob): string
    {
        $raw = base64_decode($blob, true);
        if ($raw === false || strlen($raw) <= self::TAM_IV) {
            throw new RuntimeException('Blob criptografado invalido.');
        }
        $iv = substr($raw, 0, self::TAM_IV);
        $cipher = substr($raw, self::TAM_IV);
        $plain = openssl_decrypt(
            $cipher,
            self::ALGO,
            self::getChaveMestra(),
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($plain === false) {
            throw new RuntimeException('Falha ao descriptografar (chave mestra pode ter mudado).');
        }
        return $plain;
    }

    /**
     * Criptografa um arquivo (.pfx) e grava em disco.
     * @return string Caminho do arquivo gravado (relativo a /home/sistema/nfse-nacional/certs/)
     */
    public static function criptografarArquivo(string $origem, int $empresaId, string $alias): string
    {
        $dados = file_get_contents($origem);
        if ($dados === false) {
            throw new RuntimeException('Nao foi possivel ler o arquivo: ' . $origem);
        }
        $cripto = self::criptografar($dados);
        $dir = NFSE_ROOT . '/certs';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
            chown($dir, posix_geteuid() === 0 ? 'sistema' : null);
        }
        $slug = preg_replace('/[^a-z0-9]+/i', '_', strtolower($alias));
        $arquivo = sprintf('empresa_%d_%s_%s.pfx.enc', $empresaId, $slug, substr(sha1(uniqid('', true)), 0, 8));
        $caminho = $dir . '/' . $arquivo;
        if (file_put_contents($caminho, $cripto) === false) {
            throw new RuntimeException('Nao foi possivel gravar o arquivo criptografado.');
        }
        chmod($caminho, 0640);
        return $arquivo;
    }

    /**
     * Le arquivo criptografado e retorna conteudo em claro.
     */
    public static function lerArquivo(string $arquivoRelativo): string
    {
        $caminho = NFSE_ROOT . '/certs/' . $arquivoRelativo;
        if (!file_exists($caminho)) {
            throw new RuntimeException('Arquivo de certificado nao encontrado.');
        }
        $blob = file_get_contents($caminho);
        if ($blob === false) {
            throw new RuntimeException('Nao foi possivel ler o arquivo.');
        }
        return self::descriptografar($blob);
    }
}
