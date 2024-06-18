<?php

namespace App\Services\School;

use Illuminate\Support\Facades\DB;


class WhatsappMessageService
{
    /**
     * Whatsapp api
     * Send single Whatsapp Message with api.
     *
     */
    public function sendWhatsappMessage($contact, $messageType)
    {
        if($messageType == 'announcement_website') {
            $imageUrl = 'https://bb.branding-element.com/prod/115691/115691-AVlogo_Final.png';
        } elseif($messageType == 'weclome_message1') {
            $imageUrl = 'https://bb.branding-element.com/prod/115691/115691-AVlogo_Final.png';
        } elseif($messageType == 'acids_bases_andsalts_1') {
            $imageUrl = 'https://bb.branding-element.com/prod/115691/115691-WhatsApp%20Image%202024-03-01%20at%207.48.49%20PM.jpeg';
        } elseif($messageType == 'acids_bases_andsalts_2') {
            $imageUrl = 'https://bb.branding-element.com/prod/115691/115691-WhatsApp%20Image%202024-03-01%20at%207.48.55%20PM.jpeg';
        }
        // Initialize cURL session
        $curl = curl_init();

        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://apisocial.telebu.com/whatsapp-api/v1.0/customer/99498/bot/712d21465e54426c/template',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic 4ea3e235-6b3c-4731-bb7a-01a006a060ad-HkD2SrB",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode([
                "payload" => [
                    "name" => $messageType,
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        "link" => $imageUrl
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "language" => [
                        "code" => "en_US",
                        "policy" => "deterministic"
                    ],
                    "namespace" => "849a3367_1cc0_4134_8733_d63a54633ed7"
                ],
                "phoneNumber" => "91".$contact
            ])
        ]);

        // Execute cURL request and fetch response
        $response = curl_exec($curl);

        // Check for errors
        if ($response === false) {
            $error = curl_error($curl);
            // Handle cURL error
        } else {
            $responseData = json_decode($response, true);
        }

        // Close cURL session
        curl_close($curl);
        return $responseData;

    }
}
