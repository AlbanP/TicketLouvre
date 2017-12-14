<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use AppBundle\Entity\Ticket;
use AppBundle\Entity\Bill;
use AppBundle\Entity\Rate;
use AppBundle\Form\TicketType;
use AppBundle\Form\BillType;

class TicketController extends Controller
{
    public function indexAction(Request $request)
    {
        // find rate and save to session
        $repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Rate');
        $listRate = $repository->findRate();
        $request->getSession()->set('listRate', $listRate);
        
        $dateVisit = new \Datetime();
        // When the visitor has slecteted dateVisit and day or half-day
        if ($request->isMethod('POST')) {
            $ticket = new Ticket();

            $ticket->setDateVisit($dateVisit);
            // If visitor select half-day -> true
            if (isset($_POST['half_day_button'])) {
                $ticket->setHalfDay(true);
            }
            // Save to session ticket
            $request->getSession()->set('ticket', $ticket);
            
            return $this->redirectToRoute('visitor');
        }
        
        return $this->render('AppBundle:Ticket:index.html.twig', array('dateVisit' => $dateVisit, 'list_rate' => $listRate));
    }

    public function visitorAction(Request $request)
    {
        // Load ticket and rate by session
        $ticket = $request->getSession()->get('ticket');
        $listRate = $request->getSession()->get('listRate');
        // Create form for the visitors
        $form = $this->get('form.factory')->create(TicketType::class, $ticket);
        // When the visitor select pay
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // Check if have 1 visitor
            if ($ticket->getVisitors()->isEmpty()) {
                $request->getSession()->getFlashBag()->add('notice', 'Vous devez avoir au moins 1 visiteur.');
                return $this->redirectToRoute('visitor');
            }
            // Calcul the rate visitor, the number of visitor and price Ticket
            $ticketVisitor = $this->calculVisitor($ticket, $listRate);
            $request->getSession()->set('ticket', $ticketVisitor);
            $request->getSession()->getFlashBag()->add('notice', 'Visiteur bien enregistré.');

            return $this->redirectToRoute('payment');
        }

        return $this->render('AppBundle:Ticket:visitor.html.twig', array(
            'form' => $form->createView(),
            'dateVisit' => $ticket->getDateVisit(),
            'half_day' => $ticket->getHalfDay(),
            'list_rate' => $listRate));
    }

    public function paymentAction(Request $request)
    {
        $ticket = $request->getSession()->get('ticket');
        $bill = new Bill();
        $form = $this->get('form.factory')->create(BillType::class, $bill);

        if ($request->isMethod('POST')) {
            $token = $_POST['stripeToken'];
            $email = $_POST['email'];
            // Service payment
            $payment = $this->get('app.payment');
            $response = $payment->charge($token, $ticket, array(
                                            "Email" => $email,
                                            "Nombre de Ticket" => $ticket->getNbVisitor(),
                                            "Date de visite" => $ticket->getDateVisit()->format('d/m/Y')
                                            ));

            $bill->setTransactionId($response['id']);
            $dateBill = new \Datetime();
            $dateBill->setTimestamp($response['created']);
            $bill->setDateBill($dateBill);
            $bill->setPrice($response['amount']);
            $bill->setEmail($email);
            $ticket->setBill($bill);
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();
        }

        return $this->render('AppBundle:Ticket:payment.html.twig', array(
                        'form' => $form->createView(),
                        'ticket' => $ticket));
    }

    // Calcul visitor rate, number of visitor and ticket price (total)
    public function calculVisitor(Ticket $ticket, $listRate)
    {
        $visitors = $ticket->getVisitors();
        $totalPrice = 0;
        $nbVisitor = 0;
        foreach ($visitors->toArray() as $visitor) {
            $dateVisit = $ticket->getDateVisit();
            $birthday = $visitor->getBirthday();
            $reduction = $visitor->getReduction();
            // Diff interval dateVisit-datebirthday (format year.day)
            $dateDiff = $dateVisit->diff($birthday);
            $dateDiff = $dateDiff->format('%y.%d');
            // Define rate
            if ($dateDiff <= 4) {
                $visitor->setRate($listRate[4]['price']);
            } elseif ($dateDiff <= 12) {
                $visitor->setRate($listRate[1]['price']);
            } elseif ($reduction == 1) {
                $visitor->setRate($listRate[3]['price']);
            } elseif ($dateDiff >= 60) {
                $visitor->setRate($listRate[2]['price']);
            } else {
                $visitor->setRate($listRate[0]['price']);
            }
            // Update visitor to Ticket (add id ticket to Visitor entity)
            $ticket->updateVisitor($visitor);
            // ticket price and increase number visitor
            $totalPrice += $visitor->getRate();
            $nbVisitor++;
        }
        $ticket->setNbVisitor($nbVisitor);
        $ticket->setPrice($totalPrice);
        print_r($ticket);
        return $ticket;
    }
}
