<?php

    namespace CCM\Leads\Controller;

    use Monolog\Formatter\JsonFormatter;
    use Illuminate\Log\Logger;

    class DatadogFormatter
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
