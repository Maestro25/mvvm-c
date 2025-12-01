<?php
declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

enum PaymentMethod: string
{
    use EnumHelpers;

    case CREDIT_CARD = 'credit_card';
    case PAYPAL = 'paypal';
    case BANK_TRANSFER = 'bank_transfer';
    case CRYPTO = 'crypto';

    public function label(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Credit Card',
            self::PAYPAL => 'PayPal',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CRYPTO => 'Cryptocurrency',
        };
    }
}
