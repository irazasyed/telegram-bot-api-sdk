<?php

namespace Telegram\Bot\Objects\InlineQuery;

/**
 * Class InlineQueryResultCachedDocument
 *
 * <code>
 * $params = [
 *   'id'                       => '',
 *   'title'                    => '',
 *   'document_file_id'         => '',
 *   'description'              => '',
 *   'caption'                  => '',
 *   'reply_markup'             => '',
 *   'input_message_content'    => '',
 * ];
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultcacheddocument
 *
 * @method $this setId($string)                     Unique identifier for this result, 1-64 bytes
 * @method $this setTitle($string)                  Title for the result
 * @method $this setDocumentFileId($string)         A valid file identifier for the file
 * @method $this setDescription($string)            Optional. Short description of the result
 * @method $this setCaption($string)                Optional. Caption of the document to be sent, 0-200 characters
 * @method $this setReplyMarkup($object)            Optional. Inline keyboard attached to the message
 * @method $this setInputMessageContent($object)    Optional. Content of the message to be sent instead of the file
 */
class InlineQueryResultCachedDocument extends InlineBaseObject
{
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->put('type', 'document');
    }
}