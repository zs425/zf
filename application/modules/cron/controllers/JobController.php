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

    /*
		Deleting exported graph images.
		Called by a cron job.
    */
    public function cleanupGraphImagesAction() {
        $dir = realpath(APPLICATION_PATH . '/../public/images/tmp');
        $files = scandir($dir);

        foreach($files as $file){

            if(strpos($file, ".png"))
                unlink($dir.$file);

            if(strpos($file, ".gif"))
                unlink($dir.$file);
        }
    }

    /*
		Deleting exported CSV and ZIP files.
		Called by a cron job.
    */    
    public function cleanupAction() {
        $dir = EXPORT_FOLDER;
        $files = scandir($dir);

        foreach($files as $file){

            if(strpos($file, ".csv"))
                unlink($dir.$file);

            if(strpos($file, ".zip"))
                unlink($dir.$file);
        }
    }

	/*
		Sending a mail with a summary of the latest months statistics.
		Sent monthly through a cron job.
	*/
	public function sendMonthlySummaryAction() {
		$se = new BC_StatisticsExporter();
        $result = $se->getActiveCampaigns("monthlyStats");

		/*
			Looping all active campaigns
		*/
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

			/*
				Fix for January
			*/
			if(date("n") == "1"){
				$currentYear = date('Y', strtotime(date('Y')." -1 year"));
			}else{
				$currentYear = date('Y');
			}

			$lastMonth = date('m', strtotime(date('Y-m')." -1 month"));
			$lastMonthText = strftime("%B", strtotime(date('Y-m')." -1 month"));

			/*
				Getting leads per day for the latest month
			*/
			$lastMonthsDailyLeads = $se->getDailyCampaignLeads($cid, $lastMonth, $currentYear);
			$currentYearsMonthlyLeads = $se->getMonthlyCampaignLeads($cid, $currentYear);
			$genderDistribution = $se->getGenderDistribution($cid, $lastMonth, $currentYear);

			/*
				Getting leads for the latest month
			*/
			$lastMonthStart = date("Y-m")."-01";
			$lastMonthStart = date("Y-m-d", strtotime($lastMonthStart." -1 month"));
			$lastMonthEnd = date("Y-m-t", strtotime($lastMonthStart));
			$lastMonthLeads = $se->getCampaignLeads($cid, $lastMonthStart, $lastMonthEnd, "", "");

			/*
				Getting leads for the current year
			*/			
			$lastYearAnnualLeads = $se->getAnnualCampaignLeads($cid, date('Y', strtotime(date('Y')." -1 year")));
			$currentYearAnnualLeads = $se->getAnnualCampaignLeads($cid, date('Y'));
			$annualLeads = array();
			$annualLeads[] = array($lastYearAnnualLeads, $currentYearAnnualLeads);
			$annualLeads[] = array(date('Y', strtotime(date('Y')." -1 year")), date('Y'));

			$topEmailDomains = $se->getTopEmailDomains($cid, 5, $lastMonthStart, $lastMonthEnd);

			/*
				Getting the budget for the last month
			*/
			$leadRate = $row['lead_rate'];
			$budgetLastMonth = $lastMonthLeads * $leadRate;

			/*
				Converting the result set into an array to be used in the function that creates the graphs
			*/
			$lastMonthsDailyLeadsArray = array();
			while($row = $lastMonthsDailyLeads->fetch()) {
				$lastMonthsDailyLeadsArray[] = $row["leads"];
			}

			/*
				Converting the result set into an array to be used in the function that creates the graphs
			*/
			$currentYearsMonthlyLeadsArray = array();
			while($row = $currentYearsMonthlyLeads->fetch()) {
				$currentYearsMonthlyLeadsArray[0][] = $row["leads"];
				$currentYearsMonthlyLeadsArray[1][] = $row["month"];
			}

			/*
				Sending a mail only if there are leads collected for the campaign.
			*/
			if(sizeof($lastMonthsDailyLeadsArray) > 0){
				$re = new BC_ReportsExporter();
				$graphDaily = $re->createBarGraph($lastMonthsDailyLeadsArray, array(595,350), "day", "Leads", "Jour", "Collecte mois de ".$lastMonthText);
				$graphMonthly = $re->createBarGraph($currentYearsMonthlyLeadsArray, array(290,280), "month", "Leads", "", "Collecte par mois / ".$currentYear);
				$graphAnnual = $re->createBarGraph($annualLeads, array(290,280), "year", "Leads", "", "Collecte annuel");
				$graphGender = $re->createPieGraph($genderDistribution, array(290,280), "", "", "Répartition Homme - Femme");
				$graphTopDomains = $re->createHorizontalBarGraph($topEmailDomains, array(290,280), 1, "", "", "Top 5 domains");

				/*
					Send mail if there is a mail address registered with the campaign
				*/
				if($clientEmail != ""){
					$this->sendMonthlyStatisticEmail($cid, $clientEmail, $pmEmail, $pmName, $pmPhone, $clientName, $campaignName, 1, $graphDaily, $graphMonthly, $graphGender, $graphAnnual, $budgetLastMonth, $lastMonthLeads, $graphTopDomains);

					/*
						Send email to secondary mail address.
						The mail is sent without the client name in it "Hello XXXXX" and the project manager is not in CC
					*/
					if($clientEmailSecondary != "")
						$this->sendMonthlyStatisticEmail($cid, $clientEmailSecondary, $pmEmail, $pmName, $pmPhone, $clientName, $campaignName, 0, $graphDaily, $graphMonthly, $graphGender, $graphAnnual, $budgetLastMonth, $lastMonthLeads, $graphTopDomains);

					/*
						Send email to third mail address.
						The mail is sent without the client name in it "Hello XXXXX" and the project manager is not in CC
					*/
					if($clientEmailThird != "")
						$this->sendMonthlyStatisticEmail($cid, $clientEmailThird, $pmEmail, $pmName, $pmPhone, $clientName, $campaignName, 0, $graphDaily, $graphMonthly, $graphGender, $graphAnnual, $budgetLastMonth, $lastMonthLeads, $graphTopDomains);
				}
			}
			else{
				echo "Zero monthly leads: $cid<br>";
			}
		}
		echo "Mails sent. OK.";
	}

    /*
    	Creating and sending mail to client
    */
    public function sendMonthlyStatisticEmail($cid, $clientMail, $pmEmail, $pmName, $pmPhone, $clientName, $campaignName, $ccToPM, $graphDaily, $graphMonthly, $graphGender, $graphAnnual, $budgetLastMonth, $leadsLastMonth, $graphTopDomains) {
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
		$m->addBcc("test@baseandco.com");

		if($ccToPM == 1)
			$m->addCc($pmEmail);

        $m->sendHtmlTemplate('statistics_monthly_summary.phtml');
    }

    public function testThisFunctionAction(){
   		// creating and sending the mail
        $m = new BC_HtmlMailer();
        $m->setSubject("Testing cron www-data");
		$m->addTo("test@baseandco.com");
        $m->sendHtmlTemplate('statistics.phtml');
    }

	/*
		Sending the mail for planned sendouts.
		Day and time are set on the Campaign page.
		Sent through a cron job.
	*/
	public function sendStatisticsAction() {

		$se = new BC_StatisticsExporter();
        $result = $se->getActiveCampaigns();

		/*
			Looping all the active campaigns
		*/
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

			$ftpHost = $row['ftp_host'];
			$ftpUser = $row['ftp_user'];
			$ftpPassword = $row['ftp_password'];
			$ftpPath = $row['ftp_path'];
			$ftpType = $row['ftp_type'];
			$ftpPort = $row['ftp_port'];

			$sendTime = $row['send_time'];
			$leadRate = $row['lead_rate'];
			$clientId = $row['client_id'];

			$currentMonth = date("Y-m")."-01";
			$todayDate = date("Y-m-d");
			$yesterdayDate = date("Y-m-d", time() - 60 * 60 * 24);

			/*
				Getting statistics for selected campaign
			*/
			$sinceLastActionLeads = $se->getCampaignLeads($cid, $startDate, $todayDate, $sendTime, $sendTime);
			$currentMonthLeads = $se->getCampaignLeads($cid, $currentMonth, $todayDate, "00", $sendTime);
			$totalLeads = $se->getCampaignLeads($cid);

			$budgetCurrentMonth = $currentMonthLeads * $leadRate;

			if($startDate){

        		$fileName = $se->prepareData($cid, $startDate, $endDate, "", $sendTime, $sendTransp);

                /*
                	Send if the day is correct
                	and the time is correct
                	and that there is an email registered with the campaign
                */
                if($sendTime == $currentTime && $startDate && $clientEmail != ""){
                    $se->sendEmail($cid, $clientEmail, $pmEmail, $startDate, $pmName, $pmPhone, $clientName, $campaignName, $fileName, 1, $currentMonth, $todayDate, $todayDate, $sinceLastActionLeads, $currentMonthLeads, $budgetCurrentMonth, $sendTime, $totalLeads);

					/*
						Send email to secondary mail address.
						The mail is sent without the client name in it "Hello XXXXX" and the project manager is not in CC
					*/
					if($clientEmailSecondary != "")
						$se->sendEmail($cid, $clientEmailSecondary, $pmEmail, $startDate, $pmName, $pmPhone, "", $campaignName, $fileName, 0, $currentMonth, $todayDate, $todayDate, $sinceLastActionLeads, $currentMonthLeads, $budgetCurrentMonth, $sendTime, $totalLeads);

					/*
						Send email to third mail address.
						The mail is sent without the client name in it "Hello XXXXX" and the project manager is not in CC
					*/
					if($clientEmailThird != "")
						$se->sendEmail($cid, $clientEmailThird, $pmEmail, $startDate, $pmName, $pmPhone, "", $campaignName, $fileName, 0, $currentMonth, $todayDate, $todayDate, $sinceLastActionLeads, $currentMonthLeads, $budgetCurrentMonth, $sendTime, $totalLeads);

					if($ftpHost != "" && $ftpUser != "" && $ftpPassword != ""){
						$this->sendToFTP($cid, $fileName);
					}
				}

                /*
                	Deleting CSV and the zip file used in the mail
                */
                unlink($fileName); // zip file
                unlink(str_replace("zip", "csv", $fileName)); // CSV file
            }
		}
	}

	/*
		Uploads the CSV to a FTP server
	*/
	public function sendToFtp($cid, $fileName){
		$ftp = new BC_FtpUploader();
		$ftp->uploadToFtp($cid, $fileName);
	}

	/*
	Function not longer used
	
    public function sendEmail($cid, $clientMail, $pmEmail, $startDate, $pmName, $pmPhone, $clientName, $campaignName, $fileName, $ccToPM, $currentMonth, $todayDate, $yesterdayDate, $sinceLastActionLeads, $currentMonthLeads, $budgetCurrentMonth, $sendTime, $totalLeads) {

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
		$m->setViewParam('yesterdayDate',$startDate);
		$m->setViewParam('sinceLastActionLeads',$sinceLastActionLeads);
		$m->setViewParam('currentMonthLeads',$currentMonthLeads);
		$m->setViewParam('budgetCurrentMonth',$budgetCurrentMonth);
		$m->setViewParam('sendTime',$sendTime);
		$m->setViewParam('totalLeads',$totalLeads);

		$m->addTo($clientMail);
		$m->addBcc("test@baseandco.com");

		if($ccToPM == 1)
			$m->addCc($pmEmail);

		$m->addAttachment($attachment);
        $m->sendHtmlTemplate('statistics.phtml');
    }
    */
}
