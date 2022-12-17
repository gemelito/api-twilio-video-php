<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// namespace CodeIgniter\RESTful;
namespace App\Core\RESTful;

use CodeIgniter\RESTful\BaseResource;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use Config\Services;

/**
 * An extendable controller to provide a RESTful API for a resource.
 */
class ResourceController extends BaseResource
{
    use ResponseTrait;

    /**
     * Instance of the main Request object.
     *
     * @var IncomingRequest|CLIRequest
     */
    protected $request;

    /**
     * @var string|null
     */
    public $twilioAccountSid;

    /**
     * @var string|null
     */
    public $twilioAuthToken;

    /**
     * @var string|null
     */
    public $twilioApiKeySid;

    /**
     * @var string|null
     */
    public $twilioApiKeySecret;


    /**
     * Constructor.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param LoggerInterface   $logger
     */
    
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        // instantiate our model, if needed
        $this->setModel($this->modelName);

        $this->twilioAccountSid     = getenv('twilio.ACCOUNT_SID');
        $this->twilioAuthToken      = getenv('twilio.AUTH_TOKEN');
        $this->twilioApiKeySid      = getenv('twilio.API_KEY_SID');
        $this->twilioApiKeySecret   = getenv('twilio.API_KEY_SECRET');
    }

    /**
     * Set/change the expected response representation for returned objects
     *
     * @return void
     */
    public function setFormat(string $format = 'json')
    {
        if (in_array($format, ['json', 'xml'], true)) {
            $this->format = $format;
        }
    }

    public function getResponse(array $responseBody, int $code = ResponseInterface::HTTP_OK)
    {
        return $this->response->setStatusCode($code)->setJSON($responseBody);
    }

    public function getRequestInput(IncomingRequest $request)
    {
        $input = $request->getPost();
        if (empty($input)) {
            $input = json_decode($request->getBody(), true);
        }
        return $input;
    }

    public function validateRequest($input, array $rules, array $messages = [])
    {
        $this->validator = Services::validation()->setRules($rules);
        if (is_string($rules)) {
            $validation = config('Validation');

            if (!isset($validation->$rules)) {
                throw ValidationException::forRuleNotFound($rules);
            }

            if (!$messages) {
                $errorName = $rules . '_errors';
                $messages = $validation->$errorName ?? [];
            }

            $rules = $validation->$rules;
        }

        return $this->validator->setRules($rules, $messages)->run($input);
    }
}
