<?php

namespace CCM\Leads\Jobs;

use CCM\Leads\Services\BroadcastService;

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
        $this->broadcastService = new BroadcastService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->broadcastService->sendNotification($this->params);
    }
}
