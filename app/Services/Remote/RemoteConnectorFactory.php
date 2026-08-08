<?php

namespace App\Services\Remote;

use App\Models\SiteConnection;
use App\Services\Remote\Contracts\RemoteConnectorInterface;
use Exception;

class RemoteConnectorFactory
{
    /**
     * Instancia o conector correto com base no tipo de conexão configurado.
     */
    public static function make(SiteConnection $connection): RemoteConnectorInterface
    {
        return match ($connection->type) {
            'sftp', 'ssh' => new SftpConnector($connection),
            'ftp', 'ftps'  => new FtpConnector($connection),
            default       => throw new Exception("Tipo de conexão '{$connection->type}' não é suportado atualmente."),
        };
    }
}
