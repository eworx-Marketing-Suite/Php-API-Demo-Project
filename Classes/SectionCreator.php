<?php
/*
---------------------------------------------------------------------------------------------------------------------------------------------------
---  The following API methods get used in this example:                                                                                        ---
---     • CreateSection                 https://www.eworx.at/doku/createsection/                                                                ---
---     • GetSectionDefinitions         https://www.eworx.at/doku/getsectiondefinitions/                                                        ---
---     • GetMDBFiles                   https://www.eworx.at/doku/getmdbfiles/                                                                  ---
---     • UploadFileToMDB               https://www.eworx.at/doku/uploadfiletomdb/                                                              ---
---------------------------------------------------------------------------------------------------------------------------------------------------
*/

namespace eworxMarketingSuite;

include_once 'Constants.php';
include_once './mx_rest_api.php';

// This class will show you how sections can be added to a campaign in eMS.
class SectionCreator {
    private $serviceAgent;
    private $json;
        
    function __construct($serviceAgent) {
        $this->serviceAgent = $serviceAgent;
        $this->json = new \eworxMarketingSuite\JSON();
    }

    // Description: Generates the section for the given template into the given campaign.
    // Parameter templateId: The template Id.
    // Parameter campaignId:The campaign Id.</param>
    // Returns: Whether the sections have been created or not.*/
    public function generateSection($templateId, $campaignId) {
        // Load all available section definitions for the given template
        $sectionDefinitions = $this->loadSectionDefinition($templateId);
        $sectionCreated = false;

        // There are different types of fields which can be used. Have a look at the constants class.

        // If there are no section definitions we can't setup the campaign.
        if (!is_null($sectionDefinitions) && count($sectionDefinitions) > 0) {
            $sectionCreated = true;
            // Right here we create three different sample sections for our sample campaign.

            // Load the section definition that defines an article
            $defintionArticle = $this->loadSectionDefinitionByName('Artikel', $sectionDefinitions);
            if (!is_null($defintionArticle)) {
                $createSectionArticleRequest = $this->serviceAgent->createRequest('CreateSection');
                $createSectionArticleRequest->setProperty('Campaign', array(
                    '__type' => \eworxMarketingSuite\Constants::CAMPAIGN_TYPE,
                    'Guid' => $campaignId
                ));
                $createSectionArticleRequest->setProperty('Section', $this->createArticleSection($defintionArticle));

                // ### CREATE THE SECTION ###

                $createSectionArticleResponse = $createSectionArticleRequest->getData();

                // ### CREATE THE SECTION ###

                $sectionCreated =  $sectionCreated && !is_null($createSectionArticleResponse) && !is_null($createSectionArticleResponse->Guid);
            }
               
            // Load the sector definition that defines a two column.
            $definitionTwoColumns = $this->loadSectionDefinitionByName('2 Spaltiger Beitrag', $sectionDefinitions);
            if (!is_null($definitionTwoColumns)) {
                $createSectionTwoColumnsRequest = $this->serviceAgent->createRequest('CreateSection');
                $createSectionTwoColumnsRequest->setProperty('Campaign', array(
                  '__type' => \eworxMarketingSuite\Constants::CAMPAIGN_TYPE,
                  'Guid' => $campaignId
                ));
                $createSectionTwoColumnsRequest->setProperty('Section', $this->createTwoColumnSection($definitionTwoColumns));

                $createSectionTwoColumnsResponse = $createSectionTwoColumnsRequest->getData();

                $sectionCreated = $sectionCreated && !is_null($createSectionTwoColumnsResponse) && !is_null($createSectionTwoColumnsResponse->Guid);
            }
                
            // Load the section that defines a banner.
            $definitionBanner = $this->loadSectionDefinitionByName('banner', $sectionDefinitions);
            if (!is_null($definitionBanner)) {
                $createSectionBannerRequest = $this->serviceAgent->createRequest('CreateSection');
                $createSectionBannerRequest->setProperty('Campaign', array(
                  '__type' => \eworxMarketingSuite\Constants::CAMPAIGN_TYPE,
                  'Guid' => $campaignId
                ));
                $createSectionBannerRequest->setProperty('Section', $this->createBannerSection($definitionBanner));

                $createSectionBannerResponse = $createSectionBannerRequest->getData();
                $sectionCreated = $sectionCreated && !is_null($createSectionBannerResponse) && !is_null($createSectionBannerResponse->Guid);
            }
        }

        return $sectionCreated;
    }

    // Description: Creates an article section.
    // Parameter: The section definition.
    // Returnes the created article section.
    private function createArticleSection($definitionArticle) {
        /*
         * Beware when setting field values: Please send new field-objects and ansure that the InternalName of the field contains the same value than the original field...
         * The different field types use OO paradigms and define the kind of value in the field. Multi-Text-Line, Single-Text-Line, True/False settings etc. They are like datatypes in programming languages.
         * The InternalName and the fieldtype has to match and they define at which field in the section the value will be entered.
         * The fields are defined by the eMS Template (=Layout of the email) and the defined fields there.
         */
        $fieldsToAdd = array();

        // ### BUILD UP THE SECTION ###
        foreach ($definitionArticle->Fields as $field) {
            if (strcasecmp($field->InternalName, 'a_text') == 0) {
                array_Push($fieldsToAdd, array(
                  '__type' => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                  'InternalName' => $field->InternalName,
                  // Beware single quotes do not work for attributes in HTML tags.
                  // If you want to use double quotes for your text, you must use them HTML-encoded.
                  // A text can only be linked with <a> tags and a href attributes. E.g.: <a href=""www.mailworx.info"">go to mailworx website</a>
                  'UntypedValue'=> 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy "eirmod tempor" invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et <a href="www.mailworx.info">justo</a> duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo dup dolores et ea rebum.  <a href="http://sys.mailworx.info/sys/Form.aspx?frm=4bf54eb6-97a6-4f95-a803-5013f0c62b35">Stet</a> clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
                ));
            } 
            elseif (strcasecmp($field->InternalName, 'a_img') == 0) {
                // Upload the file from the given path to the eMS media data base.
                $file =  $this->uploadFile("./Assets/email.png", "email.png");
                if (!is_null($file)) {
                    array_Push($fieldsToAdd, array(
                       '__type' => \eworxMarketingSuite\Constants::MDB_FIELD_TYPE,
                       'InternalName' => $field->InternalName,
                       'UntypedValue' => $file
                    ));
                }
            } 
            elseif (strcasecmp($field->InternalName, 'a_hl') == 0) {
                 array_Push($fieldsToAdd, array(
                     '__type' => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                     'InternalName' => $field->InternalName,
                     'UntypedValue' => '[%mwr:briefanrede%]'
                 ));
            }
        }

        return array(
            '__type' => \eworxMarketingSuite\Constants::SECTION_TYPE,
            'Created' => $this->json->getTime(date("Y-m-d H:i:s")),
            'SectionDefinitionName' => $definitionArticle->Name,
            'StatisticName' => 'my first article',
            'Fields' => $fieldsToAdd
        );
        // ### BUILD UP THE SECTION ###

    }

    // Description: Creates a two column section.
    // Parameter: The two column definition.
    // Returnes the created two column section.
    private function CreateTwoColumnSection($definitionTwoColumns) {
        $fieldsToAdd = array();
        foreach ($definitionTwoColumns->Fields as $field) {
            if (strcasecmp($field->InternalName, 'c2_l_img') == 0) {
                $file =  $this->uploadFile("./Assets/logo.png", "logo.png");
                if (!is_null($file)) {
                    array_Push($fieldsToAdd, array(
                       '__type' => \eworxMarketingSuite\Constants::MDB_FIELD_TYPE,
                       'InternalName' => $field->InternalName,
                       'UntypedValue' => $file
                    ));
                }
            } 
            elseif (strcasecmp($field->InternalName, 'c2_l_text') == 0) {
                array_Push($fieldsToAdd, array(
                        '__type' => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                        'InternalName' => $field->InternalName,
                        'UntypedValue' => 'Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. 
                        Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto ignissim,
                        qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.'
                ));
            } 
            elseif (strcasecmp($field->InternalName, 'c2_r_img') == 0) {
                 $file =  $this->uploadFile("./Assets/events.png", "events.png");
                if (!is_null($file)) {
                    array_Push($fieldsToAdd, array(
                       '__type' => \eworxMarketingSuite\Constants::MDB_FIELD_TYPE,
                       'InternalName' => $field->InternalName,
                       'UntypedValue' => $file
                    ));
                }
            } 
            elseif (strcasecmp($field->InternalName, 'c2_r_text') == 0) {
                array_Push($fieldsToAdd, array(
                        '__type' => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                        'InternalName' => $field->InternalName,
                        'UntypedValue' => 'Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit,
                        qsed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo.'
                ));
            }
        }
           
        return array(
          '__type' => \eworxMarketingSuite\Constants::SECTION_TYPE,
          'Created' => $this->json->getTime(date("Y-m-d H:i:s")),
          'SectionDefinitionName' => $definitionTwoColumns->Name,
          'StatisticName' => 'section with two columns',
          'Fields' => $fieldsToAdd
        );
    }

    // Description: Creates a banner section.
    // Parameter: The banner definition.
    // Returnes the created banner section.
    private function createBannerSection($definitionBanner) {
        $fieldsToAdd = array();
        foreach ($definitionBanner->Fields as $field) {
            if (strcasecmp($field->InternalName, 't_img') == 0) {
                $file =  $this->uploadFile("./Assets/logo.png", "eMS-logo.png");
                    
                if (!is_null($file) ) {
                    array_Push($fieldsToAdd, array(
                       '__type' => \eworxMarketingSuite\Constants::MDB_FIELD_TYPE,
                       'InternalName' => $field->InternalName,
                       'UntypedValue' => $file
                    ));
                }
            } elseif (strcasecmp($field->InternalName, 't_text') == 0) {
                array_Push($fieldsToAdd, array(
                        '__type' => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                        'InternalName' => $field->InternalName,
                        'UntypedValue' => 'Developed in the <a href="http://www.mailworx.info/en/">mailworx</a> laboratory the intelligent and auto-adaptive algorithm <a href="http://www.mailworx.info/en/irated-technology>iRated®</a>
                                             brings real progress to your email marketing. It is more than a target group oriented approach.
                                             iRated® sorts the sections of your emails automatically depending on the current preferences of every single subscriber.
                                             This helps you send individual emails even when you don\'t know much about the person behind the email address.'
                        ));
            }
        }

        return array(
          '__type' => \eworxMarketingSuite\Constants::SECTION_TYPE,
          'Created' => $this->json->getTime(date("Y-m-d H:i:s")),
          'SectionDefinitionName' => $definitionBanner->Name,
          'StatisticName' => 'banner',
          'Fields' => $fieldsToAdd
        );
    }

    // Description: Searches a file by its name.
    // Parameter fileName: The file name.
    // Parameter files: The files it should search.
    // Returnes the id of the file if its found.
    private function searchFileByName($fileName, $files) {
        foreach ($files as $file) {
            if (strcasecmp($file->Name, $fileName) == 0) {
                return $file->Id;
            }
        }
        return null;
    }

    // Description: Loads the section definitions for the given template id.
    // Parameter sectionDefinitionName: The name of the section definition.
    // Parameter sectionDefinitions: The section definitions.
    // Returnes the section definition if it finds one with the according sectionDefinitionName.
    private function loadSectionDefinitionByName($sectionDefinitionName, $sectionDefinitions) {
        foreach ($sectionDefinitions as $sectiondefinition) {
            if (strcasecmp($sectiondefinition->Name, $sectionDefinitionName) == 0) {
                return $sectiondefinition;
            }
        }

        return null;
    }

    // Description: Gets all section definitions of a template.
    // Parameter: The id of the template.
    // Returnes the sections definitions of the given template.
    private function loadSectionDefinition($templateId) {
        $sectionDefinitionRequest = $this->serviceAgent->createRequest('GetSectionDefinitions');
        $sectionDefinitionRequest->setProperty('Template',
        array(
            "__type" => \eworxMarketingSuite\Constants::TEMPLATE_TYPE,
            "Guid" => $templateId
        ));

        /* ### DEMONSTRATE SECTION DEFINITION STRUCTURE ###
        Here we use the console application in order to demonstrate the structure of each section definition.
        You need to know the structure in order to be able to create sections on your own.*/

        $sectionDefinitionResponse = $sectionDefinitionRequest->getData();
        if (is_null($sectionDefinitionResponse)) {
            return null;
        } else {
            echo '<div>-------------------------------Section definitions----------------------<br/>';
            for ($i=0; $i < count($sectionDefinitionResponse->SectionDefinitions); $i++) {
                $currentSectionDefinition = $sectionDefinitionResponse->SectionDefinitions[$i];
                echo '<div style="margin-left:20px">+++++++++++++++ Section definition '.($i+1).' +++++++++++++++</div>';
                echo '<div style="margin-left:20px">Name:'.$currentSectionDefinition->Name.'</div>';

                if (count($currentSectionDefinition->Fields) > 0) {
                    for ($j=0; $j < count($currentSectionDefinition->Fields); $j++) {
                        $currentField = $currentSectionDefinition->Fields[$j];
                        $endOftype =  strrpos($currentField->__type, ':');
                        $typeName = substr( $currentField->__type, 0, $endOftype);
                        echo '<div style="margin-left:40px">*********** Field '.($j+1).' ***********</div>';
                        echo '<div style="margin-left:40px">Name: '.$currentField->InternalName.'</div>';
                        echo '<div style="margin-left:40px">Type: '.$typeName.'</div>';

                        if (strcasecmp($typeName, 'SelectionField') == 0) {
                            echo '<div style="margin-left:60px">Selections:</div>';
                            for ($k=0; $k < count($currentField->SelectionObjects); $k++) {
                                 $currentSelection = $currentField->SelectionObjects[$k];
                                 echo '<div style="margin-left:80px">Name: '.$currentSelection->Caption.'</div>';
                                 echo '<div style="margin-left:80px">Value: '.$currentSelection->InternalName.'</div>';
                            }
                        }
                        echo'<div style="margin-left:40px">********************************</div>';
                    }
                } 
                else {
                    echo '<div style="margin-left:20px">No fields found</div>';
                }
                echo '<div style="margin-left:20px">++++++++++++++++++++++++++++++++++++++++++++++++++++</div>';
            }
            echo '------------------------------------------------------------------------</div>';
            // ### DEMONSTRATE SECTION DEFINITION STRUCTURE ###
            return $sectionDefinitionResponse->SectionDefinitions;
        }
    }

    // Description: Uploads a file to the eMS media data base.
    // Parameter path: The path where the file to upload is located.
    // Parameter fileName: Name of the file to upload.
    // Returnes the id of the uploaded file.
    private function uploadFile($path, $fileName) {
        // Get all files in the mdb for the directory mailworx.
        $getMdbFilesRequest = $this->serviceAgent->createRequest('GetMDBFiles');
        $getMdbFilesRequest->setProperty('Path', 'marketing-suite-php');
        $getMdbFilesResponse = $getMdbFilesRequest->getData();
     
        if (is_null($getMdbFilesResponse) || is_null($this->searchFileByName($fileName, $getMdbFilesResponse->{'<Files>k__BackingField'}))) {
            // The file we want to upload
            $handle = fopen($path, "rb");
            $fsize = filesize($path);
            $contents = fread($handle, $fsize);
            $byteArray = array_values(unpack("C*", $contents));

            // Send the data to eworxMarketingSuite
            $fileUploadRequest = $this->serviceAgent->createRequest('UploadFileToMDB');
            $fileUploadRequest->setProperty('File', $byteArray); // The picture as byte array.
            $fileUploadRequest->setProperty('Path', 'marketing-suite-php'); // The location within the mailworx media database. If this path does not exist within the media data base, an exception will be thrown.
            $fileUploadRequest->setProperty('Name', $fileName);  // The name of the file including the file extension.
            $fileUploadResponse = $fileUploadRequest->getData();

            if (!is_null($fileUploadResponse)) {
                return $fileUploadResponse->{'<Id>k__BackingField'};
            }
        } 
        else {
            return $this->searchFileByName($fileName, $getMdbFilesResponse->{'<Files>k__BackingField'});
        }
    }
}
