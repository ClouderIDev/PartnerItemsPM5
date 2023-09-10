<?php

namespace items;

use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

class ReversedNinjaTask extends Task {
    public function __construct(
        public Player $player,
        public Player $teleport,
        public $time = 3,
    ) {}
    
    public function onRun(): void {
        if ($this->time !== 0) {
            --$this->time;
        }
        
        if ($this->player === null || !$this->player->isOnline() || $this->player->isClosed()) {
            $this->getHandler()->cancel();
            return;
        }
        
        if ($this->time <= 7 && $this->time >= 1) {
            $message = "§cThe player §c§l{$this->player->getName()} §r§cwill teleport to you in §c§l{$this->time} §r§cseconds.";
            $this->teleport->sendMessage($message);
        }
        
        if($this->time <= 0){
            $this->player->teleport($this->teleport->getLocation());
            $this->getHandler()->cancel();
        }
    }
}