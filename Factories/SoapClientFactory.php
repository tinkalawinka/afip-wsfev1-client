<?php
namespace AfipClient\Factories;

class SoapClientFactory
{

    /**
     * Crea un cliente soap
     * @param string $wsdl
     * @param string $end_point
     * @return SoapClient
     */
    public static function create($wsdl, $end_point, $soap_version = 'SOAP_1_2')
    {
        return new \SoapClient(
        
            $wsdl,
                [
                    'soap_version'  => $soap_version,
                    'location'      => $end_point,
                    'exceptions'    => 1,
                    'trace'         => 1
                ]
        );
    }
}
