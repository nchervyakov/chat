<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 20.03.2015
 * Time: 20:33
 */


namespace AppBundle\Controller;

use AppBundle\Entity\ModelRequest;
use AppBundle\Form\Type\ModelRequestType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ModelRequestController
 * @package AppBundle\Controller
 * @Route("/model")
 */
class ModelController extends Controller
{
    /**
     * @Route("/request-registration", name="model_request_registration")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException();
        }

        $modelRequest = new ModelRequest();
        $form = $this->createRequestCreationForm($modelRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($modelRequest);
            $em->flush();

            $this->get('session')->set('model.reg_request.id', $modelRequest->getId());
            return $this->redirectToRoute('model_request_registration_success');
        }

        return $this->render(':Model:request_registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/request-registration/success", name="model_request_registration_success")
     */
    public function requestSuccessAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException();
        }

        $id = $this->get('session')->get('model.reg_request.id');
        $modelRequest = $this->getDoctrine()->getRepository('AppBundle:ModelRequest')->find($id);

        if (!$modelRequest) {
            throw new NotFoundHttpException;
        }

        return $this->render(':Model:request_success.html.twig', [
            'modelRequest' => $modelRequest
        ]);
    }

    /**
     * @Route("/activate-via-social-network/{activationToken}", name="model_request_activate_via_social_network")
     * @param string $activationToken
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activateViaFacebookAction($activationToken)
    {
        $activationToken = trim($activationToken);
        $model = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['activationToken' => $activationToken]);
        if (!$model || !$model->isEnabled()) {
            //throw new NotFoundHttpException();
        }

//        if ($model->isActivated()) {
//            throw new HttpException("The model is already activated.");
//        }

        return $this->redirectToRoute('oauth_service_redirect', [
            'activation_token' => $activationToken,
            'service' => 'facebook'
        ]);
    }

    protected function createRequestCreationForm(ModelRequest $modelRequest)
    {
        $form = $this->createForm(new ModelRequestType(), $modelRequest, [
            'action' => $this->generateUrl('model_request_registration'),
            'method' => 'POST'
        ]);

        return $form;
    }
}