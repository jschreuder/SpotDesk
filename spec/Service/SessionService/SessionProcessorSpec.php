<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\SessionService;

use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Service\SessionService\SessionProcessor;
use jschreuder\SpotDesk\Service\SessionService\SessionStorageInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SessionProcessorSpec extends ObjectBehavior
{
    /** @var  SessionStorageInterface */
    private $storage;

    /** @var  int */
    private $duration;

    /** @var  float */
    private $refreshAfter;

    public function let(SessionStorageInterface $storage) : void
    {
        $this->beConstructedWith(
            $this->storage = $storage,
            $this->duration = 3600,
            $this->refreshAfter = 0.5
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(SessionProcessor::class);
    }

    public function it_can_process_a_request(
        ServerRequestInterface $request1,
        ServerRequestInterface $request2,
        SessionInterface $session
    ) : void
    {
        $sessionKey = 'some-key-or-something';

        $request1->getHeaderLine(SessionProcessor::SESSION_KEY)->willReturn($sessionKey);
        $this->storage->load($sessionKey)->willReturn($session);
        $request1->withAttribute('session', $session)->willReturn($request2);
        $this->processRequest($request1)->shouldReturn($request2);
    }

    public function it_can_process_a_request_without_session_key(
        ServerRequestInterface $request1,
        ServerRequestInterface $request2,
        SessionInterface $session
    ) : void
    {
        $request1->getHeaderLine(SessionProcessor::SESSION_KEY)->willReturn(null);
        $this->storage->load('')->willReturn($session);
        $request1->withAttribute('session', $session)->willReturn($request2);
        $this->processRequest($request1)->shouldReturn($request2);
    }

    public function it_will_ignore_responses_with_empty_sessions(
        ServerRequestInterface $request,
        ResponseInterface $response,
        SessionInterface $session
    ) : void
    {
        $request->getAttribute('session')->willReturn($session);
        $session->isEmpty()->willReturn(true);
        $this->processResponse($request, $response)->shouldReturn($response);
    }

    public function it_will_ignore_responses_when_no_session_on_request(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : void
    {
        $request->getAttribute('session')->willReturn(null);
        $this->processResponse($request, $response)->shouldReturn($response);
    }

    public function it_will_attach_session_to_response_when_necessary(
        ServerRequestInterface $request,
        ResponseInterface $response1,
        ResponseInterface $response2,
        SessionInterface $session
    ) : void
    {
        $sessionKey = 'some-key-or-something';
        $sessionData = ['some' => 'data', 'or' => 'other'];

        $request->getAttribute('session')->willReturn($session);
        $session->isEmpty()->willReturn(false);
        $session->toArray()->willReturn($sessionData);

        $this->storage->needsRefresh($session, 1800)->willReturn(true);
        $this->storage->create($sessionData, $this->duration)->willReturn($sessionKey);
        $response1->withHeader(SessionProcessor::SESSION_KEY, $sessionKey)->willReturn($response2);

        $this->processResponse($request, $response1)->shouldReturn($response2);
    }

    public function it_will_ignore_responses_when_no_refresh_is_necessary(
        ServerRequestInterface $request,
        ResponseInterface $response,
        SessionInterface $session
    ) : void
    {
        $request->getAttribute('session')->willReturn($session);
        $session->isEmpty()->willReturn(false);
        $this->storage->needsRefresh($session, 1800)->willReturn(false);
        $this->processResponse($request, $response)->shouldReturn($response);
    }
}
