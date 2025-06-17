<?php

namespace App\Service;

class AzureTranslateService
{
    private string $endpoint;
    private string $subscriptionKey;
    private string $region;

    public function __construct(string $endpoint, string $subscriptionKey, string $region)
    {
        $this->endpoint = rtrim($endpoint, '/');
        $this->subscriptionKey = $subscriptionKey;
        $this->region = $region;
    }

    /**
     * Traduit $text de $fromLang vers $toLang ("fr" ou "en")
     */
    public function translate(string $text, string $fromLang, string $toLang): string
    {
        $url = $this->endpoint . "/translate?api-version=3.0&from={$fromLang}&to={$toLang}";
        $headers = [
            "Ocp-Apim-Subscription-Key: {$this->subscriptionKey}",
            "Ocp-Apim-Subscription-Region: {$this->region}",
            "Content-Type: application/json",
        ];
        $body = json_encode([['Text' => $text]]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // === AJOUTE CETTE LIGNE ===
        //curl_setopt($ch, CURLOPT_CAINFO, 'A:/PHP/extras/ssl/cacert.pem');
        // ===========================

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \Exception('Azure Translate API call failed: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($response, true);

        return $result[0]['translations'][0]['text'] ?? $text;
    }

}
