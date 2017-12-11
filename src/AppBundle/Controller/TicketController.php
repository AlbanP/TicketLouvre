<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use AppBundle\Entity\Ticket;
use AppBundle\Entity\Rate;
use AppBundle\Form\TicketType;

class TicketController extends Controller
{
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Rate');
        $listRate = $repository->findAll();
        $today = date('l j F Y');

        if ($request->isMethod('POST')) {
            $ticket = new Ticket();
            $ticket->setDay($_POST['day']);
            if (isset($_POST['half_day_button'])) {
                $ticket->setHalfDay(true);
            }
            $request->getSession()->set('listRate', $listRate);
            $request->getSession()->set('ticket', $ticket);

            return $this->redirectToRoute('visitor');
        }
        
        return $this->render('AppBundle:Ticket:index.html.twig', array('today' => $today, 'list_rate' => $listRate));
    }

    public function visitorAction(Request $request)
    {
        $ticket = $request->getSession()->get('ticket');
        $listRate = $request->getSession()->get('listRate');
        $form = $this->get('form.factory')->create(TicketType::class, $ticket);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if ($ticket->getVisitors()->isEmpty()) {
                $request->getSession()->getFlashBag()->add('notice', 'Vous devez avoir au moins 1 visiteur.');
                return $this->redirectToRoute('visitor');
            }
            $ticketVisitor = $this->calculVisitor($ticket, $listRate);
            $request->getSession()->set('ticket', $ticketVisitor);
            $request->getSession()->getFlashBag()->add('notice', 'Visiteur bien enregistré.');

            return $this->redirectToRoute('payment');
        }

        return $this->render('AppBundle:Ticket:visitor.html.twig', array(
            'form' => $form->createView(),
            'day' => $ticket->getDay(),
            'half_day' => $ticket->getHalfDay(),
            'list_rate' => $listRate));
    }

    public function paymentAction(Request $request)
    {
        $ticket = $request->getSession()->get('ticket');
        return $this->render('AppBundle:Ticket:payment.html.twig', array('ticket' => $ticket));
        /*
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
        */
    }

    public function calculVisitor(Ticket $ticket, $listRate)
    {
        $visitors = $ticket->getVisitors();
        $totalPrice = 0;
        $nbVisitor = 0;
        foreach ($visitors->toArray() as $visitor) {
            $dateVisit = strtotime($ticket->getDay());
            $birthday = strtotime($visitor->getBirthday()->format('Y/m/d')) ;
            $reduction = $visitor->getReduction();
            $dateDiff = abs($birthday - $dateVisit)/ ((60*60*24)*(1+365*4));
            if ($dateDiff < 1) {
                $visitor->setRate($listRate['4']);
            } elseif ($dateDiff < 3) {
                $visitor->setRate($listRate['1']);
            } elseif ($reduction == 1) {
                $visitor->setRate($listRate['3']);
            } elseif ($dateDiff > 15) {
                $visitor->setRate($listRate['2']);
            } else {
                $visitor->setRate($listRate['0']);
            }
            $totalPrice += $visitor->getRate()->getPrice();
            $nbVisitor++;
        }
        $ticket->setNbVisitor($nbVisitor);
        $ticket->setPrice($totalPrice);
        return $ticket;
    }
}
