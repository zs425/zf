<?php

setlocale(LC_ALL, 'fr_FR');

class Cron_JobController extends Zend_Controller_Action
{
	public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

		$locale = new Zend_Locale();
		$locale->setLocale('fr_FR');
    }

    public function cleanupGraphImagesAction() {
        // function to clean up exported stats images

        $dir = realpath(APPLICATION_PATH . '/../public/images/tmp');
        $files = scandir($dir);

        foreach($files as $file){

            if(strpos($file, ".png"))
                unlink($dir.$file);

            if(strpos($file, ".gif"))
                unlink($dir.$file);
        }
    }

    public function cleanupAction() {
        // function to clean up exported CSV and ZIP files

        $dir = EXPORT_FOLDER;
        $files = scandir($dir);

        foreach($files as $file){

            if(strpos($file, ".csv"))
                unlink($dir.$file);

            if(strpos($file, ".zip"))
                unlink($dir.$file);
        }
    }

	public function sendMonthlySummaryAction() {
		$se = new BC_StatisticsExporter();
        $result = $se->getActiveCampaigns("monthlyStats");

		while ($row = $result->fetch()) {
			$sendTime = $row['send_time'];
			$clientEmail = $row['client_email'];
			$clientEmailSecondary = $row['client_email_secondary'];
			$clientEmailThird = $row['client_email_third'];
			$pmEmail = $row['proj_manager_email'];
			$pmName = $row['projmanager_fname']." ".$row['projmanager_lname'];
			$pmPhone = $row['projmanager_phone'];
			$clientName = $row['client_fname']." ".$row['client_lname'];
			$campaignName = $row['campaign_name'];
			$cid = $row['campaign_id'];
			$clientId = $row['client_id'];

			$lastMonth = date('m', strtotime(date('Y-m')." -1 month"));
			$lastMonthText = strftime("%B", strtotime(date('Y-m')." -1 month"));
			$currentYear = date('Y');

			$lastMonthsDailyLeads = $se->getDailyCampaignLeads($cid, $lastMonth, $currentYear);
			$currentYearsMonthlyLeads = $se->getMonthlyCampaignLeads($cid, $currentYear);
			$genderDistribution = $se->getGenderDistribution($cid, $lastMonth, $currentYear);

			$lastMonthStart = date("Y-m")."-01";
			$lastMonthStart = date("Y-m-d", strtotime($lastMonthStart." -1 month"));
			$lastMonthEnd = date("Y-m-t", strtotime($lastMonthStart));
			$lastMonthLeads = $se->getCampaignLeads($cid, $lastMonthStart, $lastMonthEnd, "", "");

			$lastYearAnnualLeads = $se->getAnnualCampaignLeads($cid, date('Y', strtotime(date('Y')." -1 year")));
			$currentYearAnnualLeads = $se->getAnnualCampaignLeads($cid, date('Y'));

			$annualLeads = array();
			$annualLeads[] = array($lastYearAnnualLeads, $currentYearAnnualLeads);
			$annualLeads[] = array(date('Y', strtotime(date('Y')." -1 year")), date('Y'));

			$topEmailDomains = $se->getTopEmailDomains($cid, 5, $lastMonthStart, $lastMonthEnd);

			$leadRate = $row['lead_rate'];
			$budgetLastMonth = $lastMonthLeads * $leadRate;

			// converting the result set into an array
			$lastMonthsDailyLeadsArray = array();
			while($row = $lastMonthsDailyLeads->fetch()) {
				$lastMonthsDailyLeadsArray[] = $row["leads"];
			}

			// converting the result set into an array
			$currentYearsMonthlyLeadsArray = array();
			while($row = $currentYearsMonthlyLeads->fetch()) {
				$currentYearsMonthlyLeadsArray[0][] = $row["leads"];
				$currentYearsMonthlyLeadsArray[1][] = $row["month"];
			}

			if(sizeof($lastMonthsDailyLeadsArray) > 0){
				$re = new BC_ReportsExporter();
				$graphDaily = $re->createBarGraph($lastMonthsDailyLeadsArray, array(595,350), "day", "Leads", "Jour", "Collecte mois de ".$lastMonthText);
				$graphMonthly = $re->createBarGraph($currentYearsMonthlyLeadsArray, array(290,280), "month", "Leads", "", "Collecte par mois / ".$currentYear);
				$graphAnnual = $re->createBarGraph($annualLeads, array(290,280), "year", "Leads", "", "Collecte annuel");
				$graphGender = $re->createPieGraph($genderDistribution, array(290,280), "", "", "Répartition Homme - Femme");
				$graphTopDomains = $re->createHorizontalBarGraph($topEmailDomains, array(290,280), 1, "", "", "Top 5 domains");

				// send if there is an email registered with the campaign
				if($clientEmail != ""){
					$this->sendMonthlyStatisticEmail($cid, $clientEmail, $pmEmail, $pmName, $pmPhone, $clientName, $campaignName, 1, $graphDaily, $graphMonthly, $graphGender, $graphAnnual, $budgetLastMonth, $lastMonthLeads, $graphTopDomains);

					//Send email to secondary email without name of the client and no CC to the project manager
					if($clientEmailSecondary != "")
						$this->sendMonthlyStatisticEmail($cid, $clientEmailSecondary, $pmEmail, $pmName, $pmPhone, $clientName, $campaignName, 0, $graphDaily, $graphMonthly, $graphGender, $graphAnnual, $budgetLastMonth, $lastMonthLeads, $graphTopDomains);

					// Send email to third email without name of the client and no CC to the project manager
					if($clientEmailThird != "")
						$this->sendMonthlyStatisticEmail($cid, $clientEmailThird, $pmEmail, $pmName, $pmPhone, $clientName, $campaignName, 0, $graphDaily, $graphMonthly, $graphGender, $graphAnnual, $budgetLastMonth, $lastMonthLeads, $graphTopDomains);
				}
			}
		}
		echo "mails sent. ok.";
	}

    function sendMonthlyStatisticEmail($cid, $clientMail, $pmEmail, $pmName, $pmPhone, $clientName, $campaignName, $ccToPM, $graphDaily, $graphMonthly, $graphGender, $graphAnnual, $budgetLastMonth, $leadsLastMonth, $graphTopDomains) {
		$lastMonth = utf8_encode(strftime("%B", strtotime(date('Y-m')." -1 month")));

		// creating and sending the mail
        $m = new BC_HtmlMailer();
        $m->setSubject("Reporting campagne $campaignName du mois de $lastMonth");
        $m->setViewParam('clientName',$clientName);
        $m->setViewParam('campaignName',$campaignName);
        $m->setViewParam('pmName',$pmName);
        $m->setViewParam('pmEmail',$pmEmail);
		$m->setViewParam('pmPhone',$pmPhone);
		$m->setViewParam('lastMonth',$lastMonth);
		$m->setViewParam('graphDaily',$graphDaily);
		$m->setViewParam('graphMonthly',$graphMonthly);
		$m->setViewParam('graphGender',$graphGender);
		$m->setViewParam('graphAnnual',$graphAnnual);
		$m->setViewParam('budgetLastMonth',$budgetLastMonth);
		$m->setViewParam('leadsLastMonth',$leadsLastMonth);
		$m->setViewParam('graphTopDomains',$graphTopDomains);

		$m->addTo($clientMail);

		if($ccToPM == 1)
			$m->addCc($pmEmail);

        $m->sendHtmlTemplate('statistics_monthly_summary.phtml');
    }

	public function sendMailsAction() {

		$se = new BC_StatisticsExporter();
        $result = $se->getActiveCampaigns();

		while ($row = $result->fetch()) {

			$sendTime = $row['send_time'];
			$clientEmail = $row['client_email'];
			$clientEmailSecondary = $row['client_email_secondary'];
			$clientEmailThird = $row['client_email_third'];
			$pmEmail = $row['proj_manager_email'];
			$pmName = $row['projmanager_fname']." ".$row['projmanager_lname'];
			$pmPhone = $row['projmanager_phone'];
			$clientName = $row['client_fname']." ".$row['client_lname'];
			$campaignName = $row['campaign_name'];
			$cid = $row['campaign_id'];
			$currentTime = date("H");
			$endDate = date("Y-m-d", time());
			$startDate = $se->getStartDate($row['send_day']);
			$sendTransp = $row['send_transp'];

			$sendTime = $row['send_time'];
			$leadRate = $row['lead_rate'];
			$clientId = $row['client_id'];

			$currentMonth = date("Y-m")."-01";
			$todayDate = date("Y-m-d");
			$yesterdayDate = date("Y-m-d", time() - 60 * 60 * 24);

			$last24HoursLeads = $se->getCampaignLeads($cid, $yesterdayDate, $todayDate, $sendTime, $sendTime);
			$currentMonthLeads = $se->getCampaignLeads($cid, $currentMonth, $todayDate, "00", $sendTime);
			$totalLeads = $se->getCampaignLeads($cid);

			$budgetCurrentMonth = $currentMonthLeads * $leadRate;

			// if startDate is not set, no mail is supposed to be sent on the selected day
            if($startDate){

        		$fileName = $se->prepareData($cid, $startDate, $endDate, "", $sendTime, $sendTransp);

                // send if the day is correct
                // and the time is correct
                // and that there is an email registered with the campaign
                if($sendTime == $currentTime && $startDate && $clientEmail != ""){
                    $this->sendEmail($cid, $clientEmail, $pmEmail, $startDate, $pmName, $pmPhone, $clientName, $campaignName, $fileName, 1, $currentMonth, $todayDate, $yesterdayDate, $last24HoursLeads, $currentMonthLeads, $budgetCurrentMonth, $sendTime, $totalLeads);

					// Send email to secondary email without name of the client and no CC to the project manager
					if($clientEmailSecondary != "")
						$this->sendEmail($cid, $clientEmailSecondary, $pmEmail, $startDate, $pmName, $pmPhone, "", $campaignName, $fileName, 0, $currentMonth, $todayDate, $yesterdayDate, $last24HoursLeads, $currentMonthLeads, $budgetCurrentMonth, $sendTime, $totalLeads);

					// Send email to third email without name of the client and no CC to the project manager
					if($clientEmailThird != "")
						$this->sendEmail($cid, $clientEmailThird, $pmEmail, $startDate, $pmName, $pmPhone, "", $campaignName, $fileName, 0, $currentMonth, $todayDate, $yesterdayDate, $last24HoursLeads, $currentMonthLeads, $budgetCurrentMonth, $sendTime, $totalLeads);
				}

                // removoing the csv and the zip file
                unlink($fileName); // zip file
                unlink(str_replace("zip", "csv", $fileName)); // csv file
            }
		}
	}

    function sendEmail($cid, $clientMail, $pmEmail, $startDate, $pmName, $pmPhone, $clientName, $campaignName, $fileName, $ccToPM, $currentMonth, $todayDate, $yesterdayDate, $last24HoursLeads, $currentMonthLeads, $budgetCurrentMonth, $sendTime, $totalLeads) {

		$startDate = date("d/m/Y", strtotime($startDate));
		$endDate = date("d/m/Y", time());

		$currentMonth = date("d/m/Y", strtotime($currentMonth));
		$todayDate = date("d/m/Y", strtotime($todayDate));
		$yesterdayDate = date("d/m/Y", strtotime($yesterdayDate));

        // creating the attachment, with the zip file, for the mail
        $attachment = new Zend_Mime_Part(file_get_contents($fileName));
        $attachment->type = "application/zip";
        $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = Zend_Mime::ENCODING_BASE64;
        $attachment->filename = $fileName;

		// creating and sending the mail
        $m = new BC_HtmlMailer();
        $m->setSubject("Statistiques campagne $campaignName - $startDate au $endDate");
        $m->setViewParam('clientName',$clientName);
        $m->setViewParam('campaignName',$campaignName);
        $m->setViewParam('pmName',$pmName);
        $m->setViewParam('pmEmail',$pmEmail);
		$m->setViewParam('pmPhone',$pmPhone);

		$m->setViewParam('currentMonth',$currentMonth);
		$m->setViewParam('todayDate',$todayDate);
		//$m->setViewParam('yesterdayDate',$yesterdayDate);
		$m->setViewParam('yesterdayDate',$startDate);
		$m->setViewParam('last24HoursLeads',$last24HoursLeads);
		$m->setViewParam('currentMonthLeads',$currentMonthLeads);
		$m->setViewParam('budgetCurrentMonth',$budgetCurrentMonth);
		$m->setViewParam('sendTime',$sendTime);
		$m->setViewParam('totalLeads',$totalLeads);

		$m->addTo($clientMail);

		if($ccToPM == 1)
			$m->addCc($pmEmail);

		$m->addAttachment($attachment);
        $m->sendHtmlTemplate('statistics.phtml');
    }
}
