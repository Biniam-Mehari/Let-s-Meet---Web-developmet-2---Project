<?php
namespace Models;

class Message {

    public int $messageId;
    public int $fromUserId;
    public int $toUserId;
    public string $message;
    public string $date;
  
}

?>