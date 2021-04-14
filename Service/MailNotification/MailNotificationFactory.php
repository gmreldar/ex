<?php

declare(strict_types=1);

namespace App\Factories\MailNotification;

use App\Factories\MailNotification\Interfaces\AbstractMailNotificationFactoryInterface;
use App\Factories\MailNotification\Interfaces\MailNotificationInterface;
use Prophecy\Exception\Doubler\ClassNotFoundException;

/**
 * Class MailNotificationFactory
 * @package App\Factories\MailNotification
 */
class MailNotificationFactory implements AbstractMailNotificationFactoryInterface
{
    /**
     * @param string $className
     * @return MailNotificationInterface
     */
    public function make(string $className): MailNotificationInterface
    {
        $className = __NAMESPACE__ . '\\' . ucfirst($className) . 'Factory';
        if (!class_exists($className)) {
            throw new ClassNotFoundException('Class do not exist', $className);
        }
        return app($className);
    }
}
