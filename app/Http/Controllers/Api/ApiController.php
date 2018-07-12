<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Response;

class ApiController extends Controller
{
    /**
     * Http Status code
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Getter for statusCode property
     *
     * @return int
     *
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode property
     *
     * @param int $statusCode
     *
     * @return $this
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Gives json with message and 404 status code
     *
     * @param string $message
     *
     * @return \Illuminate\Http\Response
     */
    public function respondNotFound($message = 'Not Found!')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * Gives json with message and 401 status code
     *
     * @param string $message
     *
     * @return \Illuminate\Http\Response
     */
    public function respondUnauthorised($message = 'Unauthorised!')
    {
        return $this->setStatusCode(401)->respondWithError($message);
    }

    /**
     * Gives json with message and 400 status code
     *
     * @param string $message
     *
     * @return \Illuminate\Http\Response
     */
    public function respondBadRequest($message = 'Bad Request!')
    {
        return $this->setStatusCode(400)->respondWithError($message);
    }

    /**
     * Gives json with data, 200 status code and headers
     *
     * @param mixed $data
     * @param mixed $headers
     *
     * @return \Illuminate\Http\Response
     */
    public function respond($data, $headers = [])
    {
        return Response::json($data, $this->getStatusCode(), $headers);
    }

    /**
     * Gives json with error message and current status code
     *
     * @param mixed $message
     *
     * @return \Illuminate\Http\Response
     */
    public function respondWithError($message)
    {
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }

    /**
     * Gives json response with the given data
     *
     * @param mixed $data
     */
    public function respondWithData($data)
    {
        return $this->respond([
            'data' => $data
        ]);
    }
}
