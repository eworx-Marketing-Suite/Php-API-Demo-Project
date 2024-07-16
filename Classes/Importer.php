<?php
/*
---------------------------------------------------------------------------------------------------------------------------------------------------
---  The following API methods get used in this example:                                                                                        ---
---     • ImportSubscribers             https://www.eworx.at/doku/importsubscribers/                                                            ---
---     • GetSubscriberFields           https://www.eworx.at/doku/getsubscriberfields/                                                          ---
---     • GetProfiles                   https://www.eworx.at/doku/getprofiles/                                                                  ---
---------------------------------------------------------------------------------------------------------------------------------------------------
*/
 
namespace eworxMarketingSuite;

include_once 'Constants.php';

//This class will show you how subscribers can be imported into mailworx.
class Importer {
    private $serviceAgent;
    function __construct($serviceAgent) {
        $this->serviceAgent = $serviceAgent;
    }

    // Description:Imports the subscribers. 
    // Parameter profileName: The profile name of the subscriber.
    // Returns: Returns a KeyValuePair. The key is the profile id and the value is a list of ids of the imported subscribers.*/ 
    public function importSubscribers($profileName) {
        $importSubscribersRequest = $this->serviceAgent->createRequest('ImportSubscribers');
       
        // ### HANDLE PROFILE ###
        // Here we handle the profile that will be used as target group later.
        // Load the profile with the given name from mailworx.
        $profile = $this->loadProfile($profileName);

        // If there is already a profile for the given name, all subscribers of this group have to be removed.
        if (!is_null($profile)) {
            // This action will take place before the import has started.
            $importSubscribersRequest ->setProperty('BeforeImportActions', array(array(
                    "__type" => \eworxMarketingSuite\Constants::CLEAR_PROFILE_ACTION_TYPE,
                    "Name" => $profileName
            )));
        }
        
        // This action will take place after the subscribers have been imported to mailworx.
        $postSubscriberAction = array(array(
            "__type" => \eworxMarketingSuite\Constants::PROFILE_ADDER_ACTION_TYPE,
            "Name" => $profileName, // A new profile will be created if no profile does exist for the given name in eworxMarketingSuite.
             // ExecuteWith => \eworxMarketingSuite\ExecuteWith::INSERT  Only subscribers which will be added as new subscribers will be assigned to the profile.
             // ExecuteWith => \eworxMarketingSuite\ExecuteWith::UPDATE  Only subscribers which already exist will be assigned to the profile.
             // ExecuteWith => \eworxMarketingSuite\ExecuteWith::INSERT | \eworxMarketingSuite\ExecuteWith::UPDATE  Every imported subscriber will be assigned to the profile.
            "ExecuteWith" => \eworxMarketingSuite\ExecuteWith::INSERT | \eworxMarketingSuite\ExecuteWith::UPDATE
        ));

        // ### HANDLE PROFILE ###

        // ### HANDLE IMPORT PROPERTIES ###
        // ### HANDLE LIST OF SUBSCRIBERS ###
        $importSubscribersRequest->setProperty('Subscribers', $this->getSubscribers());
        // ### HANDLE LIST OF SUBSCRIBERS ###
        $importSubscribersRequest->setProperty('PostSubscriberActions', $postSubscriberAction);
        $importSubscribersRequest->setProperty('DuplicateCriteria', 'email');
        // ### HANDLE IMPORT PROPERTIES ###

        // ### DO THE IMPORT ###
        $importSubscribersResponse = $importSubscribersRequest ->getData();
        // ### DO THE IMPORT ###

        // ### HANDLE THE IMPORT RESPONSE ###
        // Here we use our console application in order to show you the results/errors of the import response.
        echo '<div>-------------------------------Import result----------------------';
        echo '<div>Duplicates:'.$importSubscribersResponse->Duplicates.'<div>';
        echo '<div>Erros:'.$importSubscribersResponse->Errors.'<div>';
        echo '<div>Imported:'.$importSubscribersResponse->Imported.'<div>';
        echo '<div>Updated:'.$importSubscribersResponse->Updated.'<div>';

        $importedSubscriberIds = array();

        if (!is_null($importSubscribersResponse->FeedbackData) && count($importSubscribersResponse->FeedbackData)) {
            echo '<div>Feedback data<ul>';
               
            for ($i=0; $i < count($importSubscribersResponse->FeedbackData); $i++) {
                if (is_null($importSubscribersResponse->FeedbackData[$i]->Error)) {
                    array_push($importedSubscriberIds, $importSubscribersResponse->FeedbackData[$i]->AffectedSubscriber);
                    echo'<li>Email: '. $importSubscribersResponse->FeedbackData[$i]->UniqueId.', Id:'.$importSubscribersResponse->FeedbackData[$i]->AffectedSubscriber.'</li>';
                } else {
                    echo '<li>'.$importSubscribersResponse->FeedbackData[$i]->Error.'</li>';
                }
            }
            echo '</ul></div>';
        } else {
            echo 'No feedback data';
        }
        echo '------------------------------------------------------------------</div>';

        // If the profile did not exist at the the first iteration we can now load it.
        if (is_null($profile)) {
            $profile = $this->loadProfile($profileName);
        }
        // ### HANDLE THE IMPORT RESPONSE ###

        return array( 'profileId' => $profile->Guid,
                      'importedSubscribers' => $importedSubscriberIds);
    }

    // Description: Get some sample subscriber for the import.
    // Returns an array of subscribers.
    private function getSubscribers() {

        // We build some new sample subscribers here.

        // This is a new subscriber.
		// We set some meta data as well as some custom data for this subscriber.
        $subscriberDetail = array();
        $subscriberDetail["__type"] = \eworxMarketingSuite\Constants::SUBSCRIBER_TYPE;

        // Set the meta data field "OptIn". 
		// If set to true the subscriber will receive newsletters.
        $subscriberDetail["Optin"] = true;

        // Set the meta data field "Mailformat". 
		// \eworxMarketingSuite\Mailformat::MULTIPART ->  The subscriber will receive the newsletter as multipart format.
		// \eworxMarketingSuite\Mailformat::HTML    ->  The subscriber will receive the newsletter as HTML format. 
		// \eworxMarketingSuite\Mailformat::TEXT      ->  The subscriber will receive the newsletter as text format. 
        $subscriberDetail["Mailformat"] = \eworxMarketingSuite\Mailformat::MULTIPART;

        // Set the meta data field "Language".
		// This is the language of the subscriber.
		// If no value is specified here, the language of the security context will be used. 
        $subscriberDetail["Language"] = "EN";

        $ubscriberDetail["Status"] = \eworxMarketingSuite\SubscriberStatus::INACTIVE_IF_ACTIVE;

        // Here we set some custom data fields for this subscriber.

        // If you want to know which fields are available for your account, then call the following method: 
        $this->getFieldsOfAccount();

        // These are te different typ of fields which can be used. Have a look at the constants class.
        // We set some fields with different field types here, just to show how to do it right:       
        $subscriberDetail["Fields"] = array(array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "email", // A field with this internal name exists in every eworxMarketingSuite account.
                                                "UntypedValue" => "am@mailworx.info"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "firstname", // A field with this internal name exists in every eworxMarketingSuite account.
                                                "UntypedValue" => "mailworx"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "lastname", // A field with this internal name exists in every eworxMarketingSuite account.
                                                "UntypedValue" => "ServiceCrew"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::DATE_TIME_FIELD_TYPE,
                                                "InternalName" => "birthdate",
                                                "UntypedValue" => date("Y-m-d H:i:s")
                                            ),
                                            array( // A field of the type memo in eworxMarketingSuite is also a textfield
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "note",
                                                "UntypedValue" => "JustPutYourTextRightHere"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::SELECTION_FIELD_TYPE,
                                                "InternalName" => "interest",
                                                "UntypedValue" => "interest_politics, interest_economy"
                                                // You can use , or ; here to split the values.
                                                // White spaces don't matter either.
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::SELECTION_FIELD_TYPE,
                                                "InternalName" => "position",
                                                "UntypedValue" => "position_sales"
                                            ));

        $subscriberExample = array();
        $subscriberExample["__type"] = \eworxMarketingSuite\Constants::SUBSCRIBER_TYPE;
        $subscriberExample["Optin"] = false;
        $subscriberExample["Mailformat"] = \eworxMarketingSuite\Mailformat::TEXT;
        $subscriberExample["Status"] = \eworxMarketingSuite\SubscriberStatus::INACTIVE_IF_ACTIVE;
        $subscriberExample["Fields"] = array(array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "email",
                                                "UntypedValue" => "max@mustermann.at"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "firstname",
                                                "UntypedValue" => "Max"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "lastname",
                                                "UntypedValue" => "Mustermann"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::NUMBER_FIELD_TYPE,
                                                "InternalName" => "customerid",
                                                "UntypedValue" => rand(0, 9999999999)
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::BOOLEAN_FIELD_TYPE,
                                                "InternalName" => "iscustomer",
                                                "UntypedValue" => true
                                            ));

        $subscriberExample2 = array();
        $subscriberExample2["__type"] = \eworxMarketingSuite\Constants::SUBSCRIBER_TYPE;
        $subscriberExample2["Optin"] = false;
        $subscriberExample2["Mailformat"] = \eworxMarketingSuite\Mailformat::HTML;;
        $subscriberExample2["Status"] = \eworxMarketingSuite\SubscriberStatus::ACTIVE;
        $subscriberExample2["Language"] = "DE";
        $subscriberExample2["Fields"] = array(array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "email",
                                                "UntypedValue" => "musterfrau@test.at"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::SELECTION_FIELD_TYPE,
                                                "InternalName" => "position",
                                                "UntypedValue" => "position_sales"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "lastname",
                                                "UntypedValue" => "Musterfrau"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::BOOLEAN_FIELD_TYPE,
                                                "InternalName" => "iscustomer",
                                                "UntypedValue" => false
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::NUMBER_FIELD_TYPE,
                                                "InternalName" => "customerid",
                                                "UntypedValue" => 1
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::DATE_TIME_FIELD_TYPE,
                                                "InternalName" => "birthdate",
                                                "UntypedValue" => date("Y-m-d H:i:s")
                                            ));
        $subscriberExample3 = array();
        $subscriberExample3["__type"] = \eworxMarketingSuite\Constants::SUBSCRIBER_TYPE;
        $subscriberExample3["Optin"] = true;
        $subscriberExample3["Mailformat"] = \eworxMarketingSuite\Mailformat::HTML;
        $subscriberExample3["Status"] = \eworxMarketingSuite\SubscriberStatus::ACTIVE;
        $subscriberExample3["Language"] = "EN";
        $subscriberExample3["Fields"] = array(array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "email",
                                                "UntypedValue" => "isolde@musterfrau.at"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::SELECTION_FIELD_TYPE,
                                                "InternalName" => "position",
                                                "UntypedValue" => "position_sales;position_mechanic"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::TEXT_FIELD_TYPE,
                                                "InternalName" => "lastname",
                                                "UntypedValue" => "Musterfrau"
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::BOOLEAN_FIELD_TYPE,
                                                "InternalName" => "iscustomer",
                                                "UntypedValue" => true
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::NUMBER_FIELD_TYPE,
                                                "InternalName" => "customerid",
                                                "UntypedValue" => ""
                                            ),
                                            array(
                                                "__type" => \eworxMarketingSuite\Constants::DATE_TIME_FIELD_TYPE,
                                                "InternalName" => "birthdate",
                                                "UntypedValue" => date("Y-m-d H:i:s")
                                            )
                                        );

        return array($subscriberDetail, $subscriberExample, $subscriberExample2, $subscriberExample3);
    }

    // Description: Gets the subscriber fields of the account which has been set in the security context.
    // Returns an array of subscriber fields for the given account.
    private function getFieldsOfAccount() {
        $getSubscriberFieldsRequest = $this->serviceAgent->createRequest('GetSubscriberFields');
        // MetaInformation => 1 ->                 Will return predefined fields like tel.nr., email, firstname, lastname, ...
        // CustomInformation => 2 - >              Will return custom defined fields.
        // MetaInformation | CustomInformation =>  Will return all kind of fields.
        $getSubscriberFieldsRequest->setProperty('FieldType', \eworxMarketingSuite\FieldType::META_INFORMATION | \eworxMarketingSuite\FieldType::CUSTOM_INFORMATION );
           
        $subscriberFieldsResponse =  $getSubscriberFieldsRequest->getData();
       
        $fieldCount = count( $subscriberFieldsResponse->Fields);
        if ($fieldCount > 0) {
            echo('<div>-------------------------------Fields----------------------');
            for ($i=0; $i < $fieldCount; $i++) {
                echo '<div style="margin-left:20px">+++++++++++++++ Field '.( $i + 1).' +++++++++++++++';
                $endOftype =  strrpos( $subscriberFieldsResponse->Fields[$i]->__type, ':');
                $typeName = substr( $subscriberFieldsResponse->Fields[$i]->__type, 0, $endOftype);
                echo '<div style="margin-left:20px">Type: '.$typeName.'</div><div style="margin-left:20px">Internalname: '. $subscriberFieldsResponse->Fields[$i]->InternalName.'</div>';
                
                // If the field is of the seletion, the selection fields should also be displayed.
                if ($typeName == 'SelectionField') {
                    $selections = $subscriberFieldsResponse->Fields[$i]->SelectionObjects;
                    $selectionCount =  count($selections);
                    echo '<div>   Selections:<ul style="margin-left:20px">';

                    for ($j=0; $j < $selectionCount; $j++) {
                        echo '<li>'.$selections[$j]->InternalName.'</li>';
                    }

                    echo '</ul></div>';
                }
                echo '+++++++++++++++++++++++++++++++++++++++</div>';
            }
            echo('-------------------------------Fields----------------------</div>');
        }

        return $subscriberFieldsResponse->Fields;
    }

    // Description: Gets the profile with the specified profile name.
    // Parameter profileName: The name of the profile to load.
    // Returns the profile or null if the profile name was not found.
    private function loadProfile($profileName) {
        $getProfilesRequest = $this->serviceAgent->createRequest('GetProfiles');
        $getProfilesRequest->SetProperty('ProfileType', \eworxMarketingSuite\ProfileType::STATIC_TYPE);
        $getProfilesResponse = $getProfilesRequest->getData();
        foreach ($getProfilesResponse->Profiles as $profile) {
            if (strcasecmp($profileName, $profile->Name) == 0) {
                return $profile;
            }
        }
        return NULL;
    }
}
