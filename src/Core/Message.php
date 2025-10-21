<?php
namespace HoltBosse\Alba\Core;

Class Message {
    public MessageType $type;
    public ?string $message;
    public ?string $redirectTo;
    public bool $success;

    function __construct(bool $success, MessageType $type, ?string $message=null, ?string $redirectTo=null) {
        $this->success = $success;
        $this->type = $type;
        $this->message = $message;
        $this->redirectTo = $redirectTo;
    }

    public function hasMessage(): bool {
        return isset($this->message) && $this->message != "";
    }

    public function toMessagesAddArgsArray(): array {
        return [
            $this->type,
            $this->message ?? "", //it is recommended that the calling code should check has message before hand
            $this->redirectTo
        ];
    }

    public function toQueueMessageArgsArray(): array {
        return [
            $this->message ?? "",
            $this->type,
            $this->redirectTo,
        ];
    }
}