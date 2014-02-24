<?php

namespace Coat\Ktbs;


use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogGroupDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogResourceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceRoleDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
// for ktbs
use Claroline\CoreBundle\Event\Log\LogRoleSubscribeEvent;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserCreateEvent;
use Coat\Ktbs\KtbsConfig;


/**
 * @DI\Service
 */
 
 
class KTBSListener

{

    private $om;
    private $securityContext;
    private $container;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "context"        = @DI\Inject("security.context"),
     *     "container"      = @DI\Inject("service_container"),
     *     "roleManager"    = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        SecurityContextInterface $context,
        $container,
        RoleManager $roleManager
    )
    {
        $this->om = $om;
        $this->securityContext = $context;
        $this->container = $container;
        $this->roleManager = $roleManager;
    }

    public function createLog(LogGenericEvent $event)
    {
        //Add doer details
        $doer = null;
        $doerIp = null;
        $doerSessionId = null;
        $doerType = null;

        //Event can override the doer
        if ($event->getDoer() === null) {
            $token = $this->securityContext->getToken();
            if ($token === null) {
                $doer = null;
                $doerType = Log::doerTypePlatform;
            } else {
                if ($token->getUser() === 'anon.') {
                    $doer = null;
                    $doerType = Log::doerTypeAnonymous;
                } else {
                    $doer = $token->getUser();
                    $doerType = Log::doerTypeUser;
                }
                $request = $this->container->get('request');
                $doerSessionId = $request->getSession()->getId();
                $doerIp = $request->getClientIp();
            }
        } else {
            $doer = $event->getDoer();
            $doerType = Log::doerTypeUser;
        }

        $log = new Log();

        //Simple type properties
        $log->setAction($event->getAction());
        $log->setToolName($event->getToolName());
        $log->setIsDisplayedInAdmin($event->getIsDisplayedInAdmin());
        $log->setIsDisplayedInWorkspace($event->getIsDisplayedInWorkspace());

        //Object properties
        $log->setOwner($event->getOwner());
        if (!($event->getAction() === LogUserDeleteEvent::ACTION && $event->getReceiver() === $doer)) {
            //Prevent self delete case
            $log->setDoer($doer);
        }
        $log->setDoerType($doerType);

        $log->setDoerIp($doerIp);
        $log->setDoerSessionId($doerSessionId);
        if ($event->getAction() !== LogUserDeleteEvent::ACTION) {
            //Prevent user delete case
            $log->setReceiver($event->getReceiver());
        }
        if ($event->getAction() !== LogGroupDeleteEvent::ACTION) {
            $log->setReceiverGroup($event->getReceiverGroup());
        }
        if (
            !(
                $event->getAction() === LogResourceDeleteEvent::ACTION &&
                $event->getResource() === $event->getWorkspace()
            )
        ) {
            //Prevent delete workspace case
            $log->setWorkspace($event->getWorkspace());
        }
        if ($event->getAction() !== LogResourceDeleteEvent::ACTION) {
            //Prevent delete resource case
            $log->setResourceNode($event->getResource());
        }
        if ($event->getAction() !== LogWorkspaceRoleDeleteEvent::ACTION) {
            //Prevent delete role case
            $log->setRole($event->getRole());
        }

        if ($doer !== null) {
            $platformRoles = $this->roleManager->getPlatformRoles($doer);

            foreach ($platformRoles as $platformRole) {
                $log->addDoerPlatformRole($platformRole);
            }

            if ($event->getWorkspace() !== null) {
                $workspaceRoles = $this->roleManager->getWorkspaceRolesForUser($doer, $event->getWorkspace());

                foreach ($workspaceRoles as $workspaceRole) {
                    $log->addDoerWorkspaceRole($workspaceRole);
                }
            }
        }
        if ($event->getResource() !== null) {
            $log->setResourceType($event->getResource()->getResourceType());
        }

        //Json_array properties
        $details = $event->getDetails();

        if ($details === null) {
            $details = array();
        }

        if ($doer !== null) {
            $details['doer'] = array(
                'firstName' => $doer->getFirstName(),
                'lastName' => $doer->getLastName()
            );

            if (count($log->getDoerPlatformRoles()) > 0) {
                $doerPlatformRolesDetails = array();
                foreach ($log->getDoerPlatformRoles() as $platformRole) {
                    $doerPlatformRolesDetails[] = $platformRole->getTranslationKey();
                }
                $details['doer']['platformRoles'] = $doerPlatformRolesDetails;
            }
            if (count($log->getDoerWorkspaceRoles()) > 0) {
                $doerWorkspaceRolesDetails = array();
                foreach ($log->getDoerWorkspaceRoles() as $workspaceRole) {
                    $doerWorkspaceRolesDetails[] = $workspaceRole->getTranslationKey();
                }
                $details['doer']['workspaceRoles'] = $doerWorkspaceRolesDetails;
            }
        }
        $log->setDetails($details);

        $this->om->persist($log);
        $this->om->flush();

        $createLogEvent = new LogCreateEvent($log);
        $this->container->get('event_dispatcher')->dispatch(LogCreateEvent::NAME, $createLogEvent);
        // bout de code for ktbs 
           // get user  
         if ($token->getUser() === 'anon.')
            {$user=$event->getReceiver();}
        else 
             $user=$token->getUser();
            // create Base Trace in the inscription event
                
         if ($event->getAction() === LogUserCreateEvent::ACTION)
            {
            $ktbs = new KtbsConfig() ;
            $ktbs->createBase($user);
            }
            
        else 
           if ($event->getAction() === LogUserLoginEvent::ACTION)
            {
            $ktbs = new KtbsConfig() ;
            $ktbs->createBase($user);
            }
        else 
            // create Trace in the inscription workspace
            if ($event->getAction() === LogRoleSubscribeEvent::ACTION_USER) 
             {   
             $ktbs = new KtbsConfig() ;
             $ktbs->createTrace($user,$event->getWorkspace());
             }
             else 
                // commucation with collector client
                    if ($event->getAction() === LogWorkspaceToolReadEvent::ACTION)
                        {
                            $ktbs = new KtbsConfig() ;
                            $DataObsel= $ktbs->DataObsel($user,$event->getWorkspace());
                            $trace_Name = $DataObsel["TraceName"];
                            $Base_URI = $DataObsel["BaseURI"];
                            $Model_URI= $DataObsel["model"] ;
                            $DataSend = JSON_encode (array ("TraceName"=>$DataObsel["TraceName"],"BaseURI"=>$DataObsel["BaseURI"],"ModelURI"=>$DataObsel["model"])) ;
                           // $DataSend = '{TraceName:'.$DataObsel["TraceName"].',BaseURI:'.$DataObsel["BaseURI"].',ModelURI:'.$DataObsel["model"].'}';
                            //var_dump ($DataSend);
                            header ("Trace_Information: $DataSend");
                            //var_dump(apache_request_headers());
                            //var_dump(apache_response_headers());
                           
                            
                        }
	                else
	                    {
	                        if ($event->getWorkspace() !== null) 
	                            {
	                                $ktbs = new KtbsConfig() ;
	                                $ktbs->createObsel ($user,$event->getWorkspace(),$log);
	                            }
	                    }
    }

   
    /**
     * @DI\Observe("log")
     *
     * @param LogGenericEvent $event
     */
    public function onLog(LogGenericEvent $event)
    {
         
          
        //if (!($event instanceof LogNotRepeatableInterface) or !$this->isARepeat($event)) {
            $this->createLog($event);
       // }
    }
}
