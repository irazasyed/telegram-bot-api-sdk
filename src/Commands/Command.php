<?php

namespace Telegram\Bot\Commands;

use Illuminate\Support\Collection;
use Telegram\Bot\Answers\Answerable;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\MessageEntity;
use Telegram\Bot\Objects\Update;

/**
 * Class Command.
 */
abstract class Command implements CommandInterface
{
    use Answerable;

    /**
     * @var string
     */
    private const OPTIONAL_BOT_NAME = '(?:\@[\w]*bot\b)?\s+';

    /**
     * The name of the Telegram command.
     * Ex: help - Whenever the user sends /help, this would be resolved.
     */
    protected string $name;

    /** @var string[] Command Aliases - Helpful when you want to trigger command with more than one name. */
    protected array $aliases = [];

    /** @var string The Telegram command description. */
    protected string $description;

    /** @var array Holds parsed command arguments */
    protected array $arguments = [];

    /** @var string Command Argument Pattern */
    protected string $pattern = '';

    /** @var array Details of the current entity this command is responding to - offset, length, type etc */
    protected array $entity = [];

    /**
     * Get the Command Name.
     *
     * The name of the Telegram command.
     * Ex: help - Whenever the user sends /help, this would be resolved.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set Command Name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Command Aliases.
     *
     * Helpful when you want to trigger command with more than one name.
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Set Command Aliases.
     */
    public function setAliases(array|string $aliases): self
    {
        $this->aliases = (array) $aliases;

        return $this;
    }

    /**
     * Get Command Description.
     *
     * The Telegram command description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set Command Description.
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function argument(string $name, mixed $default = null): mixed
    {
        return $this->arguments[$name] ?? $default;
    }

    /**
     * Get Command Arguments.
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Set Command Arguments.
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Get Command Arguments Pattern.
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Get Command Arguments Pattern.
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Process Inbound Command.
     */
    public function make(Api $telegram, Update $update, array $entity): mixed
    {
        $this->telegram = $telegram;
        $this->update = $update;
        $this->entity = $entity;
        $this->arguments = $this->parseCommandArguments();

        return $this->handle();
    }

    /**
     * Parse Command Arguments.
     */
    protected function parseCommandArguments(): array
    {
        if ($this->pattern === '') {
            return [];
        }


        // Generate the regex needed to search for this pattern
        [$pattern, $arguments] = $this->makeRegexPattern();

        preg_match("%{$pattern}%ixmu", $this->relevantMessageSubString(), $matches, PREG_UNMATCHED_AS_NULL);

        return $this->formatMatches($matches, $arguments);
    }

    private function makeRegexPattern(): array
    {
        preg_match_all(
            pattern: '#\{\s*(?<name>\w+)\s*(?::\s*(?<pattern>\S+)\s*)?}#ixmu',
            subject: $this->pattern,
            matches: $matches,
            flags: PREG_SET_ORDER
        );

        $patterns = collect($matches)
            ->mapWithKeys(function ($match): array {
                $pattern = $match['pattern'] ?? '[^ ]++';

                return [
                    $match['name'] => "(?<{$match['name']}>{$pattern})?",
                ];
            })
            ->filter();

        $commandName = ($this->aliases === []) ? $this->name : implode('|', [$this->name, ...$this->aliases]);

        return [
            sprintf('(?:\/)%s%s%s', "(?:{$commandName})", self::OPTIONAL_BOT_NAME, $patterns->implode('\s*')),
            $patterns->keys()->all(),
        ];
    }

    private function relevantMessageSubString(): string
    {
        //Get all the bot_command offsets in the Update object
        $commandOffsets = $this->allCommandOffsets();

        if ($commandOffsets->isEmpty()) {
            return $this->getUpdate()->getText() ?? '';
        }

        //Extract the current offset for this command and, if it exists, the offset of the NEXT bot_command entity
        $splice = $commandOffsets->splice(
            $commandOffsets->search($this->entity['offset']),
            2
        );

        return $splice->count() === 2 ? $this->cutTextBetween($splice) : $this->cutTextFrom($splice);
    }

    private function allCommandOffsets(): Collection
    {
        return $this->getUpdate()->getMessage()->get('entities', collect())
            ->filter(static fn (MessageEntity $entity): bool => $entity->type === 'bot_command')
            ->pluck('offset') ?? collect();
    }

    private function cutTextBetween(Collection $splice): string
    {
        return mb_substr(
            $this->getUpdate()->getText() ?? '',
            $splice->first(),
            $splice->last() - $splice->first()
        );
    }

    private function cutTextFrom(Collection $splice): string
    {
        return mb_substr(
            $this->getUpdate()->getText() ?? '',
            $splice->first()
        );
    }

    private function formatMatches(array $matches, array $arguments): array
    {
        $matches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        return array_merge(array_fill_keys($arguments, null), $matches);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function handle();

    /**
     * Helper to Trigger other Commands.
     */
    protected function triggerCommand(string $command): mixed
    {
        return $this->getCommandBus()->execute($command, $this->update, $this->entity);
    }

    /**
     * Returns an instance of Command Bus.
     */
    public function getCommandBus(): CommandBus
    {
        return $this->telegram->getCommandBus();
    }
}
