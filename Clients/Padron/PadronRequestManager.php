<?php
namespace AfipClient\Clients\Padron;

use AfipClient\ACException;
use AfipClient\Clients\Padron\PadronClient;

/**
 * 
 */
class PadronRequestManager
{

    /**
     * Armar el array para ser enviado al cliente
     * @param PadronClient $client
     * @param array $data
     * @param array $auth_params
     * @return array $params
     */
    public function buildParams(PadronClient $client, $auth_params, $data)
    {
        $params = [
            'token'             => $auth_params['Token'],
            'sign'              => $auth_params['Sign'],
            'cuitRepresentada'  => $data['cuitRepresentada'],
            'idPersona'         => $data['idPersona']
        ];
 
        return $params;
    }
}
