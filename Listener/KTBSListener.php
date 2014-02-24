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
//use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Event\Log\LogRoleSubscribeEvent;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserCreateEvent;
use Coat\Ktbs\KtbsConfig;


/**
 * @DI\Service
 */
 
 
class KTBSListener

{   /**
     * @DI\InjectParams({
     *     
     * })
     */
    public function __construct()
    {
        
    }

    public function createLog(LogCreateEvent $event)
    {   
        $log = $event->getLog();
        // bout de code for ktbs 
           // get user  
         if ($log->getDoer() === null)
            {$user=$log->getReceiver();}
        else 
             $user=$log->getDoer();
            // create Base Trace in the inscription event
                
         if ($log->getAction() === LogUserCreateEvent::ACTION)
            {
            $ktbs = new KtbsConfig() ;
            $ktbs->createBase($user);
            }
            
        else 
           if ($log->getAction() === LogUserLoginEvent::ACTION)
            {
            $ktbs = new KtbsConfig() ;
            $ktbs->createBase($user);
            }
        else 
            // create Trace in the inscription workspace
            if ($log->getAction() === LogRoleSubscribeEvent::ACTION_USER) 
             {   
             $ktbs = new KtbsConfig() ;
             $ktbs->createTrace($user,$log->getWorkspace());
             }
             else 
                // commucation with collector client
                    if ($log->getAction() === LogWorkspaceToolReadEvent::ACTION)
                        {
                            $ktbs = new KtbsConfig() ;
                            $DataObsel= $ktbs->DataObsel($user,$log->getWorkspace());
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
	                        if ($log->getWorkspace() !== null) 
	                            {
	                                $ktbs = new KtbsConfig() ;
	                                $ktbs->createObsel ($user,$log->getWorkspace(),$log);
	                            }
	                    }
    }

   
     /**
     * @DI\Observe("claroline.log.create")
     *
     * @param \Claroline\CoreBundle\Event\LogCreateEvent $event
     */
    public function onLog(LogCreateEvent $event)
    {
         $this->createLog($event);
      
    }
}

