<?php
class Admin_StatisticController extends BC_Controller_Action
{
	/**
	 * Vue des statistiques de campagnes
	 *
	 * @var Table_StatsCampaigns
	 */
	private $_statisticCampaignView;

	/**
	 * Vue des statistiques éditeur par campagne
	 *
	 * @var Table_StatsPublishers
	 */
	private $_statisticPublisherView;

	public function init() {
		$this->_statisticCampaignView = new Table_StatsCampaigns();
		$this->_statisticPublisherView = new Table_StatsPublishers();
		$this->_statisticLeadView = new Table_Lead();
	}

	public function indexAction() {
		$this->_redirect($this->_helper->url('list', 'statistic', 'admin'));
	}

	/*
		Exports the leads in the database to a CSV file for a selected campaign.
		The user sets the From and To dates.
	*/
	public function exportAction() {

		if($this->_hasParam('cid'))
        {
            $cid = (int)$this->_getParam('cid');
            $pid = $this->_hasParam('pid') ? (int)$this->_getParam('pid'):null;
            $params = $this->getRequest()->getParams();

            if(isset($params['prev_period']) && $params['prev_period'])	{
                $dateStart = $params['prev_period'];
            }
            if(isset($params['next_period']) && $params['next_period'])	{
                $dateEnd = $params['next_period'];
            }

			$sql = "SELECT ca.send_transp
					FROM campaign c
					INNER JOIN campaign_advertiser ca ON c.campaign_advertiser_id = ca.id
					WHERE c.id = ".$cid;

			$db = Zend_Db_Table::getDefaultAdapter();
			$result = $db->fetchRow($sql);
			$sendTransp = $result["send_transp"];

            /*
            	Creating the CSV file and zips it
            */
            $se = new BC_StatisticsExporter();
            $fileName = $se->prepareData($cid, $dateStart, $dateEnd, $pid, "", $sendTransp);

			if($fileName){
				if(file_exists($fileName)) {
					// Pushes to download the zip
					header('Content-type: application/zip');
					header('Content-Disposition: attachment; filename="'.$fileName.'"');
					header('Content-Length: '.filesize($fileName));
					ob_clean();
					flush();
					readfile($fileName);

					// Deleting the zip file
                	unlink(str_replace("zip", "csv", $fileName)); // csv file
					unlink($fileName);
				}
			}else{
				return $fileName;
			}
        }

		exit;
	}

	/*
		Listing all the campaigns.
		The date is by default set to todays date.
	*/
	public function listAction() {

		$params = $this->getRequest()->getParams();

		$this->startDate = '';

		//Liste de campagnes
		$date = new Zend_Date();
		$sql = array();

		/*
			Starting to create the SQL code needed for creating the list of campaigns
		*/
		$sql[] = "
					SELECT
						c.*,
						COUNT(l.id) as leads,
						s.volume,
						s.rate,
						p.name as name_publisher,
						p.price as price,
						p.volume as volume_publisher,
						p.publisher_id,
						p.campaign_id
					FROM campaign c
					LEFT JOIN stats_campaigns s ON c.id = s.campaign_id
					LEFT JOIN stats_publishers p ON c.id = p.campaign_id
					LEFT JOIN lead l ON c.id = l.campaign_id AND p.publisher_id=l.publisher_id
					WHERE
					1
				";

		if(!isset($params['prev_period']) && !$params['prev_period'] && !isset($params['next_period']) && !$params['next_period']){
			$todaysDate = date('d/m/Y');

			$this->view->startDate = $todaysDate;
			$this->view->searchStartDateSweFormat = $date->set($todaysDate, 'DD/MM/YYYY')->toString('y-MM-dd');
			$sql[] = "AND (record_date >= '{$this->view->searchStartDateSweFormat}')";

			$this->view->endDate = $todaysDate;
			$this->view->searchEndDateSweFormat = $date->set($todaysDate, 'DD/MM/YYYY')->toString('y-MM-dd');
			$sql[] = "AND (record_date <= '{$this->view->searchEndDateSweFormat} 23:59:59')";
		}

		if(isset($params['prev_period']) && $params['prev_period'])
		{
			$this->view->startDate = $params['prev_period'];
			$this->view->searchStartDateSweFormat = $date->set($params['prev_period'], 'DD/MM/YYYY')->toString('y-MM-dd');
			$sql[] = "AND (record_date >= '{$this->view->searchStartDateSweFormat}')";
		}
		if(isset($params['next_period']) && $params['next_period'])
		{
			$this->view->endDate = $params['next_period'];
			$this->view->searchEndDateSweFormat = $date->set($params['next_period'], 'DD/MM/YYYY')->toString('y-MM-dd');
			$sql[] = "AND (record_date <= '{$this->view->searchEndDateSweFormat} 23:59:59')";
		}


		if(isset($params['cid'])){
			/*
				Showing information about the selected campaign
				and the list of publishers that are participating in it
			*/
			$cid = (int)$params['cid'];
			//$this->view->stats = $this->_statisticPublisherView->fetchAll('campaign_id = ' . $cid);

			$sql[] = " AND p.campaign_id = '$cid' AND p.publisher_id IS NOT NULL";
			$sql[] = " GROUP BY p.publisher_id";
			$sql = implode(' ', $sql);

			$stmt = new Zend_Db_Statement_Pdo($this->_statisticPublisherView->getAdapter(), $sql);
			$stmt->execute();
			$this->view->stats = $stmt->fetchAll();

			$TCampaign = new Table_Campaign();
			$this->view->cid = $cid;
			$this->view->campaign = $TCampaign->find($cid)->current()->name;
			$this->view->setTitrePage("Statistiques campagne par éditeur");
			$this->view->collectGraph = $this->getEditorsGraphs($params['cid']);
			$this->render('publisher');

		} else {

			/*
				Since the campaign id (cid) is not set the creation of the campaign list continues
			*/
			$params = $this->getRequest()->getParams();
			$this->view->filter = false;

			// Selects the campaigns of the type "Coregistration" or "Mail"
			if($params['type_coreg'] == 1 || $params['type_coreg'] == ""){
				$sql[] = "AND type_coreg = 1";
				$this->view->typeCoreg = 1;
			}
			else{
				$sql[] = "AND type_coreg = 0";
				$this->view->typeCoreg = 0;
			}

			/*
				Selects the campaigns of the type "Active" or "Archived"
			*/
			if (isset($params['filter'])) {
				if ($params['filter'] == 'archived') {
					$this->view->setTitrePage("Statistiques campagnes archivées");
					$this->view->filter = true;
					$sql[] = " AND c.status = 0";
				}
			} else {
				$this->view->setTitrePage("Statistiques campagnes");
				$sql[] = " AND c.status = 1";
			}

			$sql[] = "GROUP BY c.id";
			$sql = implode(' ', $sql);

			$stmt = new Zend_Db_Statement_Pdo($this->_statisticCampaignView->getAdapter(), $sql);
			$stmt->execute();

			$result = $stmt->fetchAll();

			if($params['next_period'] != "" && $params['prev_period'] != "")
				$this->view->totalTurnover = $this->getTurnover($result);
			else
				$this->view->totalTurnover = -1;

			$this->view->stats = $result;
			$this->render('campaign');
		}
	}

	/*
		Getting the total turnover for all the campaigns in the list
	*/
	private function getTurnover($result){

		$turnover = 0;
		foreach($result as $campaign){

			$turnover = $turnover + $campaign['leads'] * $campaign['rate'];
		}

		return number_format($turnover, 0, '', ' ');
	}

	/*
		Creating the graph of registered leads for the selected campaign
	*/
	private function getEditorsGraphs($cid){

		/*
			Getting all the publishers that are participating in this campaign
		*/
		$sql = "SELECT DISTINCT publisher_id, name FROM stats_publishers WHERE campaign_id = ".$cid;
		$stmt = new Zend_Db_Statement_Pdo($this->_statisticPublisherView->getAdapter(), $sql);
		$stmt->execute();
		$publishers = $stmt->fetchAll();

		$year = date("Y");

		/*
			Looping all the publishers and collecting data from each one of them.
			This data is used to create the graph.
		*/
		if(sizeof($publishers) > 0){
			foreach($publishers as $publisher){
				$sql = "SELECT COUNT(*) as volume, MONTHNAME(record_date) as month FROM lead
						WHERE campaign_id = ".$cid."
						AND publisher_id = ".$publisher["publisher_id"]."
						AND YEAR(record_date) = ".$year."
						GROUP BY MONTH(record_date)";

				$stmt = new Zend_Db_Statement_Pdo($this->_statisticLeadView->getAdapter(), $sql);
				$stmt->execute();
				$publisherStats = $stmt->fetchAll();

				if(sizeof($publisherStats) > 0){
					$publishersData[$publisher["publisher_id"]]["name"] = $publisher["name"];

					$n = 0;
					foreach($publisherStats as $value){
						$publishersData[$publisher["publisher_id"]]["volume"][$n] = $value["volume"];
						$publishersData[$publisher["publisher_id"]]["month"][$n] = $value["month"];
						$n++;
					}

					/*
						Fix for the line graph when there is only one data point
					*/
					if(sizeof($publishersData) == 1){
						$publishersData[$publisher["publisher_id"]]["volume"][1] = "0";
						$publishersData[$publisher["publisher_id"]]["month"][1] = "";
					}
				}
			}

			if(sizeof($publishersData)){
				$re = new BC_ReportsExporter();
				$graphDaily = $re->createLineGraph($publishersData, array(930,380), "Collect", "", "Collect par editeur - ".$year);
			}else{
				$graphDaily = "";
			}
		}else{
			$graphDaily = "";
		}

		return $graphDaily;
	}

	public function campaignAction() {
		$params = $this->getRequest()->getParams();
		if (isset($params['cid'])) {
			$cid = (int)$params['cid'];
			$TCampaign = new Table_Campaign();
			$this->view->campaign = $TCampaign->find($cid)->current()->name;
			$this->view->stats = $this->_statisticPublisherView->fetchAll('campaign_id = ' . $cid);
		}
	}

	public function extractAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$params = $this->getRequest()->getParams();
		if (isset($params['cid'])) {

		}
	}



}
