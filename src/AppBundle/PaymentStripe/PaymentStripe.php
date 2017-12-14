<?php

namespace AppBundle\PaymentStripe;

class PaymentStripe
{
    private $secretKey;
    private $current;
    private $description;

    public function __construct($secretKey, $current, $description)
    {
        $this->secretKey   = $secretKey;
        $this->current    = $current;
        $this->description = $description;
    }

    public function charge($token, $ticket, $array)
    {
        \Stripe\Stripe::setApiKey("sk_test_lZReZR3lqdyyQmSsCnmAUOtQ");
        $response = \Stripe\Charge::create(array(
                    "amount" => $ticket->getPrice(),
                    "currency" => $this->current,
                    "source" => $token,
                    "description" => $this->description,
                    "metadata" => $array));

        return $response;
    }
}
