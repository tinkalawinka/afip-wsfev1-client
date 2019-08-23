<?php
namespace AfipClient\Clients\Padron;

use AfipClient\ACException;
use AfipClient\ACHelper;
use AfipClient\AuthParamsProvider;
use AfipClient\Clients\Client;
use AfipClient\Clients\Padron\PadronRequestManager;
use AfipClient\Clients\Padron\PadronResponseManager;

/**
 * Client de facturación electrónica, encargado de interactuar con api WSFEV1 de Afip
 */
class PadronClient extends Client
{
    protected $client_name; 
    protected $soap_client;
    protected $auth_params_provider;
    protected $request_manager;
    protected $response_manager;

    /**
     * @param SoapClient $soap_client SoapClientFactory::create( [wsdl], [end_point] )
     * @param AuthParamsProvider $acces_ticket_manager el objeto encargado de procesar y completar el AccessTicket
     * @param PadronRequestManager $request_manager el objeto encargado de manejar la consulta
     * @param PadronResponseManager $biller_response el objeto encargado de manejar la respuesta
     */
    public function __construct( 
                                 \SoapClient $soap_client,
                                 AuthParamsProvider $auth_params_provider,
                                 PadronRequestManager $request_manager,
                                 PadronResponseManager $response_manager,
                                 string $ws_name
    ) {
        $this->soap_client = $soap_client;
        $this->auth_params_provider = $auth_params_provider;
        $this->request_manager = $request_manager;
        $this->response_manager = $response_manager;
        $this->client_name = $ws_name;
    }
    
    public function getPersona($data = [])
    {
     
        $request_params = $this->request_manager->buildParams($this, $this->_getAuthParams(), $data);
        
        $response = $this->soap_client->getPersona($request_params);
    
        $parsed_data = $this->response_manager->validateAndParseResponse($response);

        if (!$parsed_data) {
            throw new ACException(
            "Error obteniendo datos de la empresa", $this, ACHelper::export_response($response)
            );
        }

        return $parsed_data;
    }
    
    /**
     * Devuelve array con datos de acceso consultando el AccessTicket
     * @return array ['token' => '', 'sign' => '', 'cuit' => '']
     */
    private function _getAuthParams()
    {
        return $this->auth_params_provider->getAuthParams($this);
    }
}
