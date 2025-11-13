<?php
namespace HoltBosse\Alba\Core;

//'info', 'success', 'danger', 'warning'
enum MessageType: string {
    case Info = 'info';
    case Success = "success";
    case Danger = "danger";
    case Warning = "warning";

    public function toCssClass(): string {
        return "is-" . $this->value;
    }
}