<?php

namespace CCM\Leads\Jobs;

use CCM\Leads\Services\LogsService;

class AuditLog extends Job
{
    protected $params;

    public $tries = 3;

    protected $logsService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params->toArray();
        $this->logsService = new LogsService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->logsService->audit(collect($this->params));
    }
}
