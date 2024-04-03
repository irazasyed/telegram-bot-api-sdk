<?php

use Telegram\Bot\Exceptions\TelegramSDKException;

uses(\Telegram\Bot\Methods\Forum::class);
test('Chat id have to be specified for create topic', function () {
    $this->expectException(TelegramSDKException::class);
    $this->createForumTopic([
        'chat_id' => '-',
        'name' => 'Test Chat',
    ]);
});

test('Chat id and thread id have to be specified for edit topic', function () {
    $this->expectException(TelegramSDKException::class);
    $this->editForumTopic([
        'chat_id' => '-',
        'message_thread_id' => '',
        'name' => 'Test Chat',
    ]);
});

test('Chat id and thread id have to be specified for close topic', function () {
    $this->expectException(TelegramSDKException::class);
    $this->closeForumTopic([
        'chat_id' => '-',
        'message_thread_id' => '',
    ]);
});

test('Chat id and thread id have to be specified for reopen topic', function () {
    $this->expectException(TelegramSDKException::class);
    $this->reopenForumTopic([
        'chat_id' => '-',
        'message_thread_id' => '',
    ]);
});

test('Chat id and thread id have to be specified for delete topic', function () {
    $this->expectException(TelegramSDKException::class);
    $this->deleteForumTopic([
        'chat_id' => '-',
        'message_thread_id' => '',
    ]);
});