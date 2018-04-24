# Librería para consumir el servicio wsfev1 de AFIP

Todos los webservices de AFIP necesitan pasar por un primer círculo de autenticación (WSAA), y operar con el token (TA) que devuelve el servicio WSAA. Esta librería resuelve la autenticación y luego el diálogo con el servicio `wsfev1`.

Para referencia:

- wsfev1 (Webservices de Factura Electrónica v1 - A, B y C sin detalle de items)
- wsmtxca (Web Service de Factura Electrónica - A y B con detalle de items)

La librería funciona actualmente para facturas A y B de `wsfev1`, pero tanto facturar comprobantes C como consumir el webservice `wsmtxca` no debería ser muy diferente a lo ya desarrollado. 

## Requisitos

Conseguir los certificados para interactuar con los webservices de AFIP.

Para generacion de certificados ver: http://www.afip.gob.ar/ws/WSASS/WSASS_manual.pdf

## Dependencias

- Extensión SoapClient de PHP

## Instalación

Via composer, apuntando al repo de github:

```
"repositories": [
    {
        "url": "git@github.com:libasoles/afip-wsfev1-client.git",
        "type": "git"
    }
],

"require": {   
    "libasoles/afip-wsfev1-client": "dev-master",   
},
```

## Configuración

```php
return [
    'auth_passphrase'    => 'laquetengas', // pass para firmar el certificado a enviar. Opcional
    'auth_wsdl'          => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?wsdl', 
    'auth_end_point'     => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms',
    'auth_cert_path'     => resource_path('assets/afip/cert.pem'), // certificado que la lib firma para enviar a api afip y autenticar
    'auth_key_path'      => resource_path('assets/afip/cert.key'), // clave con la que se genero el certificado en pagina de afip   
    'biller_wsdl'        => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?wsdl', 
    'biller_end_point'   => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx',
    'biller_sale_point'  => env('AFIP_SALE_POINT'), // si no se le pasa, el biller intentara obtenerlo desde la api de afip
    'tax_id'             => 9876543210, // Cuit del contribuyente
];
```

Para `wsmtxca` habría que empezar por cambiar la url de _biller_wsdl_ y _biller_end_point_

## Ejemplo de uso para Factura B

```php


require_once('vendor/autoload.php');

try {

    $conf = include( 'conf.php' );

    /* Servicio de facturación */            
    $biller = BillerFactory::create( $conf );

    $data = array(
        'Cuit' => '123456789', // Cuit del contribuyente
        'CantReg' => 1,
        'PtoVta' => $conf['biller_sale_point'], // null para que lo intente obtener el web service
        'CbteTipo' => 06, // * ver referencia
        'Concepto' => 2, // 1:producto 2:servicios
        'DocTipo' => 80, // 80:CUIL/CUIT
        'DocNro' => '123456789',
        'CbteDesde' => null, // para que lo calcule uitlizando el web service 
        'CbteHasta' => null, // para que lo calcule uitlizando el web service
        'CbteFch' => date('Ymd'),
        'ImpNeto' => 0,
        'ImpTotConc' => 1, 
        'ImpIVA' => 0,
        'ImpTrib' => 0,
        'ImpOpEx' => 0,
        'ImpTotal' => 1, 
        'FchServDesde' => date("Ymd"), // solo necesarios al facturar servicios
        'FchServHasta' => date("Ymd"), 
        'FchVtoPago' => date("Ymd"), // solo necesario al facturar servicios
        'MonId' => 'PES', // PES:Peso Argentino, DOL:Dolar Estadounidense
        'MonCotiz' => 1, // Cotización moneda. Para pesos, debe ser 1.
    );

    //solicita cae, cae_validdate, etc
    var_dump( $biller->requestCAE( $data ) );

    /* Response skel
    [ 
        'cae' => '', 
        'cae_validdate' => '',
        'invoice_number' => '',
        'sale_point' => '',
        'invoice_date' => '',
        'tax_id' => '',
        'full_response' => '',
    ];*/ 
    
} catch ( ACException $e ) {
    var_dump( $e->getMessage() );
}

```

`*` *Referencia CbteTipo*
    
- 1: Factura A
- 2: Nota de Débito A
- 3: Nota de Crédito A
- 6: Factura B
- 7: Nota de Débito B
- 8: Nota de Crédito B 

## Ejemplo de Factura A implementado en Laravel

### Variables archivo .env

```
AFIP_PASSPHRASE=laquetengas
AFIP_SALE_POINT=1
AFIP_CUIT=9876543210
```
### Archivo de configuración afip.php

```php
return [
    'auth_passphrase'    => env('AFIP_PASSPHRASE'), // pass para firmar el certificado a enviar. Opcional
    'auth_wsdl'          => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?wsdl', 
    'auth_end_point'     => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms',
    'auth_cert_path'     => resource_path('assets/afip/cert.pem'), // certificado que la lib firma para enviar a api afip y autenticar
    'auth_key_path'      => resource_path('assets/afip/cert.key'), // clave con la que se genero el certificado en pagina de afip   
    'biller_wsdl'        => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?wsdl', 
    'biller_end_point'   => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx',
    'biller_sale_point'  => env('AFIP_SALE_POINT'), // si no se le pasa, el biller intentara obtenerlo desde la api de afip
    'tax_id'             => env('AFIP_CUIT')
];
```

### Parametros

```php
$params = array(
    'Cuit' => preg_replace('/[^0-9]+/', '', config('afip.tax_id')), // cuit contribuyente
    'CantReg' => 1,
    'PtoVta' => config('afip.biller_sale_point'), 
    'CbteTipo' => 01, // Factura A
    'Concepto' => 1, // 1:productos, 2:servicios
    'DocTipo' => 80, // 80:CUIT
    'DocNro' => preg_replace('/[^0-9]+/', '', $request->input('cuit')), // cuit cliente
    'CbteDesde' => null, 
    'CbteHasta' => null, 
    'CbteFch' => date('Ymd'),
    'ImpNeto' => $net_amount,
    'ImpTotConc' => 0, 
    'ImpIVA' => $iva,
    'Iva' => [
        // este desglose debe sumar lo declarado en ImpNeto y ImpIVA
        'AlicIva' => [
            'Id' => 5, // 21%
            'BaseImp' => $net_amount,
            'Importe' => $iva,
        ]
    ],
    'ImpTrib' => 0, 
    'ImpOpEx' => 0, 
    'ImpTotal' => $total_amount_in_pesos, 
    'MonId' => 'PES', // Argentine Pesos
    'MonCotiz' => 1, // 1  
);
```

Nota: para Tributos hay que agregar los datos al igual que con el Iva, y luego agregar el parametro en la librería (no esta desarrollado actualmente pero es algo facil de agregar)

## Llamada

```php
public function store(Request $request) 
{
    $this->validate($request, [/*...*/]);
    
    $params = [/*...*/]; // ver mas arriba

    try {
        
        // Request cae y cae_validdate 
        $biller = BillerFactory::create( config('afip') );
        $afip_response = $biller->requestCAE( $params );
        
        // ...
    } catch (ACException $e ) {
        // ...
        
    } catch(\Exception $e) {
       // ...
    }
    
    // ...
}
```

--------------------------------------------------------------------------
**Manuales AFIP**

- Docs: http://www.afip.gob.ar/fe/ayuda.asp

- Auth: http://www.afip.gob.ar/ws/WSAA/Especificacion_Tecnica_WSAA_1.2.2.pdf

- F.E.: http://www.afip.gob.ar/fe/documentos/manual_desarrollador_COMPG_v2_9.pdf

----------------------------------------------------------------------------
