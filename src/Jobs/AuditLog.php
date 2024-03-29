<?php

namespace CCM\Leads\Jobs;

use App\Services\AuditController;

class AuditLog extends Job
{
    protected $params;
    protected $logsService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;;
        $this->logsService = new AuditController();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        lumenLog("Jobs");
        $this->params = collect($this->params);
        $this->logsService->index($this->params);
    }
}
