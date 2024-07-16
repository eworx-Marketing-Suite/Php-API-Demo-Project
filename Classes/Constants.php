<?php

namespace eworxMarketingSuite;

class Constants {
    //Fields types
    const TEXT_FIELD_TYPE = 'TextField:#Eworx.Mailworx.ServiceInterfaces';
    const DATE_TIME_FIELD_TYPE = 'DateTimeField:#Eworx.Mailworx.ServiceInterfaces';
    const SELECTION_FIELD_TYPE = 'SelectionField:#Eworx.Mailworx.ServiceInterfaces';
    const NUMBER_FIELD_TYPE = 'NumberField:#Eworx.Mailworx.ServiceInterfaces';
    const BOOLEAN_FIELD_TYPE = 'BooleanField:#Eworx.Mailworx.ServiceInterfaces';
    const URL_FIELD_TYPE = 'UrlField:#Eworx.Mailworx.ServiceInterfaces';
    const MDB_FIELD_TYPE = 'MdbField:#Eworx.Mailworx.ServiceInterfaces';
    const GUID_FIELD_TYPE = 'GuidField:#Eworx.Mailworx.ServiceInterfaces';
    const HTML_ENCODED_FIELD_TYPE = 'HtmlEncodedTextField:#Eworx.Mailworx.ServiceInterfaces';

    // Class types
    const CAMPAIGN_TYPE = 'Campaign:#Eworx.Mailworx.ServiceInterfaces.Campaigns';
    const SECTION_TYPE = 'Section:#Eworx.Mailworx.ServiceInterfaces.Campaigns';
    const TEMPLATE_TYPE = 'Template:#Eworx.Mailworx.ServiceInterfaces.Templates';
    const SUBSCRIBER_TYPE = 'Subscriber:#Eworx.Mailworx.ServiceInterfaces.Subscribers';
    const MANUAL_SEND_SETTINGS_TYPE = 'ManualSendSettings:#Eworx.Mailworx.ServiceInterfaces.Campaigns';
    const AB_SPLIT_TEST_SEND_SETTINGS_TYPE = 'ABSplitTestSendSettings:#Eworx.Mailworx.ServiceInterfaces.Campaigns';
    // Importactions
    // BeforeImportAction
    const CLEAR_PROFILE_ACTION_TYPE = 'ClearProfileAction:#Eworx.Mailworx.ServiceInterfaces.Subscribers.SubscriberImport';
    const REMOVE_DUPLICATE_ACTION_TYPE = 'RemoveDuplicatesAction:#Eworx.Mailworx.ServiceInterfaces.Subscribers.SubscriberImport';
    
    // PostSubscriberAction
    const PROFILE_ADDER_ACTION_TYPE = 'ProfileAdderAction:#Eworx.Mailworx.ServiceInterfaces.Subscribers.SubscriberImport';
    const SEND_CAMPAIGN_ACTION_TYPE = 'SendCampaignAction:#Eworx.Mailworx.ServiceInterfaces.Subscribers.SubscriberImport';
    const PROFILE_REMOVE_ACTION_TYPE = 'ProfileRemoveAction:#Eworx.Mailworx.ServiceInterfaces.Subscribers.SubscriberImport';
    const SEND_SMS_CAMPAIGN_ACTION_TYPE = 'SendSMSCampaignAction:#Eworx.Mailworx.ServiceInterfaces.Subscribers.SubscriberImport';

    // AfterImportAction
    const SEND_NOTIFICATION_ACTION_TYPE = 'SendNotificationAction:#Eworx.Mailworx.ServiceInterfaces.Subscribers.SubscriberImport';
}

class SendType {
    const MANUAL = 0;
    const AB_SPLIT = 1;
    const EVENT_DEPENDENT = 2; 
}

class CampaignType {
    const IN_WORK = 1;
    const SENT = 2;
    const EVENT_DEPENDENT = 4;
}

class ExecuteWith{
    const INSERT = 1;
    const UPDATE = 2;
}

class Mailformat {
    const TEXT = 0;
    const HTML = 1;
    const MULTIPART = 2;        
}

class SubscriberStatus {
    // Active   -> 0 -> Sets the subscriber to active.
    const ACTIVE = 0;

    // Inactice -> 1 -> Sets the subscriber to inactive. 
    const INACTIVE = 1;

	/* ActiveIfManualInactive -> 2
	// -> If the current state of the subscriber is manual inactive, it will be changed to active. 
	// -> If the current state of the subscriber is automatic inactive, it won't be changed to active.*/
    const ACTIVE_IF_MANUAL_INACTIVE = 2;

    /*InactiveIfActive -> 3
    -> If the state of the subscriber is active, it will be changed to manual inactive.
    -> If the state of the subscriber is manual inactive or automatic inactive, it won't be changed to manual inactive.
	The status automatic inactive can only be set by the mailworx system itself.
	If you don't want to change the value of existing subscribers leave this value unassigned.*/
    const INACTIVE_IF_ACTIVE = 3;
}

class FieldType {
    const META_INFORMATION = 1;

    const CUSTOM_INFORMATION = 2;
}

class ProfileType {
    const  DYNAMIC_TYPE = 1;

    const STATIC_TYPE = 2;
}
?>