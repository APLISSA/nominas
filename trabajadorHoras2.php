        <?php  
        	session_start();
	ob_start();
	
include("cabeceraAdministrador2.php");

$conn = phpmkr_db_connect(HOST, USER, PASS, DB, PORT);

		?>
<!--<script type="text/javascript" src="ingreso_sin_recargar.js"></script>-->
<script type="text/javascript">
$(document).ready(function() { 
    $("table#MiTablita")
//    .tablesorter({widthFixed: true, widgets: ['zebra']}) 
	.tablesorter() 
    //.tablesorterPager({container: $("#pager")}); 
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

<div id="main">
<div id="titulos">
 <div align="center"><br>
      <p class="tituloPrinRep">Catalogo de Trabajadores<br>
		
		<table cellspacing="1" border='1' class="table table-bordered table-striped tablesorter" id="MiTablita">
        <thead>
			<tr class="info">
				<th class="header">Clave</th>
				<th class="header">Nombre Completo</th>
				<th class="header">H.E. Dobles</th>
				<th class="header">H.E. Triples</th>
				<th class="header">Tot. Horas</th>
				<th class="header">Limpiar registro horas</th>
			</tr>
		</thead>
        <tfoot><tr>
		  <th>Clave</th>
          <th>Nombre Completo</th>
          <th>H.E. Dobles</th>
          <th>H.E. Triples</th>
          <th>T. Horas</th>
		  <th>Limpiar registro horas</th> 
        </tr></tfoot>
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
		  <td class='listadoInformacion'><?php echo $clave_trabajador; ?></td>
          <td class='listadoInformacion'><?php echo $nombre; ?> </td>
          <td class='edit_td'>
			<span id="dobles_<?php echo $clave_trabajador; ?>" class="text"><?php echo $horas_doble; ?></span>
			<input type="text" value="<?php echo $horas_doble; ?>" class="editbox" id="dobles_input_<?php echo $clave_trabajador; ?>" />
		  </td>
          <td class='edit_td'>
			<span id="triples_<?php echo $clave_trabajador; ?>" class="text"><?php echo $horas_triples; ?></span>
			<input type="text" value="<?php echo $horas_triples; ?>" class="editbox" id="triples_input_<?php echo $clave_trabajador; ?>" />
		  </td>
          <td class='listadoInformacion' id="total_<?php echo $clave_trabajador; ?>"><?php echo $total; ?></td>
          <td>
			<a href="#" class="btn btn-danger" role="button" onClick='return false;'><span class="glyphicon glyphicon-trash"></span></a>
		  </td>
          </tr>
          <?php
              //$d += 1;
          		}
          ?>
          
		</tbody>
    </table>
	
  </div>
</div>
</div>
<div id="abajo">
<!--
<div id="pager" class="pager">
	    <form>
		<img src="image/first.png" class="first"/>
		<img src="image/prev.png" class="prev"/>
		<input type="text" class="pagedisplay"/>
		<img src="image/next.png" class="next"/>
		<img src="image/last.png" class="last"/>
		<select class="pagesize" id="pager">
			<option selected="selected" value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option value="40">40</option>
		</select>
	</form>
   </div>-->
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
		