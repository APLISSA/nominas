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

$fname = tempnam("/tmp", "ReporteRHA.xls");
$workbook = &new writeexcel_workbook($fname);

$worksheet2 =& $workbook->addworksheet();
$worksheet2->writeexcel_worksheet('Estado de Cuenta por Empresa');

$worksheet3 =& $workbook->addworksheet();
$worksheet3->writeexcel_worksheet('Resumen General de Cuentas');

//Seleccionamos la fila q se queda fija
//$worksheet->freeze_panes(8, 0); # 1 row

//Altura de las columnas donde van los titulos
$worksheet3->set_row(0, 8);
$worksheet2->set_row(0, 8);

// Agregamos formatos para los textos.

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

// Formato para los montos
$textE =& $workbook->addformat();
$textE->set_font("Arial");
$textE->set_size(8);
$textE->set_align('right');
$textE->set_num_format('[Black]$#,##0.00;[Red]-$#,##0.00;$#,##0.00');
$textE->set_border(1, 1, 1, 1);
$textE->set_bg_color('silver');

// Formato para las cantidades
$textF =& $workbook->addformat();
$textF->set_font("Arial");
$textF->set_size(8);
$textF->set_align('right');
$textF->set_num_format('[Black]##0;[Red]-##0;##0');
$textF->set_border(1, 1, 1, 1);
$textF->set_bg_color('silver');

// Formato para el texto de cada renglon
$textG =& $workbook->addformat();
$textG->set_font("Arial");
$textG->set_italic();
$textG->set_bold();
$textG->set_size(9);
$textG->set_align('left');
$textG->set_border(1, 1, 1, 1);
$textG->set_bg_color('silver');

// Para el formato de las hojas

$worksheet3->set_column(0, 0, 60); // Empresa
$worksheet3->set_column(1, 1, 20); // No. Cuenta
$worksheet3->set_column(2, 2, 20); // Banco
$worksheet3->set_column(3, 3, 20); // Saldo Total
//$worksheet3->set_column(4, 4, 20); // Saldo Inicial
$worksheet3->set_column(4, 4, 3); // Saldo Inicial
$worksheet3->set_column(5, 5, 10); // Num Depositos
$worksheet3->set_column(6, 6, 16); // Monto Depositos
$worksheet3->set_column(7, 7, 10); // Num Retiros
$worksheet3->set_column(8, 8, 16); // Monto Retiros
$worksheet3->set_column(9, 9, 10); // Num Cheques
$worksheet3->set_column(10, 10, 16); // Monto Cheques

$worksheet2->set_column(0, 0, 60); // Nombre de la Empresa
$worksheet2->set_column(1, 1, 20); // Saldo

// Cabecera del Resumen General
$worksheet3->write(1, 0, "Reporte por cada Cuenta de Empresa", $subHA);
$worksheet3->write(2, 0, "Monto desde el primer dia del mes hasta hoy.", $subHB);

$worksheet2->write(1, 0, "Estado de Cuenta por Empresa", $subHA);
$worksheet2->write(2, 0, "Saldo al dia: " . date('d-m-Y'), $subHB);

//$worksheet3->write(3, 0, "SALDO FINAL AL DIA - Monto total de los movimientos hasta el día actual.", $subHB);

$worksheet3->write(5, 0, "EMPRESA.", $headerC);
$worksheet3->write(5, 1, "Numero de Cuenta", $headerC);
$worksheet3->write(5, 2, "BANCO", $headerC);
$worksheet3->write(5, 3, "SALDO", $headerC);
//$worksheet3->write(5, 4, "SALDO\n INICIAL", $headerC);
$worksheet3->write(5, 4, "", $headerC);
$worksheet3->write(5, 5, "NUM\n ABONOS", $headerC);
$worksheet3->write(5, 6, "MONTO\n ABONOS", $headerC);
$worksheet3->write(5, 7, "NUM\n RETIROS", $headerC);
$worksheet3->write(5, 8, "MONTO\n RETIROS", $headerC);
$worksheet3->write(5, 9, "NUM\n CHEQUES", $headerC);
$worksheet3->write(5, 10, "MONTO\n CHEQUES", $headerC);

$worksheet2->write(5, 0, "EMPRESA.", $headerC);
$worksheet2->write(5, 1, "SALDO", $headerC);

$renglonInicial=8;//Indica el renglon donde se inicia la impresion de los datos grales del empleado

$rengJ = 6;
$rengI = 6;

// Declaramos vacia la variable que es el nombre de la empresa
$tempEmp = "";
$tempNoCuenta = "";
// Declaramos vacias las variables que almacenan los totales de las cuentas.
$rhSI = 0; // Monto Saldo Inicial
$rhFI = 0; // Monto Final
$rhAB = 0; // Monto Abonos
$rhRE = 0; // Monto Retiros
$rhCH = 0; // Monto Cheques
$rhABF = 0; // Monto Abonos TODOS
$rhREF = 0; // Monto Retiros TODOS
$rhCHF = 0; // Monto Cheques TODOS
$rhS = 0; // Saldo Total

//Generamos el for por empresa
$sqlEmpresa="Select claveEmpresa, nombre from BAempresas where (estatus='Ac' or estatus='Ca') order by  nombre ASC";
$queryEmpresa=mysql_query($sqlEmpresa);

while($fg=mysql_fetch_array($queryEmpresa)){
	//Imprimimos el nombre de la empresa a la que corresponde los mov
	if($fg["nombre"] != $tempEmp){
		$tempEmp=$fg["nombre"];
	}
    else{
	}
	
	//$rhFI = 0;
	
	//Nos traemos las ctas que corresponde a la empresa
	$sqlCuentas  ="Select noCuenta, banco, saldoinicial, tipoCuenta from BAcuentasBancarias where claveEmpresa='".$fg["claveEmpresa"]."' and (estatus='Ac' or estatus='Ca') ORDER BY noCuenta ASC";
	$queryCuentas=mysql_query($sqlCuentas);
	$montoEmpresa=0;
	
	while($hb=mysql_fetch_array($queryCuentas)){
		//Imprimimos el nombre de la cuenta
		if($hb["noCuenta"] != $tempCta){	
            
			$tempCta=$hb["noCuenta"];
			$noCuenta=$hb['noCuenta'];
			$tempNoCuenta .= $noCuenta . " - ";
			$nomBanco = $hb['banco'];
			
			$rhSI = $hb['saldoinicial'];
			
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
			
			$strRep = "SELECT SUM(bamc.montoMovimiento) as DT, COUNT(bamc.montoMovimiento) as CT, CONCAT('query 1') AS CO  from BAmovimientosCuentas AS bamc WHERE claveCuenta LIKE '" . $noCuenta ."' AND (bamc.fechaRegistro BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day) AND CURDATE() ) AND bamc.descripcionMov = 1 AND estatus LIKE 'Ac' UNION ";
			$strRep .= "SELECT SUM(bamc.montoMovimiento) as DT, COUNT(bamc.montoMovimiento) as CT, CONCAT('query 2') AS CO  from BAmovimientosCuentas AS bamc WHERE claveCuenta LIKE '" . $noCuenta ."' AND (bamc.fechaRegistro BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day) AND CURDATE() ) AND bamc.descripcionMov = 2 AND estatus LIKE 'Ac' UNION ";
			$strRep .= "SELECT SUM(monto) as DT, COUNT(monto) as CT, CONCAT('query 3') AS CO from BAemisionCheques WHERE claveCuenta LIKE '" . $noCuenta ."' AND (fechaEmision BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL(1-DAYOFMonth(CURRENT_DATE)) day) AND CURDATE() ) AND estatus LIKE 'Ac' UNION ";
			$strRep .= "SELECT SUM(bamc.montoMovimiento) as DT, COUNT(bamc.montoMovimiento) as CT, CONCAT('query 4') AS CO  from BAmovimientosCuentas AS bamc WHERE claveCuenta LIKE '" . $noCuenta ."' AND bamc.descripcionMov = 1 AND estatus LIKE 'Ac' UNION ";
			$strRep .= "SELECT SUM(bamc.montoMovimiento) as DT, COUNT(bamc.montoMovimiento) as CT, CONCAT('query 5') AS CO  from BAmovimientosCuentas AS bamc WHERE claveCuenta LIKE '" . $noCuenta ."' AND bamc.descripcionMov = 2  AND estatus LIKE 'Ac' UNION ";
			$strRep .= "SELECT SUM(monto) as DT, COUNT(monto) as CT, CONCAT('query 6') AS CO from BAemisionCheques WHERE claveCuenta LIKE '" . $noCuenta ."'  AND estatus LIKE 'Ac'";
						
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
					
					case 'query 4':
						$rhABF = $valS;
						//$intCH = $valC;
						break;
						
					case 'query 5':
						$rhREF = $valS;
						//$intCH = $valC;
						break;
						
					case 'query 6':
						$rhCHF = $valS;
						//$intCH = $valC;

						break;
				}		
			}

			if ( ($rengJ % 2) == 0 ) {
				$textE->set_bg_color('white');
				$textF->set_bg_color('white');
				$textG->set_bg_color('white');
			}
			
			// Hacemos calculos de Totales
			//$rhFI = $rhFI + $rhAB - $rhRE - $rhCH;
			$rhFI = $rhFI + $rhSI + $rhABF - $rhREF - $rhCHF;
			
			$rhS = $rhSI + $rhABF - $rhREF - $rhCHF;
			
			$worksheet3->write($rengJ, 0, $tempEmp, $textG);
			$worksheet3->write($rengJ, 1, " " . $noCuenta, $textG);
			$worksheet3->write($rengJ, 2, $nomBanco, $textG);
			$worksheet3->write($rengJ, 3, $rhS, $textE);
			//$worksheet3->write($rengJ, 4, $rhSI, $textE);
			$worksheet3->write($rengJ, 5, $intAB, $textF);
			$worksheet3->write($rengJ, 6, $rhAB, $textE);
			$worksheet3->write($rengJ, 7, $intRE, $textF);
			$worksheet3->write($rengJ, 8, $rhRE, $textE);
			$worksheet3->write($rengJ, 9, $intCH, $textF);
			$worksheet3->write($rengJ, 10, $rhCH, $textE);
			
			//Incrementamos el renglon
			$rengJ +=1;
		}
		
		else{
		      //no hacemos nada
		 }	

		
	}//Cerramos while de cuentas
	//$tempEmp="";
	
	$worksheet2->write($rengI, 0, $tempEmp, $textG);
	$worksheet2->write($rengI, 1, $rhFI, $textE);
	
	$rengI = $rengI + 2;
	
	$tempEmp="";
	$rhFI = 0;
	
}//Cerramos el while de empresas

	
$workbook->close();

mysql_close($conn);

header("Content-Type: application/x-msexcel; name=\"ReporteRHA.xls\"");
header("Content-Disposition: inline; filename=\"ReporteRHA.xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

//limpiamos Variables
$_SESSION[ewSessionEmpresa] = "";
$_SESSION[ewSessionCuenta] = "";
$_SESSION[ewSessionMes] = "";
$_SESSION[ewSessionAnio] = "";
$_SESSION[ewSessionReporte] = "";