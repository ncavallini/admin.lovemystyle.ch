<?php class Brevo {
    public static function get_config(): Brevo\Client\Configuration {
        return Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $GLOBALS['CONFIG']['BREVO_API_KEY']);
    }

    public static function add_customer(string $customerNumber, string $firstName, string $lastName, string $email, string $phone = "") {
        $apiInstance = new Brevo\Client\Api\ContactsApi(config: self::get_config());
        $contact = new Brevo\Client\Model\CreateContact();
        $contact->setExtId($customerNumber);
        $contact->setListIds($GLOBALS['CONFIG']['BREVO_DEFAULT_LIST_ID']);
        $contact->setEmail($email);
        $contact->setAttributes((object)[
            'CUSTOMER_ID' => $customerNumber,
            'NOME' => $firstName,
            'COGNOME' => $lastName,
            'SMS' => $phone,            
        ]);
        
        $apiInstance->createContact($contact);

        try {
            return $apiInstance->createContact($contact);
        } catch (Exception $e) {
            error_log('Exception when calling ContactsApi->createContact: ' . $e->getMessage());
            return false;
        }
    }

    public static function delete_customer(string $email) {
        $apiInstance = new Brevo\Client\Api\ContactsApi(config: self::get_config());
        try {
            $apiInstance->deleteContact($email);
        } catch (Exception $e) {
            error_log('Exception when calling ContactsApi->deleteContact: ' . $e->getMessage());
            return false;
        }
        
    }
}
?>