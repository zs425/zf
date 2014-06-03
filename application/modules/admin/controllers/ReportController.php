<?php

require_once(APPLICATION_PATH . '/../library/JPGraph/src/jpgraph.php');
require_once(APPLICATION_PATH . '/../library/JPGraph/src/jpgraph_pie.php');
require_once(APPLICATION_PATH . '/../library/JPGraph/src/jpgraph_line.php');
require_once(APPLICATION_PATH . '/../library/JPGraph/src/jpgraph_bar.php');
require_once(APPLICATION_PATH . '/../library/JPGraph/src/jpgraph_date.php');

class Admin_ReportController extends Zend_Controller_Action
{
	public function init() {

	}

	public function indexAction() {

		global $gDateLocale;

		// Some data
		$datay=array(7,19,11,4,20);

		// Create the graph and setup the basic parameters
		$graph = new Graph(300,200,'auto');
		$graph->img->SetMargin(40,30,40,50);
		$graph->SetScale("textint");
		$graph->SetFrame(true,'blue',1);
		$graph->SetColor('lightblue');
		$graph->SetMarginColor('lightblue');

		// Setup X-axis labels
		$a = $gDateLocale->GetShortMonth();
		$graph->xaxis->SetTickLabels($a);
		$graph->xaxis->SetFont(FF_FONT1);
		$graph->xaxis->SetColor('darkblue','black');

		// Setup "hidden" y-axis by given it the same color
		// as the background (this could also be done by setting the weight
		// to zero)
		$graph->yaxis->SetColor('lightblue','darkblue');
		$graph->ygrid->SetColor('white');

		// Setup graph title ands fonts
		$graph->title->Set('Using grace = 0');
		$graph->title->SetFont(FF_FONT2,FS_BOLD);
		$graph->xaxis->SetTitle('Year 2002','center');
		$graph->xaxis->SetTitleMargin(20);
		$graph->xaxis->title->SetFont(FF_FONT2,FS_BOLD);

		// Add some grace to the top so that the scale doesn't
		// end exactly at the max value.
		$graph->yaxis->scale->SetGrace(0);


		// Create a bar pot
		$bplot = new BarPlot($datay);
		$bplot->SetFillColor('darkblue');
		$bplot->SetColor('darkblue');
		$bplot->SetWidth(0.6);
		$bplot->SetShadow('darkgray');

		// Setup the values that are displayed on top of each bar
		// Must use TTF fonts if we want text at an arbitrary angle
		$bplot->value->Show();
		$bplot->value->SetFont(FF_ARIAL,FS_NORMAL,8);
		$bplot->value->SetFormat('$%d');
		$bplot->value->SetColor('darkred');
		$bplot->value->SetAngle(45);
		$graph->Add($bplot);

		// Finally stroke the graph
		$graph->img->SetImgFormat('gif');
		$image = "images/tmp/graph".rand(1, 1000).".gif";
		$graph->Stroke($image);

		echo ('<img src="'.$image.'">'); // show the image
	}

}

