<?php
require_once './app/pdf/fpdf.php';

use \App\Models\Usuario as Usuario;
use \App\Models\Venta as Venta;
use \App\Models\Criptomoneda as Criptomoneda;


class ManejoArchivos
{

    public function GuardarPDF($request,$response,$next)
    {
        $bool = false;
        $pdf = new FPDF('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(190,15,ucfirst("Ventas"),"0","1","C");

        $lista = Venta::all();
        $ventas = json_decode(json_encode(array("listaCompleta" => $lista)));
        foreach($ventas->listaCompleta as $venta)
        {
            $pdf->SetFont('Arial','',10);
            $datos = $this->DatosToPDF($venta);
            $datos = iconv('UTF-8', 'windows-1252', $datos);
            $pdf->MultiCell(0,4,$datos,"0","L");
            $pdf->Ln(1);
        }
        if($datos != null)
        {
            $payload = json_encode(array("mensaje" => "Se guardo el PDF de empleados"));
            $pdf->Output('F',"./app/archivos/ventas.pdf",true);
            $bool = true;
        }else
        {
            $payload = json_encode(array("mensaje" => "No se guardo el PDF"));
        }
        
        $response->getBody()->write($payload);
        return $bool;
    }

    

    public function DatosToPDF($datos)
    {
        $cadena = "";
        $cadena .= "- ID: " . $datos->id . ", ID Criptomoneda: " . $datos->id_criptomoneda . ", idCliente: " . $datos->id_cliente . ", Cantidad: " . $datos->cantidad . ", Fecha de Total: " . $datos->total . ", Fecha de Compra: " . $datos->fecha_de_compra;
        return $cadena;  
    }

}
?>