<?php
namespace OCA\WCM\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCA\WCM\Listener\BeforeMessageSentListener;
use OCP\IUser;
use OCP\Mail\Events\BeforeMessageSent;

class Application extends App implements IBootstrap {
    private static ?IUser $dispatchingUser = null;

    public function __construct(array $urlParams = []) {
        parent::__construct("WCM", $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        if (class_exists("\\OCA\\Circles\\Events\\Files\\CreatingFileShareEvent")) {
            $context->registerEventListener(BeforeMessageSent::class, BeforeMessageSentListener::class);
            $context->registerEventListener(\OCA\Circles\Events\Files\CreatingFileShareEvent::class, \OCA\WCM\Listener\CreatingFileShareListener::class);
        }
    }

    public function boot(IBootContext $context): void { }

    public static function setDispatchingUser(IUser $user): void {
        static::$dispatchingUser = $user;
    }

    public static function getDispatchingUser(): ?IUser {
        return static::$dispatchingUser;
    }
}
