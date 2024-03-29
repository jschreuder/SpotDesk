<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorHandlerController implements ControllerInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        /** @var  Throwable $exception */
        $exception = $request->getAttribute('error');
        $code = $this->getCode($exception);

        return new JsonResponse(
            [
                'message' => $this->getMessage($code),
            ],
            $code
        );
    }

    private function getCode(Throwable $exception) : int
    {
        if ($exception instanceof PDOException) {
            return 503;
        }

        $code = $exception->getCode();
        if ($code >= 400 && $code < 600) {
            return $code;
        }

        return 500;
    }

    private function getMessage(int $code) : string
    {
        switch ($code) {
            case 400:
                return 'Bad input';
            case 401:
                return 'Unauthenticated';
            case 403:
                return 'Unauthorized';
            case 503:
                return 'Storage engine error';
            case 500:
            default:
                return 'Server error';
        }
    }
}
