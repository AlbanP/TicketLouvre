<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use AppBundle\Entity\Ticket;
use AppBundle\Form\TicketType;

class TicketController extends Controller
{
    public function indexAction(Request $request)
    {
        $today = date('l j F Y');

        if ($request->isMethod('POST')) {
            $ticket = new Ticket();
            $ticket->setDay($_POST['day']);
            if (isset($_POST['half_day_button'])) {
                $ticket->setHalfDay(true);
            }
            $request->getSession()->set('ticket', $ticket);

            return $this->redirectToRoute('visitor');
        }
        
        return $this->render('AppBundle:Ticket:index.html.twig', array('today' => $today));
    }

    public function visitorAction(Request $request)
    {
        $ticket = $request->getSession()->get('ticket');
        $rate = ;
        $form = $this->get('form.factory')->create(TicketType::class, $ticket);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $ticket->setVisitors($_POST['visitors']);
            $request->getSession()->set('ticket', $ticket);
            $request->getSession()->getFlashBag()->add('notice', 'Visiteur bien enregistré.');

            return $this->redirectToRoute('payment');
        }
        

        return $this->render('AppBundle:Ticket:visitor.html.twig', array('form' => $form->createView(), 'day' => $ticket->getDay(), 'halfDay' => $ticket->getHalfDay()));
    }

    public function paymentAction()
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
                "description" => "Paiement Stripe pour Ticket Louvre"
            ));
            $this->addFlash("success", "Bravo ça marche !");
            return $this->redirectToRoute("home");
        } catch (\Stripe\Error\Card $e) {
            $this->addFlash("error", "Erreur (");
            return $this->redirectToRoute("payment");
            // The card has been declined
        }
    }
}
