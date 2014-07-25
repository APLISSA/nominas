<?php
session_start();
ob_start();

//Conexiones a bases de datos
include("../db/db.php");
include ("../db/advsecu.php");
include ("../includeFechas.php"); 
include ("../funcionesVarias.php");

if (!IsLoggedIn()) {
	ob_end_clean();
	header("Location: login.php");
	exit();
}

header ("Cache-Control: no-cache, must-revalidate"); //no guardar en CACHE
header ("Pragma: no-cache");

//Nos conectamos a la base de datos
$conn = phpmkr_db_connect(HOST, USER, PASS, DB, PORT); 

require_once "class.writeexcel_workbook.inc.php";
require_once "class.writeexcel_worksheet.inc.php";

$fname = tempnam("/tmp", "MovimientosTotalesCheques.xls");
$workbook = &new writeexcel_workbook($fname);
$worksheet =& $workbook->addworksheet();
$worksheet ->writeexcel_worksheet('Movimientos Generales de Bancos (Completo)');
$worksheet2 =& $workbook->addworksheet();
$worksheet2->writeexcel_worksheet('Resumen General de Cuentas');

//Definimos las columnas a utilizar

//Defino el tamaño de las columnas
$worksheet->set_column(0, 1, 20); //Fecha
$worksheet->set_column(1, 2, 30); //Tipo de Movimiento
$worksheet->set_column(2, 2, 45); //Beneficiario
$worksheet->set_column(3, 3, 45); //Concepto
$worksheet->set_column(4, 4, 20); //Referencia
$worksheet->set_column(5, 5, 20); //Tipo de cliente
$worksheet->set_column(6, 6, 35); //Observaciones
$worksheet->set_column(7, 7, 15); //monto
$worksheet->set_column(8, 8, 15); //cargo
$worksheet->set_column(9, 9, 15); //abono
$worksheet->set_column(10, 10, 15); //cargo
$worksheet->set_column(11, 11, 15); //abono
$worksheet->set_column(12, 12, 15); //abono
//Seleccionamos la fila q se queda fija
$worksheet->freeze_panes(8, 0); # 1 row


//Altura de las columnas donde van los titulos
$worksheet->set_row(0, 8);

# Create a format for the column headings
$header =& $workbook->addformat();
$header->set_bold();
$header->set_size(8);
$header->set_color('white');
$header->set_align('center');
$header->set_bg_color('gray');
$header->set_border(2,2,2,2);
$header->set_align('vjustify');

# Create a format for the column headings
$texto =& $workbook->addformat();
$texto->set_size(8);
$texto->set_align('vjustify');

# Create a format for the column headings
$textoA =& $workbook->addformat();
$textoA->set_size(8);
$textoA->set_align('left');

# Create a format for the column headings
$headerA =& $workbook->addformat();
$headerA->set_bold();
$headerA->set_font("Courier New");
$headerA->set_size(14);
$headerA->set_align('center');

# Create a format for the column headings
$headerB =& $workbook->addformat();
$headerB->set_bold();
$headerB->set_font("Courier New");
$headerB->set_size(10);

# Create a format for the column headings
$subheaderC =& $workbook->addformat();
$subheaderC->set_bold();
$subheaderC->set_font("Courier New");
$subheaderC->set_size(10);
$subheaderC->set_font_shadow();
$subheaderC->set_bg_color('gray');
$subheaderC->set_border(2,0,0,2);
$subheaderC->set_align('center');


# Create a format for the stock price
$f_price =& $workbook->addformat();
$f_price->set_align('rigth');
$f_price->set_num_format('$0.00');
$f_price->set_num_format('[Black]$0.000;[Red]-$0.000;$0.0000');
$f_price->set_font("Courier New");
$f_price->set_size(8);

//Generamos las fechas
rangosFechas($_SESSION[ewSessionMes], $_SESSION[ewSessionAnio]);

// Cabecera del Documento
$worksheet->write(1, 3, "REPORTE: Movimentos Generales Cheques Empresas y Cuentas Bancarias (Totales)", $headerA);
$worksheet->write(3, 3, 'Periodo de Fecha Del:'.$del.' Al:'.$al, $headerB);
$worksheet->write(4, 3, 'Fecha de Emisión del Reporte:'.date('d-m-Y'), $headerB);

//Generamos las columnas
$worksheet->write(7, 0,   'Fecha', $header);
$worksheet->write(7, 1,   'No Cheque', $header);
$worksheet->write(7, 2,   'Beneficiario', $header);
$worksheet->write(7, 3,   'Cliente',   $header);
$worksheet->write(7, 4,   'Concepto', $header);
$worksheet->write(7, 5,   'SubConcepto',   $header);
$worksheet->write(7, 6,   'Monto',   $header);
$worksheet->write(7, 7,   'IVA',  $header); 
$worksheet->write(7, 8,   'MultiConceptos',   $header);
$worksheet->write(7, 9,   'Cancelado',   $header);
$worksheet->write(7, 10,  'Total IVA',  $header); 
$worksheet->write(7, 11,  'Total Cheque',  $header); 
$worksheet->write(7, 12,  'Total Cuenta',  $header); 
//$worksheet->write(7, 13,  'Total Empresa',  $header); 

$renglonInicial=8;//Indica el renglon donde se inicia la impresion de los datos grales del empleado

//Generamos el for por empresa
$sqlEmpresa="Select claveEmpresa, nombre from BAempresas where (estatus='Ac' or estatus='Ca') order by  nombre ASC";
$queryEmpresa=mysql_query($sqlEmpresa);

while($fg=mysql_fetch_array($queryEmpresa)){
	//Imprimimos el nombre de la empresa a la que corresponde los mov
	if($fg["nombre"] != $tempEmp){
		$worksheet->write($renglonInicial, 0, 'EMPRESA:'.$fg["nombre"], $header);
		// Escribimos en la segunda hoja
		$worksheet2->write($renglonInicial, 0, 'EMPRESA: '.$fg['nombre'], $header);
		
		$tempEmp=$fg["nombre"];
	}
    else{
	 
	 }
	//Incrementamos el renglon
	$renglonInicial +=1;
	
	//Nos traemos las ctas que corresponde a la empresa
	$sqlCuentas  ="Select noCuenta, banco, saldoinicial, tipoCuenta from BAcuentasBancarias where claveEmpresa='".$fg["claveEmpresa"]."' and (estatus='Ac' or estatus='Ca')";
	$queryCuentas=mysql_query($sqlCuentas);
	$montoEmpresa=0;
	
	while($hb=mysql_fetch_array($queryCuentas)){
		//Imprimimos el nombre de la cuenta
		if($hb["noCuenta"] != $tempCta){	
            $saldo=saldoMesAnterior($hb["noCuenta"], $conn, $_SESSION[ewSessionMes], $_SESSION[ewSessionAnio]);
			$worksheet->write($renglonInicial, 1, 'No Cuenta:'.$hb["noCuenta"]." ",$headerB);
			$worksheet->write($renglonInicial, 2, "Banco:".$hb["banco"]. "", $headerB);
			$worksheet->write($renglonInicial, 3, "Tipo de Cuenta:".$hb["tipoCuenta"]."", $headerB);
			$worksheet->write($renglonInicial, 4, "Saldo Inicial del Mes : $".number_format($saldo,2)."", $headerB);
			$tempCta=$hb["noCuenta"];
			
			// Obtenemos los Saldos de la cuenta.
			$sqlSI = "SELECT saldoInicial FROM BAcuentasBancarias WHERE noCuenta LIKE '" . $hb['noCuenta'] . "' ";
			$querySI =mysql_query($sqlSI);
			
			while($rSI = mysql_fetch_assoc($querySI)) {
				$worksheet2->write($renglonInicial, 1, 'No Cuenta: '. $hb['noCuenta'] . ' ');
				$worksheet2->write($renglonInicial, 2, "Banco: " . $hb['banco'] . "");
				$worksheet2->write($renglonInicial, 3, "Tipo de cuenta: " . $hb['tipoCuenta'] . "");
				$worksheet2->write($renglonInicial, 4, "Saldo Inicial del Mes: " . $rSI['saldoInicial'] . "");
			}
			
			// Obtenemos el saldo hasta el mes anterior y el saldo de los consumos del mes.
			
			$sqlST = "SELECT FORMAT(SUM(montoMovimiento),2) as ST FROM BAmovimientosCuentas WHERE claveCuenta like '" . $hb['noCuenta'] . "' AND  fechaRegistro < DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day) ";
			$queryST = mysql_query($sqlST);
			while($rST = mysql_fetch_assoc($queryST)) {
				$worksheet2->write($renglonInicial, 5, "Saldo inicial al final del mes anterior: $" . $rST['ST'] . "");
			}
			
			// Obtenemos los Movimientos de cargo del mes
			$sqlCargos= "SELECT SUM(montoMovimiento) as sumC, COUNT(montoMovimiento) as contC FROM BAmovimientosCuentas WHERE claveCuenta like '" . $hb['noCuenta'] . "' AND  fechaRegistro between DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day)  AND CURDATE() AND descripcionMov = 1";
			$queryCargos = mysql_query($sqlCargos);
			while($rCar = mysql_fetch_assoc($queryCargos)) {
				$worksheet2->write($renglonInicial, 6, "Total Cargos: " . $rCar['sumC'] . " - Total de Movimientos: " . $rCar['contC'] . " ");
			}
			
			// Obtenemos los Movimientos de abono del mes
			
			$sqlAbonos= "SELECT SUM(montoMovimiento) as sumA, COUNT(montoMovimiento) as contA FROM BAmovimientosCuentas WHERE claveCuenta like '" . $hb['noCuenta'] . "' AND  fechaRegistro between DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day)  AND CURDATE() AND descripcionMov = 2";
			$queryAbonos = mysql_query($sqlCargos);
			while($rAbo = mysql_fetch_assoc($queryAbonos)) {
				$worksheet2->write($renglonInicial, 7, "Total Abonos: " . $rAbo['sumA'] . " - Total de Movimientos: " . $rAbo['contA'] . " ");
			}
			
			//Incrementamos el renglon
	        $renglonInicial +=1;
		}
		
		else{
		      //no hacemos nada
		 }	
		 
		//Incrementamos el renglon
	    $renglonInicial +=1;

        //generamos los movimientos de cheques
		//echo $sqlMovimiento="select * from BAemisionCheques ".$_SESSION[ewSessionQuery]." and claveCuenta='".$hb["noCuenta"]."'";	
		$sqlMovimiento="select * from BAemisionCheques where claveCuenta='".$hb["noCuenta"]."' and  `fechaRegistro` BETWEEN '2014/01/01' AND '2014/08/01'";		
		$queryMovimiento=mysql_query($sqlMovimiento);	
		
		$montoTotal=0;
		$montoIva=0;	
		$saldoFi =0;
		
		while($xc=mysql_fetch_array($queryMovimiento)){
			$worksheet->write($renglonInicial, 0, cambiaf_a_normal($xc["fechaRegistro"]), $texto);
			$worksheet->write($renglonInicial, 1, $xc["numCheque"], $texto);
			$worksheet->write($renglonInicial, 2, $xc["nomBeneficiario"], $texto);
			$worksheet->write($renglonInicial, 3, nomcliente($xc["claveCliente"],$conn), $texto);
			$worksheet->write($renglonInicial, 4, $xc["concepto"], $texto);
			$worksheet->write($renglonInicial, 5, $xc["observaciones"], $texto);
			if ($xc["afectaIva"]=='S'){
				$iva=$xc["monto"]*.16;
				$monto=$xc["monto"]-$iva;
				$worksheet->write($renglonInicial, 6, $monto, $f_price);
			    $worksheet->write($renglonInicial, 7, $iva, $f_price);
			}
			else{
				$monto=$xc["monto"];
				$worksheet->write($renglonInicial, 6, $monto, $f_price);
			    $worksheet->write($renglonInicial, 7, "0", $f_price);
			}			
			$worksheet->write($renglonInicial, 8, $xc["multiconcepto"], $texto);
			if($xc["estatus"]=='Ca'){
				$worksheet->write($renglonInicial, 9, 'Si', $texto);	}
			else{
				$worksheet->write($renglonInicial, 9, '', $texto);	}
			
			//Sumamos los montos
			$montoTotal=$montoTotal + $monto;
			$montoIva=$montoIva + $iva;			

		//Incrementamos el renglon
		$renglonInicial +=1;
		
		}//Cerramos el while de cheques
		$saldoFi= $saldo -($montoTotal+ $montoIva);
		//Imprimimos el total de la cta
		$worksheet->write($renglonInicial, 10, number_format($montoIva,2), $f_price);
		$worksheet->write($renglonInicial, 11, number_format($montoTotal,2), $f_price);
		$worksheet->write($renglonInicial, 12, number_format($saldoFi,2), $f_price);
		//Incrementamos el renglon
		$renglonInicial +=1;
		
	}//Cerramos while de cuentas

	//Incrementamos el renglon
	$renglonInicial +=1;
		
}//Cerramos el while de empresas

$workbook->close();

header("Content-Type: application/x-msexcel; name=\"MovimientosTotalesCheques.xls\"");
header("Content-Disposition: inline; filename=\"MovimientosTotalesCheques.xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

//limpiamos Variables
$_SESSION[ewSessionEmpresa] = "";
$_SESSION[ewSessionCuenta] = "";
$_SESSION[ewSessionMes] = "";
$_SESSION[ewSessionAnio] = "";
$_SESSION[ewSessionReporte] = "";