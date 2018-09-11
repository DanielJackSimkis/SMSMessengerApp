<?php

namespace AppBundle\Controller;

require '../vendor/autoload.php';

use Twilio\Rest\Client;

use AppBundle\Entity\Message;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;


class MessageController extends Controller
{
    /**
     * @Route("/", name="message_list")
     */
    public function listAction()
    {
        // Fetching Messages form database
        $messages = $this->getDoctrine()
                ->getRepository('AppBundle:Message')
                ->findAll();
        
        return $this->render('message/index.html.twig', array('messages' => $messages));
    }

    /**
     * @Route("/message/create", name="message_create")
     */
    public function createAction(Request $request)
    { 
        // Adding message to the database
        $message = new Message;

        $form = $this->createFormBuilder($message)
            ->add('phoneNum', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('description', TextareaType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('save', SubmitType::class, array('label' => 'Send Message','attr' => array('class' => 'btn btn-primary', 'style' => 'margin-bottom:15px')))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // Get Data
            $phoneNum = $form['phoneNum']->getData();
            $description = $form['description']->getData();
            $status = "Sent";
            $sentDate = new\DateTime('now');

            $message->setPhoneNum($phoneNum);
            $message->setDescription($description);
            $message->setStatus($status);
            $message->setSentDate($sentDate);

            $em = $this->getDoctrine()->getManager();

            $em->persist($message);
            $em->flush();

            $this->addFlash(
                'Notice',
                'Message Sent');

             // --------------- Twilio Api --------------
            $sid = "AC56f8b7325fb39b5a4592054c9639c257";
            $token = "5184aed01edf6314a4caaae21bb36766";
            $client = new Twilio\Rest\Client($sid, $token);

            $client->messages->create($phoneNum, array('from' => '+447449928791', 
                   'body' => $message));

            return $this->redirectToRoute('message_list');
        }

        return $this->render('message/create.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/message/details/{id}", name="message_details")
     */
    public function detailsAction($id)
    {
        // replace this example code with whatever you need
        return $this->render('message/details.html.twig');
    }
}
