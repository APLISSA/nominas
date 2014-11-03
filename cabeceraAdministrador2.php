<?php session_start();
	ob_start();
	
    include("db/db.php");
	include ("db/advsecu.php");
	include("include/claseRecordset.inc.php"); 
	include("include/conexion.inc.php");
	
	require_once('FirePHPCore/FirePHP.class.php');
	
	//funcion en php que devuelve el mes en espa�ol
	setlocale("LC_TIME", "es_ES"); 
	//setlocale("LC_ALL", "es_ES"); 
	
	if (!IsLoggedIn()) {
		ob_end_clean();
		header("Location: login.php");
		exit();
	}

	header("Expires: 0");
	header ("Cache-Control: no-cache, must-revalidate"); //no guardar en CACHE
	header("Content-Type: text/html;charset=utf-8");
	header ("Pragma: no-cache"); 
	header('Content-Type: text/html; charset=ISO-8859-1');
?>
<html>
<head>
<meta name="description" content="">
<meta name="author" content="">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=0" />
<title> **** ADN Administracion Dinamicas de Nominas ****</title>

<!-- Estilos -->
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<link type="image/x-icon" href="favicon.ico" rel="shortcut icon" />
<link href="js/themes/blue/style.css" type="text/css" rel="stylesheet"/>
<link href="style/estilosNominas.css" rel="stylesheet" type="text/css" />
<link href="css/jquery.tablesorter.pager2.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" media="all" href="style/calendar-tas.css" title="win2k-cold-1" />
<link rel="stylesheet" href="css/styleIconos.css" type="text/css" charset="utf-8" />
<!-- aqui aplicamos el estil a todas las formas y que esto se debe hacer pero se debera corregir en todas las formas el como se ven los campos. -->
<link rel="stylesheet" href="css/bootstrap3.css" />

<!-- Estilos -->

<!-- Librerias de JQuery -->
<script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
<!-- <script type="text/javascript" src="js/jquery-1.7.2.js"></script>
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script> -->
<script type="text/javascript" src="js/bootstrap-typeahead.js"></script>
<script type="text/javascript" src="js/bootstrap3.js"></script>
<!-- <script type="text/javascript" src="js/jquery-latest.js"></script> -->
<script type="text/javascript" src="js/jquery.tablesorter2.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.pager2.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>

<!-- Librerias de JQuery -->
<script type="text/javascript" src="wForms.js"></script>
<script type="text/javascript" src="js/fecha.js"></script>
<script type="text/javascript" src="js/cookies.js"></script>
<script type="text/javascript" src="js/ewp.js"></script>
<script type="text/javascript" src="js/calendar.js"></script>
<script type="text/javascript" src="js/calendar-es.js"></script>
<script type="text/javascript" src="js/calendar-setup.js"></script>
<script type="text/javascript" src="js/menuMundet.js"></script>
<script type="text/javascript" src="js/menu.js"></script>
<script type="text/javascript" src="js/bloques.js"></script>
<script type="text/javascript" src="js/validacionNominaPeriodo.js"></script>
<script type="text/javascript" src="js/validacionesActivarEmpleado.js"> </script>
<script type="text/javascript" language="javascript" src="js/ventana.js"></script>
<script language="JavaScript" type="text/JavaScript">

$(document).ready(function() {
	$('#contenido_a_mostrar').hide();
	
	javascript:window.history.forward(1);
	
	//Funcion para el calendario
	$("#fecha_inicio").datepicker(); 
	$("#fecha_fin").datepicker();
	
	$('.edit_tr').click(function(){
		var ID = $(this).attr('id');
		$('#dobles_'+ID).hide();
		$('#triples_'+ID).hide();
		$('#dobles_input_'+ID).show();
		$('#triples_input_'+ID).show();
	}).change(function(){
		var ID = $(this).attr('id');
		var dobles=$('#dobles_input_'+ID).val();
		var triples=$('#triples_input_'+ID).val();
		var total= parseInt(dobles) + parseInt(triples);
		var dataString = 'id=' + ID + '&dobles=' + dobles + '&triples=' + triples + '&totales=' + total;
		alert(dataString);
		
		// Solo para depurar.
		$('#dobles_'+ID).html(dobles);
		$('#triples_'+ID).html(triples);
		$('#total_'+ID).html(total);
		
		// Verificamos que no esten vacias las variables para hacer el update
		/*if ( dobles.length>0 && triples.length>0 ) {
	
			$.ajax({
				type	:	"POST",
				url		:	"ingresaHorasE.php",
				data	:	{ dhe : dobles, the	: triples },
				cache	:	false
			})
			.done(function(html){
				$('#dobles_'+ID).html(dobles);
				$('#triples_'+ID).html(triples);
			});
		} else {
			alert('Ingrese valores a los campos');
		} */
	
	});
	
	
	
	$('.editbox').mouseup(function() {
		return false;
	});
	
	$(document).mouseup(function() {
		$('.editbox').hide();
		$('.text').show();
	});
	
	
});

//Validamos la longitud de los campos
/*function validarLongitud(campo, longitud){
 //var longitud=13;
 if (campo.value.length < longitud) {
      $().toastmessage('showErrorToast', "La longitud valida del campo "+ campo.name +" es de "+longitud+" caracteres");
	  campo.focus();
	  return false;
    }
 else{
 	   return true;
 }
}*/ 

//Validaciones
wFORMS.behaviors['validation'].errMsg_custom = "Validaciones Sistema de Nominas";
wFORMS.behaviors['validation'].errMsg_required = " **** Error **** : Este campo es obligatorio";
wFORMS.behaviors['validation'].errMsg_alphanum = " **** Error **** : Solo se pueden utilizar caracteres alfanumericos [a-z 0-9]";
wFORMS.behaviors['validation'].errMsg_float = " **** Error **** : El texto introducido tiene que ser un n˙mero decimal. Por ejemplo: 9,5";
wFORMS.behaviors['validation'].errMsg_notification = "%% error(s) detectado(s). El formulario no se enviar·.  Por favor, checa la informaciÛn suministrada.";
wFORMS.behaviors['validation'].errMsg_integer = " **** Error **** : El texto introducido tiene que ser un n˙mero entero";
wFORMS.behaviors['validation'].errMsg_email = " **** Error **** : El email introducido no tiene un formato valido";
wFORMS.behaviors['validation'].errMsg_alpha = "**** Error **** : El texto introducido tiene caracteres diferentes a letras";
wFORMS.behaviors['validation'].isAlpha = function(s) {
  var reg = /^[\u0041-\u007A\u00C0-\u00FF\u0100–\u017F]+$/;
  return this.isEmpty(s) || reg.test(s);
}

// user configuration of all toastmessages to come:
$().toastmessage({
    text     : 'Sistema de Nominas',
    sticky   : true,
    position : 'middle-center',
    type     : 'error',
    closeText: '',
    close    : function () {
        console.log("toast is closed ...");}
});

/*function muestra_oculta(id){
if (document.getElementById){ //se obtiene el id
var el = document.getElementById(id); //se define la variable "el" igual a nuestro div
el.style.display = (el.style.display == 'none') ? 'block' : 'none'; //damos un atributo display:none que oculta el div
}
}*/
/*window.onload = function(){ //hace que se cargue la funci�n lo que predetermina que div estar� oculto hasta llamar a la funci�n nuevamente
muestra_oculta('contenido_a_mostrar');// "contenido_a_mostrar" es el nombre que le dimos al DIV 
}*/

//javascript:window.history.forward(1);
//-->

//var tipoNomina =<?php echo $_SESSION[ewSessionTipoNomina]; ?>;

//Funcion para el calendario
  /*$(document).ready(function() {
    $("#fecha_inicio").datepicker(); 
	$("#fecha_fin").datepicker();
  });*/


</script>

<!-- Librerias para mandar llamar el nuevo formato de los messages box del sitios -->
<link type="text/css" href="css/jquery.toastmessage-min.css" rel="stylesheet"/>
<script type="text/javascript" src="jquery.toastmessage-min.js"></script>

<style type="text/css">
/* estilos para los errores */
  .errFld {
  			border: 1px solid #F00; 
  			background: #FF6347;}
  .errMsg { 
  		color: #C33;
  		font-style:italic;
  		font-weight:bold;
  		font-size:1.3em;
  		font-family:'Helvetica','Verdana','Monaco',sans-serif;
  		 }
		 
	.editbox {
		display	:	none;
	}
</style>
</head>

<body style="font-size:62.5%;">
<div>
<table width="1039" height="112" border="0" align="center" cellpadding="0" cellspacing="0" id="TablaCabecera">

<tr><td width="173" height="39"><img src="image/plantilla_01.jpg" width="173" height="39" alt=""></td>
	<td colspan="2"><img src="image/plantilla_02.jpg" width="467" height="39" alt=""></td>
	<td colspan="5"><img src="image/plantilla_03.jpg" width="357" height="39" alt=""></td></tr>

<tr align="center"><td><img src="image/plantilla_04.jpg" width="173" height="73" alt=""></td>
	<td colspan="2"><img src="image/plantilla_05.jpg" width="467" height="73" alt=""></td>
	<td width="58"><a href="menuAdministrador.php">
    <img src="image/plantilla_06.jpg" name="Image22" width="58" height="73" border="0" title="Regresar Pagina Principal"></a></td>
	<td width="68"><a href="seleccion_empresa.php">
    <img title="Cambio de Empresa"  src="image/plantilla_07.jpg" name="Image23" width="68" height="73" border="0" ></a></td>
	<td width="66"><a href="javascript:print();">
    <img title="Imprimir" src="image/plantilla_08.jpg" name="Image24" width="66" height="73" border="0"></a></td>
	<td width="73"><a href="manual_ayuda.php" target="_blank">
    <img title="Ayuda" src="image/plantilla_09.jpg" name="Image25" width="73" height="73" border="0"></a></td>
	<td width="100"><a href="logout.php?id='B'">
    <img src="image/plantilla_10.jpg" name="Image26" width="92" height="73" border="0" title="Cerrar Sesion"></a></td></tr>

<tr class="listadoInformacion" bgcolor="#E6E6E4">
	<td colspan="2"><div align="left" class="textoReporte"><strong>Operador:</strong>
	 <?php echo $_SESSION[nombreUserSystem]."&nbsp;".$_SESSION[ap_paterno]."&nbsp;".$_SESSION[ap_materno]; ?><br>
     <strong>Fecha :</strong> <?php echo strftime("%A %d de %B del %Y");?></div></td>
	<td colspan="3"><strong>Empresa:</strong> <?php echo $_SESSION[ewSessionNomEmpresa]; ?>
	<br><strong>Cliente:</strong> <?php echo $_SESSION[ewSessionNomCliente]; ?></td>
	<td colspan="3"><strong>Periodo</strong>: 
	<?php echo "Del: ".$_SESSION[ewSessionNominaInicio]." Al: ".$_SESSION[ewSessionNominaFin].""; ?>
    <?php if ($_SESSION[tipo] == 'A' or $_SESSION[tipo] == 'O' or $_SESSION[tipoPerfilUser] == 'A' or $_SESSION[tipoPerfilUser] == 'O' ) { ?>
      	<br><strong>Tipo</strong>:  <?php echo $_SESSION[ewSessionTipoNominaCliente]; ?>
<?php } ?></td></tr>
<tr align="center" valign="top"><td colspan="8" align="center"><p><p><p><p>
  <?php 
  //Validamos tipo de3 nomina
   if (($_SESSION[ewSessionTipoNomina] == 'H') or ($_SESSION[ewSessionTipoNomina] == 'Co')){
	   if ($_SESSION[tipo] == 'A' or $_SESSION[tipoPerfilUser] == 'A' ) { ?>
	   	<script language="JavaScript" src="js/menuAdmonAsim-Hon-Com.js"></script>
  <?php  } elseif ($_SESSION[tipo] == 'O' or $_SESSION[tipoPerfilUser] == 'O'){ ?> 
  		<script language="JavaScript" src="js/menuOperadorAsim-Hon-Com.js"></script>
  <?php  } elseif ($_SESSION[tipo] == 'OL' or $_SESSION[tipoPerfilUser] == 'OL'){ ?> 
  		<script language="JavaScript" src="js/menuOpcionesAsim-Hon-Com.js"></script>
  <?php } else { //No hacmos nada
		}
   }elseif(($_SESSION[ewSessionTipoNomina] == 'S')){ 
      if ($_SESSION[tipo] == 'A' or $_SESSION[tipoPerfilUser] == 'A') { ?>
   		<script language="JavaScript" src="js/menuAdmonAsociados.js"></script>
   		<?php  } elseif ($_SESSION[tipo] == 'O' or $_SESSION[tipoPerfilUser] == 'O'){ ?> 
			<script language="JavaScript" src="js/menuOperadorAsociados.js"></script>
  		<?php  } elseif ($_SESSION[tipo] == 'OL' or $_SESSION[tipoPerfilUser] == 'OL'){ ?> 
			<script language="JavaScript" src="js/menuOpcionesAsociados.js"></script>
  		<?php } else { //No hacmos nada
		}
   } 
   //Nomina asimilados
    elseif (($_SESSION[ewSessionTipoNomina] == 'A')){
	   if ($_SESSION[tipo] == 'A' or $_SESSION[tipoPerfilUser] == 'A' ) { ?>
	   	<script language="JavaScript" src="js/menuAdmonAsimilados.js"></script>
  <?php  } elseif ($_SESSION[tipo] == 'O' or $_SESSION[tipoPerfilUser] == 'O'){ ?> 
  		<script language="JavaScript" src="js/menuAdmonAsimilados.js"></script>
  <?php  } elseif ($_SESSION[tipo] == 'OL' or $_SESSION[tipoPerfilUser] == 'OL'){ ?> 
  		<script language="JavaScript" src="js/menuAdmonAsimilados.js"></script>
  <?php } else { //No hacmos nada
		}
   }
   
   else{
   if ($_SESSION[tipo] == 'A' or $_SESSION[tipoPerfilUser] == 'A') { ?>
   		<script language="JavaScript" src="js/menuAdmon.js"></script>
  <?php  } elseif ($_SESSION[tipo] == 'O' or $_SESSION[tipoPerfilUser] == 'O'){ ?> 
    		<script language="JavaScript" src="js/menuOperador.js"></script>
  <?php  } elseif ($_SESSION[tipo] == 'OL' or $_SESSION[tipoPerfilUser] == 'OL'){ ?> 
   		<script language="JavaScript" src="js/menuOpcionesLector.js"></script>
  <?php } else { //No hacmos nada
		}
   } ?>
</td></tr></table></div>