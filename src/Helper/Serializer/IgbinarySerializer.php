<?php

declare(strict_types=1);

namespace App\Helper\Serializer;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

/**
 * @codeCoverageIgnore
 */
class IgbinarySerializer extends PhpSerializer
{
    /**
     * {@inheritdoc}
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        if (empty($encodedEnvelope['body'])) {
            throw new MessageDecodingFailedException('Encoded envelope should have at least a "body".');
        }

        return $this->safelyUnserialize(stripslashes($encodedEnvelope['body']));
    }

    /**
     * {@inheritdoc}
     */
    public function encode(Envelope $envelope): array
    {
        return [
            'body' => addslashes(igbinary_serialize($envelope->withoutStampsOfType(NonSendableStampInterface::class))),
        ];
    }

    /**
     * @return mixed
     */
    private function safelyUnserialize(string $contents)
    {
        $signalingException = new MessageDecodingFailedException(
            sprintf('Could not decode message using PHP serialization: %s.', $contents)
        );
        $prevErrorHandler = set_error_handler(
            static function ($type, $msg, $file, $line, $context = []) use (
                &$prevErrorHandler, $signalingException
        ) {
                if (__FILE__ === $file) {
                    throw $signalingException;
                }

                return $prevErrorHandler ? $prevErrorHandler($type, $msg, $file, $line, $context) : false;
            });

        try {
            return igbinary_unserialize($contents);
        } finally {
            $prevUnserializeHandler = ini_set('unserialize_callback_func', self::class.'::handleUnserializeCallback');
            restore_error_handler();
            if ($prevUnserializeHandler) {
                ini_set('unserialize_callback_func', $prevUnserializeHandler);
            }
        }
    }
}
