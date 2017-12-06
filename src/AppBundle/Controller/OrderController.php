<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Ticket;
use AppBundle\Form\TicketType;

/**
 * This controller is used to simulate an order from a customer.
 * Class OrderController
 * @package AppBundle\Controller
 * @Route("/order", name="order_prepare")
 */
class OrderController extends Controller
{
    // add visitor(s)
    /**
     * @Route("/add", name="order_add")
    */
    public function addAction(Request $request)
    {
        $ticket = new Ticket();
        $form = $this->get('form.factory')->create(TicketType::class, $ticket);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($ticket);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Visiteur bien enregistré.');

            return $this->redirectToRoute('order_add', array('id' => $ticket->getId()));
        }
        

        return $this->render('AppBundle:Ticket:add.html.twig', array('form' => $form->createView(),));
    }


    /**
     * @Route("/prepare", name="order_prepare")
     */
    public function prepareAction()
    {
        return $this->render('ticket/prepare.html.twig');
    }

    /**
     * @Route(
     *     "/checkout",
     *     name="order_checkout",
     *     methods="POST"
     * )
    */
    public function checkoutAction()
    {
        \Stripe\Stripe::setApiKey("sk_test_lZReZR3lqdyyQmSsCnmAUOtQ");

        // Get the credit card details submitted by the form
        $token = $_POST['stripeToken'];

        // Create a charge: this will charge the user's card
        try {
            $charge = \Stripe\Charge::create(array(
                "amount" => 1000, // Amount in cents
                "currency" => "eur",
                "source" => $token,
                "description" => "Paiement Stripe - OpenClassrooms Exemple"
            ));
            $this->addFlash("success", "Bravo ça marche !");
            return $this->redirectToRoute("order_prepare");
        } catch (\Stripe\Error\Card $e) {
            $this->addFlash("error", "Snif ça marche pas :(");
            return $this->redirectToRoute("order_prepare");
            // The card has been declined
        }
    }
}
