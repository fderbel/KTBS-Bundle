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
            { $user=$log->getDoer();}
            
       if ($log->getAction() === LogUserLoginEvent::ACTION)
            {
            $ktbs = new KtbsConfig() ;
            if ($user !== null)
                {$ktbs->createBase($user);}
            }
        else 
            // create Trace in the inscription workspace
            if ($log->getAction() === LogRoleSubscribeEvent::ACTION_USER) 
             {  
             $ktbs = new KtbsConfig() ;
             if ($ktbs->exist)
             {
                  if (($log->getWorkspace() !== null) && ($user !== null))
                    {$ktbs->createTrace($user,$log->getWorkspace());}
             }
             }
             else 
                // commucation with collector client
                
                    if ($log->getAction() === LogWorkspaceToolReadEvent::ACTION)
                        {
                        
                          if (($log->getWorkspace() !== null) && ($user !== null))
                          {
                            $ktbs = new KtbsConfig() ;
                            if ($ktbs->exist)
                            {
                            $DataObsel= $ktbs->DataObsel($user,$log->getWorkspace());
                            $trace_Name = $DataObsel["TraceName"];
                            $Base_URI = $DataObsel["BaseURI"];
                            $Model_URI= $DataObsel["model"] ;
                           
          
                           setcookie("TraceName",$trace_Name);
                           setcookie("BAseURI",$Base_URI);
                           setcookie("Model_URI",$Model_URI);
                           }
                           
                            }
                        }
	                else
	                    {  
	                        if (($log->getWorkspace() !== null) && ($user !== null) && ($log->getDoer()!== null))
	                            {
	                               $ktbs = new KtbsConfig() ;
	                               if ($ktbs->exist)
	                               {
	                               $ktbs->createObsel ($user,$log->getWorkspace(),$log);
	                               }
	                               
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

