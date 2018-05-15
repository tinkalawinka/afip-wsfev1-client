<?php
namespace AfipClient\Factories;

use AfipClient\AuthParamsProvider;
use AfipClient\Clients\Padron\PadronClient;
use AfipClient\Factories\SoapClientFactory;
use AfipClient\Factories\AccessTicketProcessorFactory;
use AfipClient\Clients\Padron\PadronRequestManager;
use AfipClient\Clients\Padron\PadronResponseManager;

class PadronClientFactory
{

    /**
     * Crea un PadronClient
     * @param array $conf
     * @param string $cert_file_name nombre del archivo del certificado obtenido de afip
     * @param string $key_file_name nombre del archivo de la clave que se uso para firmar
     * @param string $nombre_ws keyname del WS. Por ejemplo, 'ws_sr_padron_a5' para Acceso 5.
     * @return PadronClient
     */
    public static function create(
        array $conf, 
        \SoapClient $soap_client = null, 
        AuthParamsProvider $auth_params_provider = null, 
        PadronRequestManager $request_manager = null, 
        PadronResponseManager $response_manager = null,
        string $nombre_ws = 'ws_sr_padron_a5'
    )
    {
        return new PadronClient(
            $soap_client ? $soap_client : SoapClientFactory::create(
                $conf['padron_wsdl'], 
                $conf['padron_end_point'],
                'SOAP_1_1' // necesario para obtener un error claro de parte del WS
            ), 
            $auth_params_provider ? $auth_params_provider : AccessTicketProcessorFactory::create($conf), 
            $request_manager ?: new PadronRequestManager(), 
            $response_manager ?: new PadronResponseManager(),
            $nombre_ws
        );
    }
}
