<?php

namespace Telegram\Bot\Objects;

use Telegram\Bot\Helpers\Emojify;

/**
 * Class Message.
 *
 *
 * @method int              getMessageId()              Unique message identifier.
 * @method User             getFrom()                   (Optional). Sender, can be empty for messages sent to channels.
 * @method int              getDate()                   Date the message was sent in Unix time.
 * @method Chat             getChat()                   Conversation the message belongs to.
 * @method User             getForwardFrom()            (Optional). For forwarded messages, sender of the original message.
 * @method int              getForwardDate()            (Optional). For forwarded messages, date the original message was sent in Unix time.
 * @method Message          getReplyToMessage()         (Optional). For replies, the original message. Note that the Message object in this field will not contain further reply_to_message fields even if it itself is a reply.
 * @method MessageEntity[]  getEntities()               (Optional). For text messages, special entities like usernames, URLs, bot commands, etc. that appear in the text.
 * @method Audio            getAudio()                  (Optional). Message is an audio file, information about the file.
 * @method Document         getDocument()               (Optional). Message is a general file, information about the file.
 * @method PhotoSize[]      getPhoto()                  (Optional). Message is a photo, available sizes of the photo.
 * @method Sticker          getSticker()                (Optional). Message is a sticker, information about the sticker.
 * @method Video            getVideo()                  (Optional). Message is a video, information about the video.
 * @method Voice            getVoice()                  (Optional). Message is a voice message, information about the file.
 * @method Contact          getContact()                (Optional). Message is a shared contact, information about the contact.
 * @method Location         getLocation()               (Optional). Message is a shared location, information about the location.
 * @method Venue            getVenue()                  (Optional). Message is a venue, information about the venue.
 * @method User             getNewChatMember()          (Optional). A new member was added to the group, information about them (this member may be the bot itself).
 * @method User             getLeftChatMember()         (Optional). A member was removed from the group, information about them (this member may be the bot itself).
 * @method string           getNewChatTitle()           (Optional). A chat title was changed to this value.
 * @method PhotoSize[]      getNewChatPhoto()           (Optional). A chat photo was change to this value.
 * @method bool             getDeleteChatPhoto()        (Optional). Service message: the chat photo was deleted.
 * @method bool             getGroupChatCreated()       (Optional). Service message: the group has been created.
 * @method bool             getSupergroupChatCreated()  (Optional). Service message: the super group has been created.
 * @method bool             getChannelChatCreated()     (Optional). Service message: the channel has been created.
 * @method int              getMigrateToChatId()        (Optional). The group has been migrated to a supergroup with the specified identifier, not exceeding 1e13 by absolute value.
 * @method int              getMigrateFromChatId()      (Optional). The supergroup has been migrated from a group with the specified identifier, not exceeding 1e13 by absolute value.
 * @method Message          getPinnedMessage()          (Optional). Specified message was pinned. Note that the Message object in this field will not contain further reply_to_message fields even if it is itself a reply.
 */
class Message extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'from'             => User::class,
            'chat'             => Chat::class,
            'forward_from'     => User::class,
            'reply_to_message' => self::class,
            'entities'         => MessageEntity::class,
            'audio'            => Audio::class,
            'document'         => Document::class,
            'photo'            => PhotoSize::class,
            'sticker'          => Sticker::class,
            'video'            => Video::class,
            'voice'            => Voice::class,
            'contact'          => Contact::class,
            'location'         => Location::class,
            'venue'            => Venue::class,
            'new_chat_member'  => User::class,
            'left_chat_member' => User::class,
            'new_chat_photo'   => PhotoSize::class,
            'pinned_message'   => Message::class,
        ];
    }

    /**
     * (Optional). For text messages, the actual UTF-8 text of the message.
     *
     * @param bool $unemojify
     *
     * @return string
     */
    public function getText($unemojify = true)
    {
        $text = $this->get('text');
        return $unemojify ? Emojify::translate($text) : $text;
    }

    /**
     * (Optional). Caption for the document, photo or video contact.
     *
     * @param bool $unemojify
     *
     * @return string
     */
    public function getCaption($unemojify = true)
    {
        $caption = $this->get('caption');
        return $unemojify ? Emojify::translate($caption) : $caption;
    }

}
