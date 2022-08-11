<?php

namespace CCM\Leads\Jobs;

use App\Services\LoginLogsController;

class LoginLog extends Job
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
        $this->params = $params;
        $this->logsService = new LoginLogsController();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        lumenLog("job");
        $this->params = collect($this->params);
        $this->logsService->index($this->params);
    }
}
