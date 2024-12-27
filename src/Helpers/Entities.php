<?php

namespace Telegram\Bot\Helpers;

/**
 * Class Entities.
 */
final class Entities
{
    /** @var array Entities from Telegram */
    private array $entities = [];

    /** @var int Formatting Mode: 0:Markdown | 1:HTML */
    private int $mode = 0;

    /**
     * Entities constructor.
     */
    public function __construct(
        private string $text
    ) {
    }

    public static function format(string $text): self
    {
        return new self($text);
    }

    /**
     * @return $this
     */
    public function withEntities(array $entities): self
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * Format it to markdown style.
     */
    public function toMarkdown(): string
    {
        $this->mode = 0;

        return $this->apply();
    }

    /**
     * Format it to HTML syntax.
     */
    public function toHTML(): string
    {
        $this->mode = 1;

        return $this->apply();
    }

    /**
     * Apply format for given text and entities.
     */
    private function apply(): string
    {
        $syntax = $this->syntax();

        foreach (array_reverse($this->entities) as $entity) {
            $value = mb_substr($this->text, $entity['offset'], $entity['length']);
            $type = $entity['type'];
            $replacement = match ($type) {
                'text_link' => sprintf($syntax[$type][$this->mode], $value, $entity['url']),
                'text_mention' => sprintf($syntax[$type][$this->mode], $entity['user']['username']),
                default => sprintf($syntax[$type][$this->mode], $value),
            };

            $this->text = substr_replace($this->text, $replacement, $entity['offset'], $entity['length']);
        }

        return $this->text;
    }

    /**
     * Formatting Syntax.
     *
     * @return array{bold: string[], italic: string[], code: string[], pre: string[], text_mention: string[], text_link: string[]}
     */
    private function syntax(): array
    {
        // No need of any special formatting for these entity types.
        // 'url', 'bot_command', 'hashtag', 'cashtag', 'email', 'phone_number', 'mention'

        return [
            'bold' => ['*%s*', '<strong>%s</strong>'],
            'italic' => ['_%s_', '<i>%s</i>'],
            'code' => ['`%s`', '<code>%s</code>'],
            'pre' => ["```\n%s```", '<pre>%s</pre>'],
            'text_mention' => ['[%1$s](tg://user?id=%1$s)', '<a href="tg://user?id=%1$s">%1$s</a>'],
            'text_link' => ['[%s](%s)', '<a href="%2$s">%1$s</a>'],
            // underline doesn't have a markdown style
            'underline' => ['%s', '<u>%s</u>']
        ];
    }
}
