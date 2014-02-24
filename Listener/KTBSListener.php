<?php

namespace Coat\KtbsBundle\Listener;


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
    {   $token = $this->securityContext->getToken();
        $log = $event->getLog();
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
