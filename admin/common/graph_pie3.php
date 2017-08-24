<?php /* $Id: graph_pie.php 6816 2008-09-19 18:33:18Z p_lindheimer $ */
include_once(dirname(__FILE__) . "/../cdr/lib/defines.php");
include_once(dirname(__FILE__) . "/../cdr/lib/Class.Table.php");
include_once(dirname(__FILE__) . "/../cdr/jpgraph_lib/jpgraph.php");
include_once(dirname(__FILE__) . "/../cdr/jpgraph_lib/jpgraph_pie.php");
include_once(dirname(__FILE__) . "/../cdr/jpgraph_lib/jpgraph_pie3d.php");


getpost_ifset(array('months_compare', 'fromstatsmonth_sday', 'clidtype', 'srctype', 'src'));
//$fromstatsmonth_sday = '2011-03';
//$clidtype = '1';
//$src = '';
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
$i=0;
while ($nego = mysql_fetch_array($contenido)){
$mylegend = $nego["negocio"];
//echo $mylegend."\n";
if ($clidtype=='1') {
	$sql = "select sum(duration) from cdr where negocio='$mylegend' and calldate rlike '$fromstatsmonth_sday'";
	if ($src!='')
	$sql .= " and src='$src'";
	$datos = mysql_query($sql, $link4);
	while($foo = mysql_fetch_assoc($datos)){
        $data[] = intval($foo["sum(duration)"]/60);
	$data2[] = $mylegend;
	$i++;
	}
	$data3 = array_combine($data2,$data);
	arsort($data3);
        $legend=array_keys($data3);
        $values=array_values($data3);
	$j=0;
	while($j < $i) {
	$legend2[$j]=$legend[$j].": ".$values[$j];
	$legend3[$j]=$legend[$j]."\n%.1f%%";
	$j++;
	//var_dump($values);
	}
   } else {
	$sql = "select sum(billsec) from cdr where negocio='$mylegend' and calldate rlike '$fromstatsmonth_sday'";
	if ($src!='') 
	$sql.= " and src='$src'";
   	$datos = mysql_query($sql, $link4);
        while($foo = mysql_fetch_assoc($datos)){
        $data[] = intval($foo["sum(billsec)"]/60);
        $data2[] = $mylegend;
	$i++;
        }
         $data3 = array_combine($data2,$data);
         arsort($data3);
         $legend=array_keys($data3);
         $values=array_values($data3);
         $j=0;
         while($j < $i) {
         $legend2[$j]=$legend[$j].": ".$values[$j];
         $legend3[$j]=$legend[$j]."\n%.1f%%";
         $j++;
         }
	}   
   }


$graph = new PieGraph(780,540,"auto");
$graph->SetShadow();
if ($clidtype=='1')
$graph->title->Set("Total minutos - $mes $anho");
else
$graph->title->Set("Total minutos al aire - $mes $anho");
$graph->title->SetFont(FF_FONT2,FS_BOLD);

$p1 = new PiePlot($values);
//$p1->ExplodeSlice(0);
$p1->SetCenter(0.35);
$p1->SetSize(0.45);
$p1->SetLabelType(PIE_VALUE_PER);
$p1->SetLabelPos(1.0);
$p1->SetLabels($legend3);
$p1->SetLegends($legend2);


// Format the legend box
$graph->legend->SetColor('navy');
$graph->legend->SetFillColor('gray@0.8');
$graph->legend->SetLineWeight(1);
//$graph->legend->SetFont(FF_ARIAL,FS_BOLD,8);
$graph->legend->SetShadow('gray@0.4',3);
//$graph->legend->SetAbsPos(10,80,'right','bottom');

$graph->Add($p1);
$txt =new Text("Negocio");
$txt->SetFont(FF_FONT1,FS_BOLD);
$txt->Pos( 645,50);
$txt->SetColor( "black");
$graph->AddText( $txt);
$graph->Stroke();

?>
