<?php

namespace CCM\Leads\Jobs;

use App\Services\NotificationController;

class SendNotification extends Job
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
        $this->params = $params;
        $this->broadcastService = new NotificationController();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->params = collect($this->params);
        
        $this->broadcastService->index($this->params);
    
    }
}
