<?php

namespace CCM\Leads\Jobs;

use App\Services\EventLogController;

class EventLog extends Job
{
    protected $params;

    public $tries = 3;

    protected $logService;

    protected $isObject;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
        $this->logService = new EventLogController();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        lumenLog("EventLog");

        $this->params = collect($this->params);
        $this->logService->index($this->params);

    }
}
