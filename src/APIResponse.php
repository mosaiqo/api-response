<?php

namespace Obiefy\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Traits\Macroable;
use Obiefy\API\Contracts\APIResponseContract;

class APIResponse implements APIResponseContract
{
    use Macroable;

    /**
     * Status Label
     * @var string
     */
    protected $statusLabel;

    /**
     * Message Label
     * @var string
     */
    protected $messageLabel;

    /**
     * Data Label
     * @var string
     */
    protected $dataLabel;

    /**
     * Data count Label
     * @var string
     */
    public $dataCountLabel;


    public function __construct()
    {
        $this->setLabels();
    }

    /**
     * Register response labels
     */
    public function setLabels()
    {
        $this->statusLabel = config('api.keys.status');
        $this->messageLabel = config('api.keys.message');
        $this->dataLabel = config('api.keys.data');
        $this->dataCountLabel = config('api.keys.dataCount', 'DATACOUNT');
    }

    /**
     * Create API response
     *
     * @param int $status
     * @param string $message
     * @param array $data
     * @param array $extraData
     *
     * @return JsonResponse
     */
    public function response($status, $message, $data, ...$extraData)
    {
        $json = [
            $this->statusLabel  => config('api.stringify') ? strval($status) : $status,
            $this->messageLabel => $message,
            $this->dataLabel    => $data,
        ];

        is_countable($data) && config('api.includeDataCount', false) && !empty($data) ?
            $json = array_merge($json, [$this->dataCountLabel => count($data)]) :
            '';

        if ($extraData) {
            foreach ($extraData as $extra) {
                $json = array_merge($json, $extra);
            }
        }

        return (config('api.matchstatus')) ? response()->json($json, $status) : response()->json($json);
    }

    /**
     * Create successful (200) API response
     *
     * @param string $message
     * @param array $data
     * @param array $extraData
     *
     * @return JsonResponse
     */
    public function ok($message = '', $data = [], ...$extraData)
    {
        if (empty($message)) {
            $message = config('api.messages.success');
        }

        return $this->response(config('api.codes.success'), $message, $data, ...$extraData);
    }

    /**
     * Create Not found (404) API response
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function notFound($message = '')
    {
        if (empty($message)) {
            $message = config('api.messages.notfound');
        }

        return $this->response(config('api.codes.notfound'), $message, []);
    }

    /**
     * Create Validation (422) API response
     *
     * @param string $message
     * @param array $errors
     * @param array $extraData
     *
     * @return JsonResponse
     */
    public function validation($message = '', $errors = [], ...$extraData)
    {
        if (empty($message)) {
            $message = config('api.messages.validation');
        }

        return $this->response(config('api.codes.validation'), $message, $errors, ...$extraData);
    }

    /**
     * @param $message
     * @param $errors
     * @param array $extraData
     *
     * @return JsonResponse
     */
    public function validationFailedWithMessage($message, $errors, ...$extraData)
    {
        return $this->response(422, $message, $errors, ...$extraData);
    }

    /**
     * Create Server error (500) API response
     *
     * @param string $message
     * @param array $data
     * @param array $extraData
     *
     * @return JsonResponse
     */
    public function error($message = '', $data = [], ...$extraData)
    {
        if (empty($message)) {
            $message = config('api.messages.error');
        }

        return $this->response(config('api.codes.error'), $message, $data, ...$extraData);
    }
}
