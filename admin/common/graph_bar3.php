<?php /* $Id: graph_pie.php 6816 2008-09-19 18:33:18Z p_lindheimer $ */
include_once(dirname(__FILE__) . "/../cdr/lib/defines.php");
include_once(dirname(__FILE__) . "/../cdr/lib/Class.Table.php");
include_once(dirname(__FILE__) . "/../cdr/jpgraph_lib/jpgraph.php");
include_once(dirname(__FILE__) . "/../cdr/jpgraph_lib/jpgraph_bar.php");


getpost_ifset(array('months_compare', 'fromstatsmonth_sday', 'srctype', 'src'));
$months = Array ( 0 => 'Enero', 1 => 'Febrero', 2 => 'Marzo', 3 => 'Abril', 4 => 'Mayo', 5 => 'Junio', 6 => 'Julio', 7 => 'Agosto', 8 => 'Septiembre', 9 => 'Octubre', 10 => 'Noviembre', 11 => 'Diciembre' );

$mesnum = intval(substr($fromstatsmonth_sday,-2));
$mes = $months[$mesnum-1];
$anho = substr($fromstatsmonth_sday,0,4);
$db = 'asteriskcdrdb';
$dbuser = 'freepbx';
$dbpass = 'fpbx';
$dbhost = 'localhost';
$link4 = mysql_connect($dbhost,$dbuser,$dbpass);
mysql_select_db("$db", $link4);
$contenido = mysql_query("select distinct negocio from cdr where negocio != '' and calldate rlike '$fromstatsmonth_sday'", $link4);
while ($nego = mysql_fetch_array($contenido)){
$mylegend = $nego["negocio"];
$sql = "select sum(duration),sum(billsec) from cdr where negocio='$mylegend' and calldate rlike '$fromstatsmonth_sday'";
if ($src!='')
$sql .= " and src='$src'";
$datos = mysql_query($sql, $link4);
	while($foo = mysql_fetch_assoc($datos)){
	$data0[] = $mylegend;
	$data1[] = intval($foo["sum(duration)"]/60);
	$data2[] = intval($foo["sum(billsec)"]/60);
	}
	$data3 = array_combine($data0,$data1);
	$data4 = array_combine($data0,$data2);
	arsort($data3);
	$mylegend = array_keys($data3);
	$values1 = array_values($data3);
	arsort($data4);
	$values2 = array_values($data4);
}

$width=450;
$height=750;
$graph = new Graph($width,$height,"auto");
$graph->SetShadow();
$graph->title->Set("Total minutos - $mes $anho");
$graph->title->SetFont(FF_FONT2,FS_BOLD);
$graph->SetScale("textlin");
$graph->xaxis->scale->SetGrace(4);
$top = 60;
$bottom = 30;
$left = 80;
$right = 30;
$graph->Set90AndMargin($left,$right,$top,$bottom);
$graph->xaxis->SetTickLabels($mylegend);
$graph->xaxis->SetLabelAlign('right','center','right');
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->SetLabelAlign('center','bottom');
// Enable X and Y Grid
$graph->xgrid->Show();
$graph->xgrid->SetColor('gray@0.5');
$graph->ygrid->SetColor('gray@0.5');
$graph->yaxis->HideZeroLabel();
$graph->xaxis->HideZeroLabel();
$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#CDDEFF@0.5');
// Format the legend box
$graph->legend->SetColor('navy');
$graph->legend->SetFillColor('gray@0.8');
$graph->legend->SetLineWeight(1);
//$graph->legend->SetFont(FF_ARIAL,FS_BOLD,8);
$graph->legend->SetShadow('gray@0.4',3);
$graph->legend->SetAbsPos(30,220,'right','bottom');

$p1 = new BarPlot($values1);
$p1->SetFillColor('orange');
$p1->SetWidth(0.5);
$p1->value->Show();
$p1->SetLegend("Total");
$p2 = new BarPlot($values2);
$p2->SetFillColor('blue');
$p2->SetWidth(0.5);
$p2->value->Show();
$p2->SetLegend("Al Aire");
$gbplot = new GroupBarPlot(array($p1,$p2));
$graph->Add($gbplot);
$graph->Stroke();

?>
