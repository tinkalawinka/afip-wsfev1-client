<?php
namespace AfipClient\Clients\Padron;

/**
 * Devolver la parte de la consulta que nos resulta relevante
 */
class PadronResponseManager
{
    public function validateAndParseResponse(\stdClass $response)
    {
       
        if (isset($response->personaReturn->errorConstancia) ||
            !($response->personaReturn->datosGenerales)) {
            return false;
        }

        return $response->personaReturn->datosGenerales;
    }
}
