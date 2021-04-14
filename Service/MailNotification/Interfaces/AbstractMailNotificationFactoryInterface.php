<?php

declare(strict_types=1);

namespace App\Factories\MailNotification\Interfaces;

/**
 * Interface AbstractMailNotificationFactoryInterface
 * @package App\Factories\MailNotification\Interfaces
 */
interface AbstractMailNotificationFactoryInterface
{
    /**
     * @param string $className
     * @return MailNotificationInterface
     */
    public function make(string $className): MailNotificationInterface;
}
