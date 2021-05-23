<?php

namespace Telegram\Bot\Traits;

use Telegram\Bot\Objects\Update;

const HIGHEST_ID = -1;

/**
 * CommandsHandler.
 */
trait CommandsHandler
{
    /**
     * Get all registered commands.
     *
     * @return array
     */
    public function getCommands(): array
    {
       // return $this->getCommandBus()->getCommands();
    }

    /**
     * Processes Inbound Command.
     *
     * @param bool $webhook
     *
     * @return Update|Update[]
     */
    public function commandsHandler(bool $webhook = false)
    {
       // return $webhook ? $this->useWebHook() : $this->useGetUpdates();
    }

    protected function useWebHook()
    {
//        $update = $this->getWebhookUpdate();
//        $this->processCommand($update);
//
//        return $update;
    }

    /**
     * Process the update object for a command using the getUpdates method.
     *
     * @return Update[]
     */
    protected function useGetUpdates(): array
    {
        $updates = $this->getUpdates();
        $highestId = HIGHEST_ID;

        foreach ($updates as $update) {
            $highestId = $update->updateId;
            $this->processCommand($update);
        }

        //An update is considered confirmed as soon as getUpdates is called with an offset higher than it's update_id.
        if ($highestId != HIGHEST_ID) {
            $this->markUpdateAsRead($highestId);
        }

        return $updates;
    }

    /**
     * Mark updates as read.
     *
     * @param $highestId
     *
     * @return Update[]
     */
    protected function markUpdateAsRead($highestId): array
    {
        $params = [];
        $params['offset'] = $highestId + 1;
        $params['limit'] = 1;

        return $this->getUpdates($params);
    }

    /**
     * Check update object for a command and process.
     *
     * @param Update $update
     */
    public function processCommand(Update $update)
    {
        //$this->getCommandBus()->handler($update);
    }

    /**
     * Helper to Trigger Command.
     *
     * @param string $name   Command Name
     * @param Update $update Update Object
     * @param null   $entity
     *
     * @return mixed
     */
    public function triggerCommand(string $name, Update $update, $entity = null)
    {
//        $entity = $entity ?? ['offset' => 0, 'length' => strlen($name) + 1, 'type' => "bot_command"];
//
//        return $this->getCommandBus()->execute(
//            $name,
//            $update,
//            $entity
//        );
    }
}
