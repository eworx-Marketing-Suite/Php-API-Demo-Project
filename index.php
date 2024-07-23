<pre><?php
/*
---------------------------------------------------------------------------------------------------------------------------------------------------
---  This is a sample implementation for using the mailworx API in order to create and send an email campaign over mailworx.                    ---
---  Be aware of the fact that this example might not work in your mailworx account.                                                            ---
---																																				---
---  The following API methods get used in this example:                                                                                        ---
---     • GetProfiles                   https://www.eworx.at/doku/getprofiles                                                                   ---
---     • GetSubscriberFields           https://www.eworx.at/doku/getsubscriberfields/                                                          ---
---     • ImportSubscribers             https://www.eworx.at/doku/importsubscribers                                                             ---
---     • GetCampaigns                  https://www.eworx.at/doku/getcampaigns/                                                                 ---
---     • CopyCampaign                  https://www.eworx.at/doku/copycampaign                                                                  ---
---     • UpdateCampaign                https://www.eworx.at/doku/updatecampaign/                                                               ---
---     • GetSectionDefinitions         https://www.eworx.at/doku/getsectiondefinitions                                                         ---
---     • CreateSection                 https://www.eworx.at/doku/createsection                                                                 ---
---     • SendCampaign                  https://www.eworx.at/doku/sendcampaign/                                                                 ---
---     • GetMDBFiles                   https://www.eworx.at/doku/getmdbfiles/                                                                  ---
---     • UploadFileToMDB               https://www.eworx.at/doku/uploadfiletomdb/                                                              ---
---                                                                                                                                             ---
---   This is a step by step example:                                                                                                           ---
---     1. Preparation                                                                                                                          ---
---     2. Import the subscribers into mailworx                                                                                                 ---
---     3. Create a campaign                                                                                                                    ---
---     4. Add sections to the campaign                                                                                                         ---
---     5. Send the campaign to the imported subscribers                                                                                        ---
---     6. Read campaign statistic data                                                                                                         ---
---------------------------------------------------------------------------------------------------------------------------------------------------
*/

include_once 'Classes/Importer.php';
include_once 'Classes/CampaignCreator.php';
include_once 'Classes/SectionCreator.php';
include_once 'Classes/Constants.php';
include_once 'Classes/ReadCampaignStatistic.php';
include_once 'mx_rest_api.php';

// Set  the login data.  
$serviceAgent = new \eworxMarketingSuite\EmsServiceAgent();

// Url to the eMS service.
// Not required, default value "https://mailworx.marketingsuite.info/Services/JSON/ServiceAgent.svc"
    
// ### STEP 1 : Preparation ###           

$serviceAgent->useLanguage("EN");     // Language of the text values ​​returned. Not required, default value is "EN"
$serviceAgent->useCredentials(
        "[ACCOUNT]",        // account name (Mandant) of the eMS to login
        "[USERNAME]",       // user name to use to login
        "[PASSWORD]",       // the user's password
        "[APPLICATION]"     // the name of the registered application
);

$campaignName = '[CAMPAIGN_NAME]'; // Set the campaign name here.
$profileName = '[PROFILE_NAME]'; // Set the profile name here.

// ### STEP 1 : Preparation ###

// ### STEP 2 : IMPORT ###

// Here we use a helper class in order to do all the necessary import steps.
$importer = new \eworxMarketingSuite\Importer($serviceAgent);

// The key is the id of the profile where the subscribers have been imported to.
// The value is a list of ids of the imported subscribers.
$importedData = $importer->importSubscribers($profileName);

// ### STEP 2 : IMPORT ###
if (!is_null($importedData) && count($importedData['importedSubscribers']) > 0) {

    // ### STEP 3 : CREATE CAMPAIGN ###
    // Here we use another helper class in order to do all the necessary steps for creating a campaign.
    $campaignCreator = new \eworxMarketingSuite\CampaignCreator($serviceAgent);
    // The key is the id of the template.
    // The value is the id of the campaign.

    $data = $campaignCreator->createCampaign($importedData['profileId'], $campaignName);
  
    // ### STEP 3 : CREATE CAMPAIGN ###

    // If a campaign was returned we can add the sections.
    if (!is_null($data)) {
        // ### STEP 4 : ADD SECTIONS TO CAMPAIGN ###
        // Here we use another helper class in order to do all the necessary steps for adding sections to the campaign.
        $sectionCreator = new \eworxMarketingSuite\SectionCreator($serviceAgent);

        // Send the campaign, if all sections have been created.
        if ($sectionCreator->generateSection($data['templateId'], $data['campaignId'])) {
            // ### STEP 4 : ADD SECTIONS TO CAMPAIGN ###

            // ### STEP 5 : SEND CAMPAIGN ###

            $sendCampaignRequest = $serviceAgent->createRequest('SendCampaign');
            $sendCampaignRequest->setProperty('CampaignId', $data['campaignId']);
            $sendCampaignRequest->setProperty('IgnoreCulture', false); // Send the campaign only to subscribers with the same language as the campaign
            $sendCampaignRequest->setProperty('SendType', \eworxMarketingSuite\SendType::MANUAL);
            // If the SendType is set to Manual, ManualSendSettings are needed
            // If the SendType is set to ABSplit, ABSplitTestSendSettings are needed

            $sendCampaignRequest->setProperty('Settings', array(
                '__type' => \eworxMarketingSuite\Constants::MANUAL_SEND_SETTINGS_TYPE,
                'SendTime' => $sendCampaignRequest->getTime(strtotime(date('Y-m-d H:i:s')))
            ));

            $sendCampaignRequest->setProperty('UseIRated', false); // Here is some more info about iRated https://www.eworx.at/doku/so-funktioniert-irated/
            $sendCampaignRequest->setProperty('UseRTR', true);
            $sendCampaignResponse = $sendCampaignRequest->getData();

            // ### STEP 5 : SEND CAMPAIGN ###

            if(is_null($sendCampaignResponse)) {
               echo 'Something went wrong';
            }
            else{
               echo 'Effective subscribers: '.$sendCampaignResponse->RecipientsEffective;

               // ### STEP 6 : READ CAMPAIGN STATISTICS ###

               $statisticReader = new \eworxMarketingSuite\ReadCampaignStatistic($serviceAgent);
               $statisticReader->readCampaignStatistics();

               // ### 6 : READ CAMPAIGN STATISTICS ###
            }
        }
    }
}


?></pre>
<!DOCTYPE html>
<html>
<head>
    <style>
        html {
            font: 14px/1em sans-serif;
        }

        pre {
            border: 1px solid #ccc;
            padding: 10px;
            background: #eee;
        }

        .buttons a {
            display: inline-block;
            margin-right: 5px;
            background: silver;
            color: #fff;
            padding: 5px;
        }
    </style>
    <div>
        
    </div>
</head>
