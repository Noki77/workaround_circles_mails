<?php

namespace OCA\WCM\Listener;

use OCA\Circles\Events\CircleGenericEvent;
use OCA\Circles\Model\ShareWrapper;
use OCA\Circles\Tools\Exceptions\InvalidItemException;
use OCA\Circles\Tools\Exceptions\ItemNotFoundException;
use OCA\Circles\Tools\Exceptions\UnknownTypeException;
use OCA\WCM\AppInfo\Application;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IUserManager;

class CreatingFileShareListener implements IEventListener {
    private IUserManager $userManager;

    public function __construct(IUserManager $userManager) {
        $this->userManager = $userManager;
    }

    /**
     * @throws UnknownTypeException
     * @throws InvalidItemException
     * @throws ItemNotFoundException
     */
    public function handle(Event $event): void {
        if (!$event instanceof CircleGenericEvent) {
            return;
        }

        $fEvent = $event->getFederatedEvent();
        $circle = $fEvent->getCircle();
        $circleOwnerId = $circle->getOwner()->getUserId();

        $dispatchingUser = $this->userManager->get($circleOwnerId);
        if (!$dispatchingUser) {
            /** @var ShareWrapper $share */
            $share = $event->getFederatedEvent()->getParams()->gObj("wrappedShare", ShareWrapper::class);

            $shareByUser = $share->getSharedBy();
            $dispatchingUser = $this->userManager->get($shareByUser);
        }

        if (!$dispatchingUser) {
            return;
        }

        Application::setDispatchingUser($dispatchingUser);
    }
}
