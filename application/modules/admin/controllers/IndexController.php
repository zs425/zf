<?php
class Admin_IndexController extends BC_Controller_Action
{
	public function init() {
	}

	public function indexAction() {
		$this->_redirect($this->_helper->url('list', 'index', 'admin'));
	}

	public function listAction() {
		$this->view->setTitrePage("Classement");

        $counter = 0;
        $this->view->campaigns = array();

        while ($counter <= 2){

            if($counter == 2){
                $month = date("F Y");
                $sql = "SELECT c.name, COUNT(l.id) as leads, COUNT(l.id) * rate as turnover  FROM campaign c
						LEFT JOIN stats_campaigns s ON c.id = s.campaign_id
						LEFT JOIN stats_publishers p ON c.id = p.campaign_id
						LEFT JOIN lead l ON c.id = l.campaign_id
						AND p.publisher_id=l.publisher_id
						WHERE 1
						AND type_coreg = 1
						AND MONTH(l.record_date) = MONTH(NOW())
						AND YEAR(l.record_date) = YEAR(NOW())
						GROUP BY c.id
						ORDER BY turnover DESC
						LIMIT 5";
            }

            if($counter == 1){
                $month = date("F Y", strtotime('-1 month', time()));

   				$m1 = date("n", strtotime('-1 month', time()));
   				$m3 = date("m", strtotime('-1 month', time()));
   				$m2 = date("n");

				if(($m2 - $m1) < 0)
					 $year = date("Y", strtotime('-1 year', time()));

                $sql = "SELECT c.name, COUNT(l.id) as leads, COUNT(l.id) * rate as turnover  FROM campaign c
						LEFT JOIN stats_campaigns s ON c.id = s.campaign_id
						LEFT JOIN stats_publishers p ON c.id = p.campaign_id
						LEFT JOIN lead l ON c.id = l.campaign_id
						AND p.publisher_id=l.publisher_id
						WHERE 1
						AND type_coreg = 1
						AND MONTH(l.record_date) = '".$m3."'
						AND YEAR(l.record_date) = '".$year."'
						GROUP BY c.id
						ORDER BY turnover DESC
						LIMIT 5";
            }

            if($counter == 0){
                $month = date("F Y", strtotime('-2 month', time()));

   				$m1 = date("n", strtotime('-2 month', time()));
   				$m3 = date("m", strtotime('-2 month', time()));
   				$m2 = date("n");

				if(($m2 - $m1) < 0)
					 $year = date("Y", strtotime('-1 year', time()));

                $sql = "SELECT c.name, COUNT(l.id) as leads, COUNT(l.id) * rate as turnover  FROM campaign c
						LEFT JOIN stats_campaigns s ON c.id = s.campaign_id
						LEFT JOIN stats_publishers p ON c.id = p.campaign_id
						LEFT JOIN lead l ON c.id = l.campaign_id
						AND p.publisher_id=l.publisher_id
						WHERE 1
						AND type_coreg = 1
						AND MONTH(l.record_date) = '".$m3."'
						AND YEAR(l.record_date) = '".$year."'
						GROUP BY c.id
						ORDER BY turnover DESC
						LIMIT 5";
            }

            $db = Zend_Db_Table::getDefaultAdapter();
            $result = $db->query($sql);

            $this->view->campaigns[$counter][0] = $month;
            $this->view->campaigns[$counter][1] = $result;

            $counter++;
        }

        $counter = 0;
        $this->view->publishers = array();
        while ($counter <= 2){

            if($counter == 2){
                $month = date("F Y");
				$date = date("Y-m");
				$dateStart = $date."-01";
				$dateEnd = $date."-".date("t");

				$publishersCampaigns = $this->getPublishersCampaigns($dateStart, $dateEnd);

				$publishers = array();

				foreach($publishersCampaigns as $publisher){

					$publisherName = $publisher["publisher_name"];
					$campaignTurnover = $publisher["turnover"];

					if(array_key_exists($publisherName, $publishers))
						$publishers[$publisherName] = $publishers[$publisherName] + $campaignTurnover;
					else
						$publishers[$publisherName] = $campaignTurnover;
				}

				arsort($publishers);
				$publishers = array_slice($publishers, 0, 4);
            }

            if($counter == 1){
				$month = date("F Y", strtotime('-1 month', time()));
				$date = date("Y-m", strtotime('-1 month', time()));
				$dateStart = $date."-01";
				$dateEnd = $date."-".date("t", strtotime('-1 month', time()));

				$publishersCampaigns = $this->getPublishersCampaigns($dateStart, $dateEnd);

				$publishers = array();

				foreach($publishersCampaigns as $publisher){

					$publisherName = $publisher["publisher_name"];
					$campaignTurnover = $publisher["turnover"];

					if(array_key_exists($publisherName, $publishers))
						$publishers[$publisherName] = $publishers[$publisherName] + $campaignTurnover;
					else
						$publishers[$publisherName] = $campaignTurnover;
				}

				arsort($publishers);
				$publishers = array_slice($publishers, 0, 4);
            }

            if($counter == 0){

				$month = date("F Y", strtotime('-2 month', time()));
				$date = date("Y-m", strtotime('-2 month', time()));
				$dateStart = $date."-01";
				$dateEnd = $date."-".date("t", strtotime('-2 month', time()));

				$publishersCampaigns = $this->getPublishersCampaigns($dateStart, $dateEnd);

				$publishers = array();

				foreach($publishersCampaigns as $publisher){

					$publisherName = $publisher["publisher_name"];
					$campaignTurnover = $publisher["turnover"];

					if(array_key_exists($publisherName, $publishers))
						$publishers[$publisherName] = $publishers[$publisherName] + $campaignTurnover;
					else
						$publishers[$publisherName] = $campaignTurnover;
				}

				arsort($publishers);
				$publishers = array_slice($publishers, 0, 4);
            }

            $db = Zend_Db_Table::getDefaultAdapter();
            $result = $db->query($sql);

            $this->view->publishers[$counter][0] = $month;
            $this->view->publishers[$counter][1] = $publishers;

            $counter++;
        }
	}

	public function getPublishersCampaigns($dateStart, $dateEnd) {

		//$dateStart = "2013-11-02";
		//$dateEnd = "2013-11-02";

		$sql = "SELECT
					COUNT(l.id) as leads,
					s.rate,
					p.name as publisher_name,
					p.price as price,
					p.publisher_id,
					p.campaign_id,
					(s.rate * COUNT(l.id)) as turnover
				FROM campaign c
				LEFT JOIN stats_campaigns s ON c.id = s.campaign_id
				LEFT JOIN stats_publishers p ON c.id = p.campaign_id
				LEFT JOIN lead l ON c.id = l.campaign_id AND p.publisher_id=l.publisher_id
				WHERE record_date >= '".$dateStart."'
				AND record_date <= '".$dateEnd." 23:59:59'
				GROUP BY p.campaign_id, p.publisher_id
				ORDER BY p.publisher_id ASC";

		//echo $sql."<br>";

		$db = Zend_Db_Table::getDefaultAdapter();
		$result = $db->query($sql)->fetchAll();

		return $result;
	}

	public function topAction() {
		$this->view->setTitrePage("Classement");
	}
}
