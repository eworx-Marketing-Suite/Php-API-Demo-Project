<?php
/*
---------------------------------------------------------------------------------------------------------------------------------------------------
---  The following API methods get used in this example:                                                                                        ---
---     • GetCampaigns                  https://www.eworx.at/doku/getcampaigns/                                                                 ---
---     • GetCampaignStatistics         https://www.eworx.at/doku/campaignstatistics/                                                           ---
---     • GetBouncesOfCampaign          https://www.eworx.at/doku/campaignstatistics/                                                           ---
---     • GetClickRatesOfCampaign       https://www.eworx.at/doku/campaignstatistics/                                                           ---
---     • GetOpeningRatesOfCampaign     https://www.eworx.at/doku/campaignstatistics/                                                           ---
---------------------------------------------------------------------------------------------------------------------------------------------------
*/

namespace eworxMarketingSuite;
 
include_once './mx_rest_api.php';

//This class will show you how campaign statistics can be read.
class ReadCampaignStatistic {
    private $serviceAgent;
    private $json;
        
    function __construct($serviceAgent) {
        $this->serviceAgent = $serviceAgent;
        $this->json = new \eworxMarketingSuite\JSON();
    }

    // Description: Prints campaign statistics to the console.
    public function readCampaignStatistics() {
        $campaignId = '[CAMPAIGN_ID]';

        $campaignsRequest = $this->serviceAgent->createRequest('GetCampaigns');
        $campaignsRequest->setProperty('Id', $campaignId);
        $campaignsRequest->setProperty('ResponseDetail', 1);

        // ResponseDetail = CampaignResponseDetailInfo.BasicInformation -> Get almost all details of the campaign type (without links and sections).
        // ResponseDetail = CampaignResponseDetailInfo.Sections -> Also gets the sections of the campaign.
        // ResponseDetail = CampaignResponseDetailInfo.Links -> Also gets the links of the campaign.
        // ResponseDetail = CampaignResponseDetailInfo.SectionProfiles -> Also gets info about which sections are restricted to which target groups.

        $campaignsResponse = $campaignsRequest->getData();
        $campaign = $campaignsResponse->Campaigns[0];

        $this->printGeneralCampaignInfo($campaign);
        $this->printStatistics($campaign);
        $this->printBouncesInfo($campaign);
        $this->printClickratesInfo($campaign);
        $this->printOpeningRatesInfo($campaign);
    }

    // Description: Prints the campaign info to the console.
    // Parameter campaign: The campaign to print to the console.
    private function printGeneralCampaignInfo($campaign) {
        $json = new \eworxMarketingSuite\JSON();
        echo '<div>General info of '.$campaign->Name.', created '.$json->getJSONTime($campaign->Created).'</div>';
        echo '**********************************************************************************************';
        echo '<div>Subject: '.$campaign->Subject.'</div>';
        echo '<div>Template name: '.$campaign->TemplateName.'</div>';
        echo '<div>Sender: '.$campaign->SenderAddress.'</div>';
        echo '<div>Profile name: '.$campaign->ProfileName.'</div>';
        echo '<div>Culture: '.$campaign->Culture.'</div>';
        echo '<div>Notify address: '.$campaign->NotifyAddress.'</div>';
        echo '**********************************************************************************************';
        echo '';
    }

    // Description: Prints the campaign statistics to the console.
    // Parameter campaign: The campaign to print to the console.
    private function printStatistics($campaign) {
        $statisticsRequest = $this->serviceAgent->createRequest('GetCampaignStatistics');
        $statisticsRequest->setProperty('CampaignGuid', $campaign->Guid);

        $statisticsResponse = $statisticsRequest->getData();

        echo '<div>Campaign statstics of '.$campaign->Name.'</div>';
        echo '**********************************************************************************************';
        echo '<div>Sent mails: '.$statisticsResponse->TotalMails.'</div>';
        echo '<div>Opened mails: '.$statisticsResponse->OpenedMails.'</div>';
        echo '<div>Bounce mails: '.$statisticsResponse->BounceMails.'</div>';
        echo '<div>Amount of clicks: '.$statisticsResponse->Clicks.'</div>';
        echo '**********************************************************************************************';
        echo '';
    }

    // Description: Prints the bounces info to the console.
    // Parameter campaign: The campaign to print to the console.
    private function printBouncesInfo($campaign) {
        $bounceRequest = $this->serviceAgent->createRequest('GetBouncesOfCampaign');
        $bounceRequest->setProperty('CampaignGuid', $campaign->Guid);

        $bounceInfo = $bounceRequest->getData();

        echo '<div>Bounces statistics of '.$campaign->Name.'</div>';
        echo '**********************************************************************************************';
        echo '<div><Subscribers:/div>';
        foreach ($bounceInfo->Subscribers as $subscriber) {
            echo '<div>Guid of the subscriber: '.$subscriber->Guid.'</div>';
        }
        echo '**********************************************************************************************';
        echo '';
    }

    // Description: Prints the click rates info to the console.
    // Parameter campaign: The campaign to print to the console.
    private function printClickratesInfo($campaign) {
        $clickRatesRequest = $this->serviceAgent->createRequest('GetClickRatesOfCampaign');
        $clickRatesRequest->setProperty('CampaignGuid', $campaign->Guid);

        $clickRatesInfo = $clickRatesRequest->getData();

        echo '<div>Click rates statistics of '.$campaign->Name.'</div>';
        echo '**********************************************************************************************';
        echo '<div>Clicked links:</div>';
        foreach ($clickRatesInfo->ClickedLinks as $statisticLink) {
            echo '<div>Linkname: '.$statisticLink->LinkName.'</div>';
            echo '<div>Clicks: '.$statisticLink->Clicks.'</div>';
            echo '<div>Url: '.$statisticLink->Url.'</div>';
            echo '<div>------------------------------------------------------</div>';
            echo '';
        }
        echo '**********************************************************************************************';
        echo '';
    }

    // Description: Prints the opening rates to the console.
    // Parameter campaign: The campaign to print to the console.
    private function printOpeningRatesInfo($campaign) {
        $json = new \eworxMarketingSuite\JSON();

        $openingRatesRequest = $this->serviceAgent->createRequest('GetOpeningRatesOfCampaign');
        $openingRatesRequest->setProperty('CampaignGuid', $campaign->Guid);

        $openingRatesInfo = $openingRatesRequest->getData();

        echo '<div>Opening rates statistics of '.$campaign->Name.'</div>';
        echo '**********************************************************************************************';
        echo '<div>Opened mails:</div>';
        foreach($openingRatesInfo->Openings as $openedMailInfo) {
            echo '<div>State: '.$openedMailInfo->ReadingState.'</div>';
            echo '<div>Opened at: '.$json->getJSONTime($openedMailInfo->OpenedAt).'</div>';
            echo '<div>Reading state specified: '.$openedMailInfo->ReadingStateSpecified.'</div>';
            echo '<div>------------------------------------------------------</div>';
        }
        echo '**********************************************************************************************';
        echo '';
    }
}
