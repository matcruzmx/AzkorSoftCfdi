``` TXT

cfdi/
├── src/
│   ├── CFDI40/
│   │   ├── Comprobante.php
│   │   ├── Emisor.php
│   │   ├── Receptor.php
│   │   ├── Concepto.php
│   │   ├── Impuestos/
│   │   │   ├── Traslado.php
│   │   │   └── Retencion.php
│   │   └── Cfdi.php
│   │
│   ├── XML/
│   │   ├── XmlBuilder.php
│   │   ├── XmlSigner.php
│   │   └── XmlValidator.php
│   │
│   ├── SAT/
│   │   ├── Catalogos.php
│   │   └── RegimenFiscal.php
│   │
│   ├── PAC/
│   │   ├── PacInterface.php
│   │   └── Providers/
│   │       ├── Finkok.php
│   │       └── Facturama.php
│   │
│   ├── Exceptions/
│   │   └── CfdiException.php
│   │
│   └── Utils/
│       ├── Certificado.php
│       └── CadenaOriginal.php
│
├── tests/
├── composer.json
└── README.md
```

## Ejemplo de Uso

```PHP
$cfdi = new Cfdi();

$cfdi->comprobante()
    ->tipoDeComprobante('I')
    ->serie('A')
    ->folio('123')
    ->formaPago('03')
    ->metodoPago('PUE');

$cfdi->emisor()
    ->rfc('AAA010101AAA')
    ->nombre('Empresa SA de CV')
    ->regimenFiscal('601');

$cfdi->receptor()
    ->rfc('XAXX010101000')
    ->nombre('Publico en General')
    ->usoCfdi('G03')
    ->domicilioFiscal('99999')
    ->regimenFiscal('616');

$cfdi->validar();
```


## Ejemplo de uso con XML Builder
```PHP
use AzkorSoft\Cfdi\Cfdi40\Cfdi;
use AzkorSoft\Cfdi\XML\XmlBuilder;

$cfdi = new Cfdi();

$cfdi->comprobante()
    ->tipoDeComprobante('I')
    ->serie('A')
    ->folio('123')
    ->formaPago('03')
    ->metodoPago('PUE')
    ->moneda('MXN');

$cfdi->emisor()
    ->rfc('AAA010101AAA')
    ->nombre('Empresa SA de CV')
    ->regimenFiscal('601');

$cfdi->receptor()
    ->rfc('XAXX010101000')
    ->nombre('Publico en General')
    ->usoCfdi('G03')
    ->domicilioFiscal('99999')
    ->regimenFiscal('616');

$xml = (new XmlBuilder())->build($cfdi);

file_put_contents('cfdi.xml', $xml);
```

## Ejemplo de uso con conceptos
```PHP
use AzkorSoft\Cfdi\Cfdi40\Concepto;

$cfdi->conceptos()->add(
    (new Concepto())
        ->claveProdServ('01010101')
        ->claveUnidad('H87')
        ->cantidad(2)
        ->valorUnitario(500)
        ->descripcion('Servicio de desarrollo')
);
```


## Ejemplo IVA 16%
```PHP
use AzkorSoft\Cfdi\Cfdi40\Impuestos\Traslado;

$concepto = (new Concepto())
    ->claveProdServ('01010101')
    ->claveUnidad('H87')
    ->cantidad(1)
    ->valorUnitario(1000)
    ->descripcion('Servicio de desarrollo');

$concepto->impuestos()->addTraslado(
    new Traslado(
        impuesto: '002',        // IVA
        base: 1000,
        tasaOCuota: 0.160000
    )
);

$cfdi->conceptos()->add($concepto);
```

## Uso completo ya sellado
```PHP
use AzkorSoft\Cfdi\XML\XmlBuilder;
use AzkorSoft\Cfdi\Security\Certificado;
use AzkorSoft\Cfdi\Security\LlavePrivada;

$builder = new XmlBuilder();

$xml = $builder->build($cfdi);

$cert = new Certificado('/ruta/csd.cer');
$key  = new LlavePrivada('/ruta/csd.key', 'password');

$xmlSellado = $builder->sellar(
    $xml,
    $cert,
    $key,
    '/ruta/cadenaoriginal_4_0.xslt'
);

file_put_contents('cfdi_sellado.xml', $xmlSellado);
```


## Flujo completo con PAC
```PHP
use AzkorSoft\Cfdi\XML\XmlBuilder;
use AzkorSoft\Cfdi\Security\Certificado;
use AzkorSoft\Cfdi\Security\LlavePrivada;
use AzkorSoft\Cfdi\PAC\Providers\Finkok;

// 1️⃣ Generar XML
$builder = new XmlBuilder();
$xml = $builder->build($cfdi);

// 2️⃣ Sellar
$cert = new Certificado('/csd.cer');
$key  = new LlavePrivada('/csd.key', 'password');

$xmlSellado = $builder->sellar(
    $xml,
    $cert,
    $key,
    '/cadenaoriginal_4_0.xslt'
);

// 3️⃣ Timbrar
$pac = new Finkok(
    user: 'demo',
    password: 'demo',
    production: false
);

$result = $pac->timbrar($xmlSellado);

// 4️⃣ Guardar
file_put_contents(
    'cfdi_timbrado.xml',
    $result->xmlTimbrado
);

echo $result->uuid;
```


## Flujo con timbrado SW
```PHP
use AzkorSoft\Cfdi4\Sellado\CadenaOriginal;
use AzkorSoft\Cfdi4\Sellado\SelloDigital;
use AzkorSoft\Cfdi4\Pac\SwPac;

// 1. XML base
$xml = $cfdiBuilder->getXml();

// 2. Cadena original
$cadena = CadenaOriginal::generar(
    $xml,
    __DIR__ . '/xslt/cadenaoriginal_4_0.xslt'
);

// 3. Sellar
$sello = SelloDigital::sellar(
    $cadena,
    '/ruta/csd.key',
    'password'
);

// 4. Insertar sello y certificado
$xmlSellado = str_replace(
    '</cfdi:Comprobante>',
    ' Sello="'.$sello.'" Certificado="'.$certificado.'" NoCertificado="'.$noCert.'"></cfdi:Comprobante>',
    $xml
);

// 5. Timbrar con SW
$pac = new SwPac('TOKEN_SW', false);
$response = $pac->timbrar($xmlSellado);

if (!$response->success) {
    throw new Exception($response->mensaje);
}

file_put_contents('cfdi_timbrado.xml', $response->xml);

```

## Isertar timbre fiscal a xml
```PHP
use AzkorSoft\Cfdi4\Xml\TimbreFiscalInserter;

// XML original sellado
$xmlSellado = $cfdiBuilder->getXml();

// XML timbrado (SW)
$xmlTimbrado = $response->xml;

// Insertar timbre en DOM
$xmlFinal = TimbreFiscalInserter::insertar(
    $xmlSellado,
    $xmlTimbrado
);

file_put_contents('cfdi_final.xml', $xmlFinal);
```


## Uso de cancelacion SW
```PHP
use AzkorSoft\Cfdi4\Cancelacion\SwCancelador;

$cancelador = new SwCancelador('TOKEN_SW', false);

$response = $cancelador->cancelar(
    'UUID-DEL-CFDI',
    'AAA010101AAA',
    '02'
);

if (!$response->success) {
    throw new Exception($response->mensaje);
}

file_put_contents(
    'acuse_cancelacion.xml',
    $response->acuse
);


// Respuesta
// <Acuse>
//    <Folios>
//       <UUID EstatusUUID="201" />
//    </Folios>
// </Acuse>
// 📌 201 = Cancelado correctamente
// 📌 202 = En proceso
// 📌 203 = No corresponde al emisor

```

## PDF CFdi y Cancelación
```PHP
use AzkorSoft\Cfdi4\Pdf\CfdiPdf;
use AzkorSoft\Cfdi4\Pdf\CancelacionPdf;

// PDF CFDI
CfdiPdf::generar(
    file_get_contents('cfdi_final.xml'),
    'factura.pdf'
);

// PDF Cancelación
CancelacionPdf::generar(
    'UUID-DEL-CFDI',
    '02',
    file_get_contents('acuse_cancelacion.xml'),
    'cancelacion.pdf'
);


## Uso de PAc SW
$pac = new SwPac('TOKEN_SW', false);

try {
    $result = $pac->timbrar($xmlSellado);

    // XML timbrado
    $xmlTimbrado = $result->xmlTimbrado;

    // Datos clave
    $uuid = $result->uuid;
    $fecha = $result->fechaTimbrado;

} catch (\RuntimeException $e) {
    // Log + manejo de error
    throw $e;
}
```
