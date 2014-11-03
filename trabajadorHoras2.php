        <?php  
        	session_start();
	ob_start();
	
include("cabeceraAdministrador2.php");

$conn = phpmkr_db_connect(HOST, USER, PASS, DB, PORT);

		?>
<!--<script type="text/javascript" src="ingreso_sin_recargar.js"></script>-->
<script type="text/javascript">

$.extend( $.fn.dataTable.defaults, {
    //"searching": false,
    //"ordering": false,
	"order": [[ 1, "asc" ]],
	"pageLength": 40
} );

$(document).ready(function() { 
    $("table#MiTablita").dataTable({
		//"pageLength": 40,
		"language": {
			"sProcessing":     "Procesando...",
			"sLengthMenu":     "Mostrar _MENU_ registros",
			"sZeroRecords":    "No se encontraron resultados",
			"sEmptyTable":     "Ningún dato disponible en esta tabla",
			"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
			"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
			"sInfoPostFix":    "",
			"sSearch":         "Buscar:",
			"sUrl":            "",
			"sInfoThousands":  ",",
			"sLoadingRecords": "Cargando...",
			"oPaginate": {
				"sFirst":    "Primero",
				"sLast":     "Último",
				"sNext":     "Siguiente",
				"sPrevious": "Anterior"
				},
			"oAria": {
				"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
				"sSortDescending": ": Activar para ordenar la columna de manera descendente"
				}
			}
		});
}); 
</script>

<style type="text/css">
div#abajo 
{
	bottom: 0;
	margin-left: 400px;
}

div#titulos
{
  margin-left: 150px;
}
</style>

<!--<div id="main">
<div id="titulos">-->
<br />
<br />
<br />
<p class="tituloPrinRep">Catalogo de Trabajadores</p>

<br />
<div>
<div class="container">
	<div class="row">
	<div class=" col-md-10 col-md-offset-1">
		<table cellspacing="1" border='1' class="table table-bordered table-striped table-condensed" id="MiTablita">
        <thead>
			<tr class="info" style="font-size:14; font-weight: bold;">
				<th class="header">Clave <span class="glyphicon glyphicon-sort"><span></th>
				<th class="header">Nombre Completo <span class="glyphicon glyphicon-sort"><span></th>
				<th class="header">H.E. Dobles <span class="glyphicon glyphicon-sort"><span></th>
				<th class="header">H.E. Triples <span class="glyphicon glyphicon-sort"><span></th>
				<th class="header">Tot. Horas <span class="glyphicon glyphicon-sort"><span></th>
				<th class="header">Limpiar<br />registro<br />horas <span class="glyphicon glyphicon-sort"><span></th>
			</tr>
		</thead>
        <!--<tfoot><tr>
		  <th>Clave</th>
          <th>Nombre Completo</th>
          <th>H.E. Dobles</th>
          <th>H.E. Triples</th>
          <th>T. Horas</th>
		  <th>Limpiar registro horas</th> 
        </tr></tfoot>-->
        <tbody>
          <?php
          		$sql  = " SELECT T.nombre, T.paterno, T.materno, T.clave_trabajador FROM trabajador as T ";
				$sql .= " INNER JOIN trabajador_laboral as TL where T.clave_empresa='".$_SESSION[ewSessionEmpresa]."' and ";
				$sql .= " T.clave_cliente='".$_SESSION[ewSessionCliente]."' and T.clave_empresa=TL.clave_empresa";
				$sql .= " and T.clave_cliente=TL.clave_cliente and T.clave_trabajador=TL.clave_trabajador and ( T.estatus= 'A' "; 
				$sql .= " or T.estatus='In' or T.estatus='Va') and (TL.fecha_baja = '' or TL.fecha_baja   ";
				$sql .= " like '%".$_SESSION[ewSessionAnioEjercicio]."') order by T.paterno, T.materno  ";
				$res=mysql_query($sql);
				//$d=0;
		
				while($registro=mysql_fetch_array($res)){  
					$clave_trabajador = $registro[3];
					$nombre = $registro[1].' '.$registro[2].' '.$registro[0];
					$nombre = strtoupper($nombre);
					
					//$gb=$id.'/'.$horas_doble.'/'.$horas_triples.'/'.$total.'/'.$tipo;
					$arreglo = horasExtraPorEmp($clave_trabajador);
					
					//separamos variables
					list($id, $horas_doble, $horas_triples, $total, $tipo)=explode('/', $arreglo);
          ?>
          <tr id='<?php echo $clave_trabajador; ?>' class='edit_tr'>
		  <!--<td class='listadoInformacion'><?php //echo $clave_trabajador; ?></td>-->
		  <td style="font-size:11;"><?php echo $clave_trabajador; ?></td>
          <td style="font-size:11;"><?php echo $nombre; ?> </td>
          <td class='edit_td'  style="font-size:11;">
			<span id="dobles_<?php echo $clave_trabajador; ?>" class="text"><?php echo $horas_doble; ?></span>
			<input type="text" value="<?php echo $horas_doble; ?>" class="editbox" id="dobles_input_<?php echo $clave_trabajador; ?>" />
		  </td>
          <td class='edit_td'  style="font-size:11;">
			<span id="triples_<?php echo $clave_trabajador; ?>" class="text"><?php echo $horas_triples; ?></span>
			<input type="text" value="<?php echo $horas_triples; ?>" class="editbox" id="triples_input_<?php echo $clave_trabajador; ?>" />
		  </td>
          <td id="total_<?php echo $clave_trabajador; ?>"  style="font-size:11;"><?php echo $total; ?></td>
          <td style="font-size:11;">
			<a href="#" class="btn btn-sm btn-danger" role="button" onClick='return false;'><span class="glyphicon glyphicon-trash white"></span></a>
		  </td>
          </tr>
          <?php
          		}
          ?>
          
		</tbody>
    </table>
	</div>
	</div>
</div>

<?php 

mysql_close($conn);

include("pie.php"); 

function horasExtraPorEmp($clave_trabajador){

//Traemos la conexion de la base de datos
$connPP = phpmkr_db_connect(HOST, USER, PASS, DB, PORT); 

$sqlHoras  = "Select id_hora, horas_dobles, horas_triples, total_horas, tipo from horas_extra where clave_trabajador='".$clave_trabajador."' and clave_empresa='".$_SESSION[ewSessionEmpresa]."' ";
$sqlHoras .= "and clave_cliente='".$_SESSION[ewSessionCliente]."' and clave_periodo='".$_SESSION[ewSessionPeriodo]."'";
		   
$queryHoras = mysql_query($sqlHoras);
$nomRegistro=mysql_num_rows($queryHoras);
		   
if($nomRegistro>0){
	while($dr=mysql_fetch_array($queryHoras)){
			$horas_doble=$dr[1];
			$horas_triples=$dr[2];
			$total=$dr[3];
			$id=$dr[0];
			$tipo=$dr[4];
	}
}

$gb=$id.'/'.$horas_doble.'/'.$horas_triples.'/'.$total.'/'.$tipo;

mysql_close($connPP);

return $gb;
 
}
?>
		