<?php
namespace App\Models;
use CodeIgniter\Model;
class Cae_model extends Model
{
	protected $table = 'cae';
    protected $primaryKey = 'id_cae';
    protected $allowedFields = ['id_cae','cae','vto_cae'];


function generarQR($id_cabecera) {
    $db = \Config\Database::connect();

    // Query para obtener los datos de la factura
    $query = $db->query("
        SELECT 
            vc.id_cae AS nroCmp,  -- Este es el número de comprobante que ARCA usa
            vc.fecha_pedido AS fecha, 
            vc.total_bonificado AS importe, 
            c.cuil AS nroDocRec, 
            ca.cae AS codAut, 
            ca.vto_cae AS fechaVtoCae 
        FROM ventas_cabecera vc
        JOIN cliente c ON vc.id_cliente = c.id_cliente
        JOIN cae ca ON vc.id_cae = ca.id_cae
        WHERE vc.id = ?
    ", [$id_cabecera]);

    $resultado = $query->getRow();

    if (!$resultado) {
        return "Factura no encontrada.";
    }

    // Determinar el tipo de documento del receptor (cliente)
    if (empty($resultado->nroDocRec) || $resultado->nroDocRec == 0) {
        $tipoDocRec = 99;  // 99 = Consumidor Final
        $nroDocRec = 0;
    } else {
        $tipoDocRec = 80;  // 80 = CUIT
        $nroDocRec = (int) $resultado->nroDocRec;
    }

    // Construcción del JSON para el QR de ARCA
    $data = [
        "ver" => 1,
        "fecha" => $resultado->fecha,
        "cuit" => 20369557263,  // CUIT del negocio
        "ptoVta" => 2,  // Punto de venta fijo
        "tipoCmp" => 11,  // Factura C
        "nroCmp" => (int) $resultado->nroCmp,  // ID del CAE (Número de comprobante real)
        "importe" => (float) $resultado->importe,  // Monto total
        "moneda" => "PES",  // Pesos argentinos
        "ctz" => 1,  // Cotización
        "tipoDocRec" => $tipoDocRec,  // Tipo de documento (CUIT o Consumidor Final)
        "nroDocRec" => $nroDocRec,  // CUIT del cliente o 0 si es Consumidor Final
        "tipoCodAut" => "E",  // Electrónico
        "codAut" => $resultado->codAut  // Código de Autorización Electrónico (CAE)
    ];

    // Convertir a JSON
    $json = json_encode($data);
    $base64url = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($json));

    // URL del QR para ARCA (AFIP)
    $url_qr = "https://www.afip.gob.ar/fe/qr/?p=" . $base64url;

    // Devolver URL del QR
    return $url_qr;
}


}