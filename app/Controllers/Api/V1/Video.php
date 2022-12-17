<?php

namespace App\Controllers\Api\V1;

// use CodeIgniter\RESTful\ResourceController;
use App\Core\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use Twilio\Rest\Client;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

class Video extends ResourceController
{
    public function token()
    {
        # Get the params from the request
        $rules = [
            'user_identity' => 'required|string',
            'room_name' => 'required|string',
            'request_agent' => 'numeric'
        ];        

        $post = $this->getRequestInput($this->request);

        if (!$this->validateRequest($post, $rules)) {
            return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        $identity       = $post['user_identity'];
        $room_name      = $post['room_name'];
        $request_agent  = $post['user_identity'];

        try {
            // Create an Access Token
            $token = new AccessToken(
                $this->twilioAccountSid,
                $this->twilioApiKeySid,
                $this->twilioApiKeySecret,
                3600,
                $identity
            );

            // Grant access to Video
            $grant = new VideoGrant();
            $grant->setRoom($room_name);
            $token->addGrant($grant);       

            $response = [
                'message'   => 'Token generated successfully',
                'room_type' => 'group',
                'room_name' => $room_name,
                'token'     => $token->toJWT(),
            ];

            return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->getResponse([ 'error' => $e->getMessage() ], ResponseInterface::HTTP_BAD_REQUEST);
        }
    }

    public function sendMessage(){
        $twilio = new Client($sid, $token);
        $message = $twilio->messages
                  ->create("whatsapp:+52998", // to
                           [
                               "from" => "whatsapp:+14155238886",
                               "body" => "Hello there!"
                           ]
                  );

        print($message->sid);
    }
}
