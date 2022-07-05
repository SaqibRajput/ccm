<?php

    namespace CCM\Leads\Controllers;

    use CCM\Leads\Controllers\Controller as LeadsController;

    use Monolog\Formatter\JsonFormatter;
    use Illuminate\Log\Logger;

    class DatadogFormatter extends LeadsController
    {
        /**
         * Customize the given logger instance.
         *
         * @param  \Illuminate\Log\Logger  $logger
         * @return void
         */
        public function __invoke(Logger $logger)
        {
             foreach ($logger->getHandlers() as $handler) {
                $formatter = new JsonFormatter();
                $handler->setFormatter($formatter);
             }
        }
    }
