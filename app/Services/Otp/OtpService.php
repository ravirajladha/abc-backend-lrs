<?php

namespace App\Services\Otp;

use Illuminate\Support\Facades\Http;

class OtpService
{
    protected $apiUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->apiUrl = 'https://restapi.smscountry.com/v0.1/Accounts/Fmm5AOg8EK2Ljab4ilgp/SMSes/';
        $this->username = 'Fmm5AOg8EK2Ljab4ilgp'; // Replace with your actual username
        $this->password = 'CmcMUhZyVnw3qcFp7owhuEnBJQdwva0ofgwsrJIZ'; // Replace with your actual password
    }

    public function sendOtp($phoneNumber,$username, $otp)
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->post($this->apiUrl, [
                'Text' => "Dear $username,Your One-Time Password (OTP) for password recovery is: ".$otp."Please use this OTP to reset your password. This OTP is valid for the next 10 minutes.Thank you,SHREE ARADHYA EDUCATIONAL AND CHARITABLE TRUST",
                'Number' => '91'.$phoneNumber,
                'SenderId' => 'SHRARA',
                'DRNotifyUrl' => 'https://www.domainname.com/notifyurl',
                'DRNotifyHttpMethod' => 'POST',
                'Tool' => 'API',
            ]);

        if ($response->successful()) {
            return true;
        } else {
            // Log the error or handle it as needed
            return false;
        }
    }
}
