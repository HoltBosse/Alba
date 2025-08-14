<?php

use HoltBosse\Alba\Core\{Messages, MessageType};

test('MessageType enum has correct values', function () {
    expect(MessageType::Info->value)->toBe('info');
    expect(MessageType::Success->value)->toBe('success');
    expect(MessageType::Danger->value)->toBe('danger');
    expect(MessageType::Warning->value)->toBe('warning');
});

test('Messages::add stores messages in session', function () {
    session_destroy();
    session_start();

    $result = Messages::add(MessageType::Success, 'Test message');
    expect($result)->toBeTrue();
    expect($_SESSION['flash_messages'][MessageType::Success->value][0])->toBe('Test message');
});

test('Messages::add creates session array if not present', function () {
    session_destroy();
    session_start();

    Messages::add(MessageType::Info, 'Info message');
    expect($_SESSION['flash_messages'][MessageType::Info->value][0])->toBe('Info message');
});

test('Messages::clear removes messages by type', function () {
    session_destroy();
    session_start();

    $_SESSION['flash_messages'] = [
        MessageType::Info->value => ['msg1'],
        MessageType::Danger->value => ['msg2'],
    ];
    $messages = new Messages();
    $messages->clear(MessageType::Info);

    //@phpstan-ignore-next-line
    expect(isset($_SESSION['flash_messages'][MessageType::Info->value]))->toBeFalse();
    //@phpstan-ignore-next-line
    expect(isset($_SESSION['flash_messages'][MessageType::Danger->value]))->toBeTrue();
});

test('Messages::clear removes all messages if no type given', function () {
    session_destroy();
    session_start();

    $_SESSION['flash_messages'] = [
        MessageType::Info->value => ['msg1'],
        MessageType::Danger->value => ['msg2'],
    ];
    $messages = new Messages();
    $messages->clear();
    
    //@phpstan-ignore-next-line
    expect(isset($_SESSION['flash_messages']))->toBeFalse();
});