<?php
/*
---------------------------------------------------------------------------------------------------------------------------------------------------
---  The following API methods get used in this example:                                                                                        ---
---     • GetCampaigns                  https://www.eworx.at/doku/getcampaigns/                                                                 ---
---     • CopyCampaign                  https://www.eworx.at/doku/copycampaign/                                                                 ---
---     • UpdateCampaign                https://www.eworx.at/doku/updatecampaign/                                                               ---
---------------------------------------------------------------------------------------------------------------------------------------------------
*/

namespace eworxMarketingSuite;

//This class will show you how a campaign can be created and updated in mailworx.
class CampaignCreator {
    private $serviceAgent;
        
    function __construct($serviceAgent) {
        $this->serviceAgent = $serviceAgent;
    }

    /*
    /// Description: Creates a campaign in mailworx.
	/// Parameter profileId: The profile id that should be used for the campaign.
	/// Returns: KeyValuePair where the key is the template id and the value is the created campaign id.*/
    public function createCampaign($profileId, $campaignName) {
        // Load the original campaign.
        $originalCampaign = $this->loadCampaign($campaignName);
        $data = null;

        if (!is_null($originalCampaign)) {
            if ($originalCampaign->Name == $campaignName) {

                // Copy the original campaign
                $copyCampaign = $this->copyCampaign($originalCampaign->Guid);

                // Update the sender, profile, ....
                if ($this->updateCampaign($copyCampaign, $profileId, $campaignName)) {
                    $data = array ('campaignId' => $copyCampaign->Guid,
                                  'templateId' => $copyCampaign->TemplateGuid);
                }
            } 
            else {
                // Return the already existing campaign.
                $data = array('campaignId' => $originalCampaign->Guid,
                              'templateId'=> $originalCampaign->TemplateGuid);
            }
        }

        return $data;
    }
        
    // Description: Updates the given campaign (name, senderAddress, senderName, subject...)
    // Returns true if the update is succesfull.
    private function updateCampaign($campaignToUpdate, $profileId, $campaignName) {
        // Every value of type string in the UpdateCampaignRequest must be assigned, otherwise it will be updated to the default value (which is string.Empty).

        $updateCampaignRequest = $this->serviceAgent->createRequest('UpdateCampaign');
        $updateCampaignRequest->setProperty('CampaignGuid', $campaignToUpdate->Guid);
        $updateCampaignRequest->setProperty('ProfileGuid', $profileId);
        $updateCampaignRequest->setProperty('Name', $campaignName);
        $updateCampaignRequest->setProperty('SenderAddress', 'service@mailworx.info');
        $updateCampaignRequest->setProperty('SenderName', 'mailworx Service Crew');
        $updateCampaignRequest->setProperty('Subject', 'My first Newsletter');

        return !is_null($updateCampaignRequest->getData());
    }

    // Description: Copies a campaign.
    // Parameter: campaignId: The id of the campaign.
    // Returns a copy of the given campaign.
    private function copyCampaign($campaignId) {
        $copyCampaignRequest = $this->serviceAgent->createRequest('CopyCampaign');
        $copyCampaignRequest->setProperty('CampaignToCopy', $campaignId); // The campaign which should be copied.

        $copyCampaignResponse =  $copyCampaignRequest->getData();

        if (is_null($copyCampaignResponse)) {
            return null;
        } 
        else {
            return $this->loadCampaign(null, $copyCampaignResponse->NewCampaignGuid);
        }
    }

    // Description: Loads the campaign with the specified id.
    // Parameter: campaignName: The name of the campaign.
    // Returns the campaign according to the campaign name.
    private function loadCampaign($campaignName, $campaignId = null) {
        $campaignRequest =$this->serviceAgent->createRequest('GetCampaigns');
        $campaignRequest->setProperty('Type', \eworxMarketingSuite\CampaignType::IN_WORK);

        if (is_null($campaignId)) { // If there is no campaign id given, then load the campaign by its name.
            $campaignsResponse = $campaignRequest->getData();
            $existingCampaign = null;
            foreach ($campaignsResponse->Campaigns as $campaign) {
                if (strcasecmp($campaign->Name, $campaignName) == 0) {
                    $existingCampaign = $campaign;
                    break;
                }
            }
        } else { // If there is a campaign id given, then load the campaign by its id.
            $campaignRequest->setProperty('Id', $campaignId);
            $campaignsResponse = $campaignRequest->getData();
                
            if (is_null($campaignsResponse) || count($campaignsResponse->Campaigns) == 0) {
                return null;
            } 
            else {
                return $campaignsResponse->Campaigns[0];
            }
        }

        return $existingCampaign;
    }
}
