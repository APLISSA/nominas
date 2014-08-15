<?php
session_start();
ob_start();

$_SESSION[ewSessionMes]=7; 
$_SESSION[ewSessionAnio]=2014;

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
$worksheet3 =& $workbook->addworksheet();
$worksheet3->writeexcel_worksheet('Resumen General de Cuentas');
$worksheet =& $workbook->addworksheet();
$worksheet ->writeexcel_worksheet('Movimientos Generales de Bancos (Completo)');
$worksheet2 =& $workbook->addworksheet();
$worksheet2->writeexcel_worksheet('Resumen Desglosado de Cuentas');

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

// Para el formato de la primer hoja

$worksheet3->set_column(0, 0, 60); // Empresa
$worksheet3->set_column(1, 1, 20); // No. Cuenta
$worksheet3->set_column(2, 2, 20); // Banco
$worksheet3->set_column(3, 3, 10); // Num Depositos
$worksheet3->set_column(4, 4, 16); // Monto Depositos
$worksheet3->set_column(5, 5, 10); // Num Retiros
$worksheet3->set_column(6, 6, 16); // Monto Retiros
$worksheet3->set_column(7, 7, 10); // Num Cheques
$worksheet3->set_column(8, 8, 16); // Monto Cheques

$subHA =& $workbook->addformat();
$subHA->set_bold();
$subHA->set_font('Arial');
$subHA->set_size(14);
$subHA->set_align('left');

$subHB =& $workbook->addformat();
$subHB->set_font('Arial');
$subHB->set_size(12);
$subHB->set_text_wrap();
$subHB->set_align('left');

$headerC =& $workbook->addformat();
$headerC->set_font("Arial");
$headerC->set_size(10);
$headerC->set_align('center');
$headerC->set_align('vjustify');
$headerC->set_text_wrap();
$headerC->set_bg_color('gray');
$headerC->set_color('white');
$headerC->set_border_color('white');
$headerC->set_border(3, 3, 3, 3);


$headerD =& $workbook->addformat();
$headerD->set_font("Arial");
$headerD->set_size(10);
$headerD->set_align('left');
$headerD->set_bold();

// Formato para los montos de la hoja 1
$textE =& $workbook->addformat();
$textE->set_font("Arial");
$textE->set_size(8);
$textE->set_align('right');
$textE->set_num_format('[Black]$#,##0.00;[Red]-$#,##0.00;$#,##0.00');
$textE->set_border(1, 1, 1, 1);
$textE->set_bg_color('silver');

// Formato para las cantidades de la hoja 1
$textF =& $workbook->addformat();
$textF->set_font("Arial");
$textF->set_size(8);
$textF->set_align('right');
$textF->set_num_format('[Black]##0;[Red]-##0;##0');
$textF->set_border(1, 1, 1, 1);
$textF->set_bg_color('silver');

// Formato para el texto de cada renglon en la hoja 1
$textG =& $workbook->addformat();
$textG->set_font("Arial");
$textG->set_italic();
$textG->set_bold();
$textG->set_size(9);
$textG->set_align('left');
$textG->set_border(1, 1, 1, 1);
$textG->set_bg_color('silver');

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
$f_price->set_align('right');
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

// Cabecera del Resumen General
$worksheet3->write(1, 0, "Reporte por cada Cuenta de Empresa", $subHA);
$worksheet3->write(2, 0, "Monto desde el primer dia del mes hasta hoy.", $subHB);
//$worksheet3->write(3, 0, "SALDO FINAL AL DIA - Monto total de los movimientos hasta el día actual.", $subHB);

$worksheet3->write(5, 0, "EMPRESA.", $headerC);
$worksheet3->write(5, 1, "Numero de Cuenta", $headerC);
$worksheet3->write(5, 2, "BANCO", $headerC);
$worksheet3->write(5, 3, "NUM\n ABONOS", $headerC);
$worksheet3->write(5, 4, "MONTO\n ABONOS", $headerC);
$worksheet3->write(5, 5, "NUM\n RETIROS", $headerC);
$worksheet3->write(5, 6, "MONTO\n RETIROS", $headerC);
$worksheet3->write(5, 7, "NUM\n CHEQUES", $headerC);
$worksheet3->write(5, 8, "MONTO\n CHEQUES", $headerC);

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

$rengI = 8;
$rengJ = 6;

// Declaramos vacia la variable que es el nombre de la empresa
$tempEmp = "";
$tempNoCuenta = "";
// Declaramos vacias las variables que almacenan los totales de las cuentas.
$rhSI = 0;
$rhFI = 0;
$rhAB = 0;
$rhRE = 0;
$rhCH = 0;

//Generamos el for por empresa
$sqlEmpresa="Select claveEmpresa, nombre from BAempresas where (estatus='Ac' or estatus='Ca') order by  nombre ASC";
$queryEmpresa=mysql_query($sqlEmpresa);

while($fg=mysql_fetch_array($queryEmpresa)){
	//Imprimimos el nombre de la empresa a la que corresponde los mov
	if($fg["nombre"] != $tempEmp){
		$worksheet->write($renglonInicial, 0, 'EMPRESA:'.$fg["nombre"], $header);
		// Escribimos en la tercera hoja
		$worksheet2->write($rengI, 0, 'EMPRESA: '.$fg['nombre'], $header);
		// Escribimos en la hoja del resumen general
		//$worksheet3->write($rengJ, 0, $fg['nombre'], $headerD);
		
		$tempEmp=$fg["nombre"];
		
	}
    else{
	}
	 
	//Incrementamos el renglon
	$renglonInicial +=1;
	
	//Nos traemos las ctas que corresponde a la empresa
	$sqlCuentas  ="Select noCuenta, banco, saldoinicial, tipoCuenta from BAcuentasBancarias where claveEmpresa='".$fg["claveEmpresa"]."' and (estatus='Ac' or estatus='Ca') ORDER BY noCuenta ASC";
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
			$noCuenta=$hb['noCuenta'];
			$tempNoCuenta .= $noCuenta . " - ";
			$nomBanco = $hb['banco'];
			
			// Obtenemos los Saldos de la cuenta.
			//$sqlSI = "SELECT FORMAT(saldoInicial,2) as saldoi FROM BAcuentasBancarias WHERE noCuenta LIKE '" . $hb['noCuenta'] . "' ";
			$sqlSI = "SELECT FORMAT(saldoInicial,2) as saldoi FROM BAcuentasBancarias WHERE noCuenta LIKE '" . $noCuenta . "' ";
			$querySI =mysql_query($sqlSI);
			$worksheet2->write($rengI + 1, 3, "No Cuenta: ");
			$worksheet2->write($rengI + 1, 5, $hb['noCuenta']);
			$worksheet2->write($rengI + 2, 3, "Banco: ");
			$worksheet2->write($rengI + 2, 5, $hb['banco']);
			$worksheet2->write($rengI + 3, 3, "Tipo de cuenta: ");
			$worksheet2->write($rengI + 3, 5, $hb['tipoCuenta']);
			$worksheet2->write($rengI + 4, 3, "Saldo Inicial del Mes: ");
			while($rSI = mysql_fetch_assoc($querySI)) {
				//$worksheet2->write($rengI + 4, 5, "$" . $hb['saldoInicial']);
				$saldoI = $rSI['saldoi'];
				$worksheet2->write($rengI + 4, 5, "$" . $saldoI);
			}
			
			// Obtenemos el saldo hasta el mes anterior y el saldo de los consumos del mes.
			
			//$sqlST = "SELECT FORMAT(SUM(montoMovimiento),2) as ST FROM BAmovimientosCuentas WHERE claveCuenta like '" . $hb['noCuenta'] . "' AND  fechaRegistro < DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day) ";
			$sqlST = "SELECT FORMAT(SUM(montoMovimiento),2) as ST FROM BAmovimientosCuentas WHERE claveCuenta like '" . $noCuenta . "' AND  fechaRegistro < DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day) ";
			$queryST = mysql_query($sqlST);
			while($rST = mysql_fetch_assoc($queryST)) {
				$worksheet2->write($rengI + 5, 3, "Saldo inicial al final del mes anterior:");
				$worksheet2->write($rengI + 5, 5, "$" . $rST['ST']);
			}
			
			// Obtenemos los Movimientos de cargo del mes
			$sqlCargos= "SELECT SUM(montoMovimiento) as sumC, COUNT(montoMovimiento) as contC FROM BAmovimientosCuentas WHERE claveCuenta like '" . $hb['noCuenta'] . "' AND  fechaRegistro between DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day)  AND CURDATE() AND descripcionMov = 1";
			$queryCargos = mysql_query($sqlCargos);
			while($rCar = mysql_fetch_assoc($queryCargos)) {
				$worksheet2->write($rengI + 6, 3, "Total Cargos: ");
				$worksheet2->write($rengI + 6, 4, "$" . $rCar['sumC']);
				$worksheet2->write($rengI + 6, 5, "Total de Movimientos: ");
				$worksheet2->write($rengI + 6, 6, $rCar['contC']);
			}
			
			// Obtenemos los Movimientos de abono del mes
			
			$sqlAbonos= "SELECT SUM(montoMovimiento) as sumA, COUNT(montoMovimiento) as contA FROM BAmovimientosCuentas WHERE claveCuenta like '" . $hb['noCuenta'] . "' AND  fechaRegistro between DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day)  AND CURDATE() AND descripcionMov = 2";
			$queryAbonos = mysql_query($sqlCargos);
			while($rAbo = mysql_fetch_assoc($queryAbonos)) {
				$worksheet2->write($rengI + 7, 3, "Total Abonos: ");
				$worksheet2->write($rengI + 7, 4, "$" . $rAbo['sumA']);
				$worksheet2->write($rengI + 7, 5, "Total de Movimientos: ");
				$worksheet2->write($rengI + 7, 6, $rAbo['contA']);
			}
			
			/* Empezamos a conseguir los concentrados */
			/*
			$sqlSI = "SELECT FORMAT(saldoInicial,2) as saldoi FROM BAcuentasBancarias WHERE noCuenta LIKE '" . $noCuenta . "' ";
			$querySI =mysql_query($sqlSI);
			while($rSI = mysql_fetch_assoc($querySI)) {
				$saldoI = $rSI['saldoi'];
			}
			*/
			
			/*
			$strRep = "SELECT SUM(bamc.montoMovimiento) as DT, COUNT(bamc.montoMovimiento) as CT, CONCAT('query 1') AS CO  from BAmovimientosCuentas AS bamc WHERE claveCuenta LIKE '" . $noCuenta ."' AND (bamc.fechaRegistro < CURDATE() ) AND bamc.descripcionMov = 1 UNION ";
			$strRep .= "SELECT SUM(bamc.montoMovimiento) as DT, COUNT(bamc.montoMovimiento) as CT, CONCAT('query 2') AS CO  from BAmovimientosCuentas AS bamc WHERE claveCuenta LIKE '" . $noCuenta ."' AND (bamc.fechaRegistro < CURDATE() ) AND bamc.descripcionMov = 2 UNION ";
			$strRep .= "SELECT SUM(monto) as DT, COUNT(monto) as CT, CONCAT('query 3') AS CO from BAemisionCheques WHERE claveCuenta LIKE '" . $noCuenta ."' AND (fechaEmision < CURDATE() ) AND estatus LIKE 'Ac'";
			*/
			
			$strRep = "SELECT SUM(bamc.montoMovimiento) as DT, COUNT(bamc.montoMovimiento) as CT, CONCAT('query 1') AS CO  from BAmovimientosCuentas AS bamc WHERE claveCuenta LIKE '" . $noCuenta ."' AND (bamc.fechaRegistro BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day) AND CURDATE() ) AND bamc.descripcionMov = 1 UNION ";
			$strRep .= "SELECT SUM(bamc.montoMovimiento) as DT, COUNT(bamc.montoMovimiento) as CT, CONCAT('query 2') AS CO  from BAmovimientosCuentas AS bamc WHERE claveCuenta LIKE '" . $noCuenta ."' AND (bamc.fechaRegistro BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day) AND CURDATE() ) AND bamc.descripcionMov = 2 UNION ";
			$strRep .= "SELECT SUM(monto) as DT, COUNT(monto) as CT, CONCAT('query 3') AS CO from BAemisionCheques WHERE claveCuenta LIKE '" . $noCuenta ."' AND (fechaEmision BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day) AND CURDATE() ) AND estatus LIKE 'Ac'";
						
			$queryRep = mysql_query($strRep);
			if(!$queryRep) {
				$worksheet3->write(9,0, "No se hizo el query - " . mysql_error());
			}
			
			if(mysql_num_rows($queryRep) == 0) {
				$worksheet3->write(9,0, "No se obtuvieron resultados - " . mysql_error());
			}
			
			while($resRep = mysql_fetch_assoc($queryRep)) {
			
				$valQ = $resRep['CO'];
				$valC = $resRep['CT'];
				$valS = $resRep['DT'];
				switch ($valQ) {
					case 'query 1':
						$rhAB = $valS;
						$intAB = $valC;
						
						break;
					case 'query 2':
						$rhRE = $valS;
						$intRE = $valC;
						
						break;
					case 'query 3':
						$rhCH = $valS;
						$intCH = $valC;

						break;
				}		
			}

			if ( ($rengJ % 2) == 0 ) {
				$textE->set_bg_color('white');
				$textF->set_bg_color('white');
				$textG->set_bg_color('white');
			}
			
			$worksheet3->write($rengJ, 0, $tempEmp, $textG);
			$worksheet3->write($rengJ, 1, " " . $noCuenta, $textG);
			$worksheet3->write($rengJ, 2, $nomBanco, $textG);
			$worksheet3->write($rengJ, 3, $intAB, $textF);
			$worksheet3->write($rengJ, 4, $rhAB, $textE);
			$worksheet3->write($rengJ, 5, $intRE, $textF);
			$worksheet3->write($rengJ, 6, $rhRE, $textE);
			$worksheet3->write($rengJ, 7, $intCH, $textF);
			$worksheet3->write($rengJ, 8, $rhCH, $textE);
			
			$rengJ +=1;
						
			//Incrementamos el renglon
	        $renglonInicial +=1;
	        $rengI += 8;
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
		
		
		$tempEmp="";
	}//Cerramos while de cuentas
	//$tempEmp="";
	//Incrementamos el renglon
	$renglonInicial +=1;
		
}//Cerramos el while de empresas

	
$workbook->close();

mysql_close($conn);

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