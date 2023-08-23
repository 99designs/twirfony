<?php

namespace Twirfony\TwirfonyBundle\Controller;

use Throwable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Google\Protobuf\Internal\Message;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twirfony\TwirfonyBundle\Event\ErrorEvent;
use Twirfony\TwirfonyBundle\Event\RequestEvent;
use Twirfony\TwirfonyBundle\Event\ResultEvent;
use Twirfony\TwirpError;

class TwirpController extends AbstractController implements LoggerAwareInterface
{
    private $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function rpcAction(Request $request)
    {
        $inputType = $request->attributes->get('inputType');
        $serviceId = $request->attributes->get('service');
        $service = $this->container->get($serviceId);
        $method = $request->attributes->get('method');

        $input = null;
        try {
            $this->assertPost($request);
            $useJson = $this->useJson($request);
            $input = $this->createMessage($inputType);

            if ($useJson) {
                $input->mergeFromJsonString($request->getContent());
            } else {
                $input->mergeFromString($request->getContent());
            }

            $this->container->get('event_dispatcher')->dispatch(new RequestEvent($request, $serviceId, $method, $input), RequestEvent::NAME);
            $output = $service->$method($input);
            $this->container->get('event_dispatcher')->dispatch(new ResultEvent($request, $serviceId, $method, $input, $output), ResultEvent::NAME);

            $response = new Response();

            if ($useJson) {
                $response->setContent($output->serializeToJsonString());
                $response->headers->set('Content-Type', 'application/json');
            } else {
                $response->setContent($output->serializeToString());
                $response->headers->set('Content-Type', 'application/protobuf');
            }
            return $response;
        } catch (TwirpError $e) {
            $this->container->get('event_dispatcher')->dispatch(new ErrorEvent($request, $serviceId, $method, $input, $e), ErrorEvent::NAME);
            return $this->errorResponse($e);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e
            ]);
            $this->container->get('event_dispatcher')->dispatch(new ErrorEvent($request, $serviceId, $method, $input, $e), ErrorEvent::NAME);
            return $this->errorResponse(TwirpError::internalErrorWith($e));
        }
    }

    /**
     * Catch-all route so that undefined services/methods get Twirp errors instead of default symfony errors
     *
     * @param Request $request
     * @return Response
     */
    public function notFoundAction(Request $request)
    {
        return $this->errorResponse(
            $this->badRoute($request, 'No service method defined at ' . $request->getRequestUri())
        );
    }

    /**
     * @param string $messageClass
     * @return Message
     * @throws \Exception
     */
    private function createMessage($messageClass)
    {
        if (!class_exists($messageClass)) {
            throw new \Exception($messageClass . ' is not a defined class');
        }

        $input = new $messageClass();

        if (!is_subclass_of($input, Message::class)) {
            throw new \Exception($messageClass . ' is not a protobuf message');
        }

        return $input;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws TwirpError
     */
    private function useJson(Request $request)
    {
        $types = ['application/json' => true, 'application/protobuf' => false];
        foreach ($types as $mime => $useJson) {
            if (substr($request->headers->get('CONTENT_TYPE'), 0, strlen($mime)) === $mime) {
                return $useJson;
            }
        }

        throw $this->badRoute($request, "Unexpected Content-Type: '{$request->getContentType()}'");
    }

    private function errorResponse(TwirpError $error)
    {
        $content = json_encode($error->toWireFormat());
        return new Response($content, $error->getStatusCode(), [
            'Content-Type', 'application/json'
        ]);
    }

    private function badRoute($request, $message)
    {
        $err = new TwirpError(TwirpError::BAD_ROUTE, $message);
        $err->addMeta('twirp_invalid_route', $request->getMethod() . ' ' . $request->getRequestUri());
        return $err;
    }

    /**
     * @param Request $request
     * @throws TwirpError
     */
    private function assertPost(Request $request)
    {
        if ($request->getMethod() != 'POST') {
            throw $this->badRoute($request, "Unsupported method {$request->getMethod()} (only POST is allowed)");
        }
    }
}
