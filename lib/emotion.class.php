<?php
error_reporting(0);
/**
* 
*/
class Emotion
{
    private $key;

    function __construct($key = 'your_key_here')
    {
        $this->key = $key;
    }

    public function getEmotionScore($picurl)
    {
        // Create the context for the request
        $context = stream_context_create(array(
            'http' => array(
                // http://www.php.net/manual/en/context.http.php
                'method' => 'POST',
                'header' => "Ocp-Apim-Subscription-Key: {$this->key}\r\n".
                    "Content-Type: application/json\r\n",
                'content' => json_encode(['url' => $picurl], true)
            )
        ));

        // Send the request
        $response = file_get_contents('https://api.projectoxford.ai/emotion/v1.0/recognize', FALSE, $context);

        
        if($response === "[]")
        {
            $response = json_encode([ "disgust" => 0,
                                      "contempt" => 0,
                                      "anger" => 0,
                                      "happiness" => 0,
                                      "sadness" => 0,
                                      "fear" => 0,
                                      "surprise" => 0,
                                      "neutral" => 0]);
        }
        return $response;
    }
}