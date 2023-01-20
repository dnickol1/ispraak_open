<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_bar.php');

//example request
//https://www.ispraak.net/graphite_ispraak.php?a=10&b=23&c=13&d=16&e=14&w1=tacos&w2=burritos&w3=quesadillas&w4=salsa&w5=flautas

//these are the raw frequency numbers of missed words

$g1=$_GET['a']; //from query string
$g2=$_GET['b']; //from query string
$g3=$_GET['c']; //from query string
$g4=$_GET['d']; //from query string
$g5=$_GET['e']; //from query string

//these are the words themselves

$w1=$_GET['w1']; //from query string
$w2=$_GET['w2']; //from query string
$w3=$_GET['w3']; //from query string
$w4=$_GET['w4']; //from query string
$w5=$_GET['w5']; //from query string

//set scale based on largest number above

$freak = array($w1, $w2, $w3, $w4, $w5);
sort($freak);
$hi_freak = $freak[$w5];

//doing top 5 words for now

$datay=array($g1,$g2,$g3,$g4,$g5);

// Create the graph. These two calls are always required
//changing 220 to 420 to accomodate longer words
$graph = new Graph(320,300,'auto');
$graph->SetScale('textlin',0,$hi_freak); 

$theme_class=new UniversalTheme;
$graph->SetTheme($theme_class);

//change left margin from 50 to 100
$graph->Set90AndMargin(100,40,40,40);
$graph->img->SetAngle(90); 

// set major and minor tick positions manually
$graph->SetBox(false);

//$graph->ygrid->SetColor('gray');
$graph->ygrid->Show(false);
$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels(array($w1,$w2,$w3,$w4,$w5));
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

// For background to be gradient, setfill is needed first.
$graph->SetBackgroundGradient('#CCE0F5', '#FFFFFF', GRAD_HOR, BGRAD_PLOT);

// Create the bar plots
$b1plot = new BarPlot($datay);

// ...and add it to the graPH
$graph->Add($b1plot);

$b1plot->SetWeight(0);
$b1plot->SetFillGradient("#3385D6","#00478F",GRAD_HOR);
$b1plot->SetWidth(17);

// Display the graph
$graph->Stroke();
?>