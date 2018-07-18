<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomThrottleRequest extends ThrottleRequests
{
    use ApiResponser;


    /**
     * Crea un error cuando el usuario rebasa el limite de peticiones a nuestra API
     *
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return HttpException
     */
    protected function buildException($key, $maxAttempts)
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);

        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

        return $this->errorResponse(['message' => 'mamalo', 'code' => 409],409);
    }
}
