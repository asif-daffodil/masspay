<?php
class masspay
{
    protected const api_key = "eyJlbmMiOiJBMTI4R0NNIiwiYWxnIjoiUlNBLU9BRVAtMjU2In0.kIcPN9DL4mqVnTZxDfDV6vEcx-KuGc6sa1iXCg4jvJW3KvNFClaRG1ZdJpLPjda61UHVlbjgd3jd2sd_-vDlAuiZXQE-lppPARp5OIc_RN1zdxpjEUXbX6Me48sBCim6sKSv6XrdNURvFDU0zChQxxaGeLnsGwxzEMthQ3DoizJ2c_4kK8wyLWl28Auh04UI8wEG2wTXHeCLawthj8MFsN2lTy_MjPqgwd-twKGS3i4YDC0cG1zTFzL7UI0EXY-4JmHWNi_DYWLspyIMWcZmrkHH45hpnVsb5Nnc7n0qCrjtKGGasYpWS9bzHRQyxrR3tBGgvy3Gtmz6tgw_Yj2f_GNZU40KYwLcGqLPuTsrLlaCYbVhWFsos2wPuJqhlcD2X8fNegPx-wdO7oel1MXlTSXaPZENdSS__D4rwM4t4E4LkEb_5kCKp12WwuBlBXv9MWszU16NmI6rl6x6zX46LH7jo5EKJaAdKuKSfSyinmuYFXT1Ye9NHX4Vl2KkyIXJt1VMmxVjAwx0r2zL5cmNWycbeFF64DJMEpWem2Vo0GcZ52d679U6P_jTX4fXqbSjbHS15osoI3HfoFRYF1RqRY114cNY5xfLZoLHXEbK_cwZbZXb4mO3pjLRF-YRSpeWncb970EysAFcVcJVUcgVZy-SzPbDRll-aBakwvnvji0.7Tn0ccyCyrDkvwR7.oSHFW9KUTuq5IH1HNsLGG_Y9YLAA_uPLrG068NXp9DD16TUa-06t3chQvCcbDn2bOfqZy0LkxxPZHY_iCwUqNb-ipuLZ1sX2RaR5g4wSNTLT5u5EcwwpJ5spIaUBNqANjmghGIy1vNiM8cRht8dK2hHIsv_U4yWf9uf-hfu61MlcC4tbMXhbGZNXtIWsWPe8qkBNXOEHvCdz9Koe0hwiIosIKwIXrQaw0EbqiX7uwyJBmX3Cm1CnVMSeHAIbSullKvaimlic_Zftp9DofEWmkhj_W1HslI8B45B0.5jwGNAt2m7OBk-DtWcRKyA";
    protected const curl_url = "https://staging-api.masspay.io/v1.0.0";

    protected static $curl;

    public function handle_curl_operation ($url, $method, $data = null): array | bool
    {
        // validate url and method
        if (empty($url) || empty($method)) {
            return false;
        }


        curl_setopt_array(self::$curl, [
            CURLOPT_URL => self::curl_url . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $data != null ? json_encode($data): null,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . self::api_key,
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec(self::$curl);
        $err = curl_error(self::$curl);

        if ($err) {
            return ["error" => "cURL Error #:" . $err];
        } else {
            return json_decode($response, true);
        }
    }

    /**
     * Check Available Balance
     * @return array
     * @requires MassPay account created
     * @requires MassPay API credentials available
     */

    public function check_available_balance(): array
    {
        return $this->handle_curl_operation("/payout/account/balance", "GET");
    }

    /**
     * Create User
     * @param array $data
     * @return array
     * @requires MassPay account created
     * @requires MassPay API credentials available
     * @requires User data array ['country', 'first_name', 'last_name', 'email']
     */
    public function create_user(array $data): array | bool
    {
        if(empty($data) || !isset($data['country']) || !isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email'])) {
            return false;
        }

        return $this->handle_curl_operation("/payout/user", "POST", [
            'notify_user' => false,
            'internal_user_id' => uniqid(),
            'country' => $data['country'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'address1' => $data['address1'] ?? null,
            'address2' => $data['address2'] ?? null,
            'city' => $data['city'] ?? null,
            'state_province' => $data['state_province'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'mobile_number' => $data['mobile_number'] ?? null,
            'business_name' => $data['business_name'] ?? null,
            'language' => $data['language'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
        ]);
    }

    /**Get destination token
     * @param array $data
     * @return array
     * @requires country code
     */


    public function get_destination_token(array $data): array | bool
    {
        if(empty($data) || !isset($data['country']) || !isset($data['amount']) || !isset($data['include_payer_logos'])) {
            return false;
        }

        return $this->handle_curl_operation("/payout/country/" . $data['country'] . "/best?amount=" . $data['amount'] . "&include_payer_logos=" . $data['include_payer_logos'], "GET");
    }

    /** Get user attributes for destination_token
     * @param array $data
     * @return array
     * @requires user_token, destination_token, currency
     */

    public function get_user_attributes(array $data): array | bool
    {
        if(empty($data) ||  !isset($data['user_token']) || !isset($data['destination_token']) || !isset($data['currency'])) {
            return false;
        }

        return $this->handle_curl_operation("/payout/attribute/" . $data['user_token'] . "/" . $data['destination_token'] . "/" . $data['currency'] . "?show_latest_attr_set_token=false", "GET");
    }

    /**
     * Store user attributes
     * @param array $data
     * @return array
     * @requires user_token, destination_token, currency, values
     * @requires values array ['token', 'value']
     * get attribute token from get_user_attributes
     * @requires Debit Card Expiration Date, Your Address 1, Debit Card Number, City, Date of Birth
     * @optional Social Security Number or ITIN, Message To Receiver, Phone Number, Zip Code
     */

    public function store_user_attributes(array $data): array | bool
    {
        if(empty($data) ||  !isset($data['user_token']) || !isset($data['destination_token']) || !isset($data['currency']) || !isset($data['values'])) {
            return false;
        }

        $values = [];
        foreach ($data['values'] as $value) {
            $values[] = [
                'token' => $value['token'],
                'value' => $value['value']
            ];
        }

        return $this->handle_curl_operation("/payout/attribute/" . $data['user_token'] . "/" . $data['destination_token'] . "/" . $data['currency'], "POST", [
            'values' => $values
        ]);
    }

    /**
     * Initiate a payout transaction
     * @param array $data
     * @return array
     * @requires user_token, values
     * @requires values array ['source_currency_code', 'notify_user', 'auto_commit', 'destination_currency_code', 'destination_token', 'source_token', 'source_amount', 'attr_set_token', 'client_transfer_id', 'statement_description']
     * @requires source_currency_code, notify_user, auto_commit, destination_currency_code, destination_token, source_token, source_amount, attr_set_token
     * @optional client_transfer_id, statement_description
     * to get attr_set_token, call store_user_attributes
     */

    public function initiate_payout($data): array | bool
    {
        if(empty($data) ||  !isset($data['user_token']) || !isset($data['values'])) {
            return false;
        }
        
        return $this->handle_curl_operation("/payout/" . $data['user_token'], "POST", $data['values']);
    }



    public function __construct()
    {
        self::$curl = curl_init();
    }

    public function __destruct()
    {
        curl_close(self::$curl);
    }
}


/* 
// Check available balance 
$masspay = new masspay();
echo "<pre>";
print_r($masspay->check_available_balance());
echo "</pre>"; 
*/



/* 
//  Create User
 $masspay = new masspay();
 $data = ['country' => 'USA', 'first_name' => 'Asif', 'last_name' => 'Abir', 'email' => 'asif@abir.com'];
 echo "<pre>";
 print_r($masspay->create_user($data));
 echo "</pre>"; 
 */


/* 
//  Gets a list of Companies and their best service offerings for the given country code
 $masspay = new masspay();
 echo "<pre>";
 print_r($masspay->get_destination_token(['country' => 'USA', 'amount' => 200, 'include_payer_logos' => false]));
 echo "</pre>";
 */


/* 
// Get user attributes for destination_token
$masspay = new masspay();
$data = ['user_token' => 'usr_7b564bdb-d5e6-11ef-a9fe-0a5553066c45', 'destination_token' => 'dest_006c8fe1-835b-11ef-b954-0235c9d109d3', 'currency' => 'USD'];
echo "<pre>";
print_r($masspay->get_user_attributes($data));
echo "</pre>";  
 */


/* 
// Store user attributes
$masspay = new masspay();
$data = [
    'user_token' => 'usr_7b564bdb-d5e6-11ef-a9fe-0a5553066c45',
    'destination_token' => 'dest_006c8fe1-835b-11ef-b954-0235c9d109d3',
    'currency' => 'USD',
    'values' => [
        ['token' => 'attr_92558bc4-4a0d-11ee-ae7f-02a96673e721', 'value' => '2025-01'],
        ['token' => 'attr_914fc9b9-b88e-11ed-8ee0-02c3be5336a9', 'value' => 'Dhanmondi Dhaka'],
        ['token' => 'attr_cec22fb8-eea6-11ed-99df-02450efa1c35', 'value' => '4170338019116179'],
        ['token' => 'attr_df7e0871-21e5-2391-9034-06297e0562fa', 'value' => 'Dhaka'],
        ['token' => 'attr_bc139893-f17f-11ea-a05b-06fe542523ef', 'value' => '1987-10-09']
    ]
];
echo "<pre>";
print_r($masspay->store_user_attributes($data));
echo "</pre>"; 
 */

/* 
// Initiate a payout transaction
$masspay = new masspay();
$data = [
    'user_token' => 'usr_7b564bdb-d5e6-11ef-a9fe-0a5553066c45',
    'values' => [
        'source_currency_code' => 'USD',
        'notify_user' => false,
        'auto_commit' => false,
        'destination_currency_code' => 'USD',
        'destination_token' => '006c8fe1-835b-11ef-b954-0235c9d109d3',
        'source_token' => '64326163-3736-6434-2d35-3736612d3131',
        'source_amount' => 100,
        'attr_set_token' => '856b587f-db86-400f-9a17-846125ff9f6a',
        'client_transfer_id' => uniqid(),
        'statement_description' => 'Merchant 4561'
    ]
];
echo "<pre>";
print_r($masspay->initiate_payout($data));
echo "</pre>"; 
 */
?>