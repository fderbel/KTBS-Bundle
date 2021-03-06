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
use JMS\DiExtraBundle\Annotation as DI;

// for ktbs
//use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Event\Log\LogRoleSubscribeEvent;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserCreateEvent;
use Coat\Ktbs\KtbsConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * @DI\Service
 */
 
 
class KTBSListener 

{  
    
    /**
     * 
     * @DI\InjectParams({
     *     
     * })
     * 
     */
     
    public function __construct( ){
        
      }
    
    public function createLog(LogCreateEvent $event){  
    
        $log = $event->getLog();
        // get user  
        if ($log->getDoer() === null)
            {$user=$log->getReceiver();}
        else 
            { $user=$log->getDoer();}
        // case with action 
       
        switch ($log->getAction()){
            case LogWorkspaceToolReadEvent::ACTION :
            {           //enter in the workspace and commucation with collector client
                       
                        if (($log->getWorkspace() !== null) && ($user !== null))
                        {
                              $ktbs = new KtbsConfig($user,$log->getWorkspace()) ;
                              $ktbs->createTrace();
                              $trace_Name   = $ktbs->getTraceName();
                              $Base_URI     = $ktbs->getBaseURI();
                              $Model_URI    = $ktbs->getModelURI();
                              setcookie("TraceName",$trace_Name);
                              setcookie("BAseURI",$Base_URI);
                              setcookie("Model_URI",$Model_URI);
                             
                        }
             }
             default :
             {      //observe event
                        
                        if (($log->getWorkspace() !== null) && ($user !== null) )
	                    {
	                         $ktbs = new KtbsConfig($user,$log->getWorkspace()) ;
	                         $ktbs->createObsel ($log,$user);
	                    }
             } 
      
        }
    }

   
     /**
     * @DI\Observe("claroline.log.create")
     *
     * @param \Claroline\CoreBundle\Event\LogCreateEvent $event
     */
    public function onLog(LogCreateEvent $event){
        $this->createLog($event);
    }
    
}

