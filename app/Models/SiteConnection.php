<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class SiteConnection extends Model
{
    protected $fillable = [
        'site_id',
        'type',
        'host',
        'port',
        'username',
        'encrypted_credential',
        'passphrase_encrypted',
        'root_path',
        'status',
        'last_check_at',
        'latency_ms',
        'error_message',
    ];

    /**
     * Oculta dados extremamente sensíveis de qualquer retorno em JSON ou Array.
     */
    protected $hidden = [
        'encrypted_credential',
        'passphrase_encrypted',
    ];

    protected $casts = [
        'last_check_at' => 'datetime',
        'port' => 'integer',
        'latency_ms' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Salva a credencial (senha ou chave privada) de forma segura criptografada com AES-256-GCM.
     */
    public function setCredential(string $plainTextCredential): void
    {
        $this->attributes['encrypted_credential'] = Crypt::encryptString($plainTextCredential);
    }

    /**
     * Recupera a credencial descriptografada apenas no backend.
     */
    public function getDecryptedCredential(): ?string
    {
        if (empty($this->attributes['encrypted_credential'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['encrypted_credential']);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Salva a passphrase da chave privada SSH de forma criptografada.
     */
    public function setPassphrase(?string $plainTextPassphrase): void
    {
        if (empty($plainTextPassphrase)) {
            $this->attributes['passphrase_encrypted'] = null;
        } else {
            $this->attributes['passphrase_encrypted'] = Crypt::encryptString($plainTextPassphrase);
        }
    }

    /**
     * Recupera a passphrase descriptografada.
     */
    public function getDecryptedPassphrase(): ?string
    {
        if (empty($this->attributes['passphrase_encrypted'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['passphrase_encrypted']);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isConnected(): bool
    {
        return $this->status === 'conectado';
    }
}
