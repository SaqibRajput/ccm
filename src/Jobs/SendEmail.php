<?php

namespace CCM\Leads\Jobs;

use App\Services\EmailController;

class SendEmail extends Job
{
    protected $params;

    public $tries = 3;

    protected $broadcastService;

    protected $isObject;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        if(is_object($params)) {
            // getting some error in object so using below code but now it working fine will be removed after proper testing.
//            $this->isObject = true;
//            $params = $params->toArray();
        }

        $this->params = $params;
        $this->broadcastService = new EmailController();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->isObject)
        {
        //    $this->params = collect($this->params);
        }

        $this->params = collect($this->params);
        
        $this->broadcastService->index($this->params);

        //$this->broadcastService->sendEmail($this->params);
    }
}
