<?php

namespace OCA\WCM\Listener;

use OCA\WCM\AppInfo\Application;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Mail\Events\BeforeMessageSent;
use ReflectionClass;
use ReflectionProperty;

class BeforeMessageSentListener implements IEventListener {
    private ?ReflectionProperty $mailProp;

    /**
     *
     * @param BeforeMessageSent $event
     * @return void
     */
    public function handle(Event $event): void {
        if (!Application::getDispatchingUser()) {
            return;
        }

        $msg = $event->getMessage();
        $userDisplayName = Application::getDispatchingUser()->getDisplayName();
        if ($msg instanceof \OC\Mail\Message && str_contains($msg->getSubject(), "someone")) {
            $msg->setSubject(str_replace("someone", $userDisplayName, $msg->getSubject()));
            $msg->setPlainBody(str_replace("someone", $userDisplayName, $msg->getPlainBody()));

            if (!isset($this->mailProp)) {
                $refMessage = new ReflectionClass("\OC\Mail\Message");
                try {
                    $field = $refMessage->getProperty("symfonyEmail");
                    $this->mailProp = $field;
                } catch (\ReflectionException) {
                    $this->mailProp = null;
                }
            }

            if ($this->mailProp) {
                $symfonyMail = $this->mailProp->getValue($msg);

                $msg->setHtmlBody(str_replace("someone", $userDisplayName, $symfonyMail->getHtmlBody()));
            }
        }
    }
}
