<?php

/**
  *  A free plugin for PocketMine-MP.
  *	
  *	Copyright (c) AEDXDEV
  *  
  *	Youtube: AEDX DEV 
  *	Discord: aedxdev
  *	Github: AEDXDEV
  *	Email: aedxdev@gmail.com
  *	Donate: https://paypal.me/AEDXDEV
  *   
  *        This program is free software: you can redistribute it and/or modify
  *        it under the terms of the GNU General Public License as published by
  *        the Free Software Foundation, either version 3 of the License, or
  *        (at your option) any later version.
  *
  *        This program is distributed in the hope that it will be useful,
  *        but WITHOUT ANY WARRANTY; without even the implied warranty of
  *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *        GNU General Public License for more details.
  *
  *        You should have received a copy of the GNU General Public License
  *        along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *         
  */

namespace AEDXDEV\PlayersList;

use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Models\Messages\Embed\Field;
use JaxkDev\DiscordBot\Models\Messages\Embed\Footer;
use JaxkDev\DiscordBot\Plugin\Events\MessageSent;
use JaxkDev\DiscordBot\Plugin\Api;
use JaxkDev\DiscordBot\Plugin\Main as DiscordBot;
use JaxkDev\DiscordBot\Plugin\ApiResultion;
use JaxkDev\DiscordBot\Plugin\ApiRejection;
use JaxkDev\DiscordBot\Models\Messages\Message;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;

class Main extends PluginBase implements Listener{
  
  public DiscordBot $discord;
  
  public Config $config;
  
  public function onEnable() : void{
    $discord = $this->getServer()->getPluginManager()->getPlugin("DiscordBot");
    if (!$discord instanceof DiscordBot){
      throw new PluginException("Incompatible dependency 'DiscordBot' detected, see https://github.com/DiscordBot-PMMP/DiscordBot/releases for the correct plugin.");
    }
    $this->discord = $discord;
    $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
      "ServerId" => 1234567890,
      "Command" => "!list"
    ]);
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  public function MessageSent(MessageSent $event){
    $api = $this->discord->getApi();
    $message = $event->getMessage();
    $content = $message->getContent();
    $channel_id = $message->getChannelId();
    $args = explode(" ", $content);
    $args[0] ??= "";
    // command
    if($args[0] == $this->config->get("Command", "!list")){
      $onlines = $this->getServer()->getOnlinePlayers();
      $players = implode("\n", array_map(fn(Player $player) => $player->getName(), $onlines));
      if (($server_id = $this->config->get("ServerId", 1234567890)) == 1234567890){
        $this->getLogger()->info("§cPlease add discord server id in config.yml");
        $this->getServer()->getPluginManager()->disablePlugin($this);
        return false;
      }
      $api->sendMessage($server_id, $channel_id, null, $message->getId(), [new Embed(
        "List Players",
        count($onlines) === 0 ? "There are no players in the server" : count($onlines) . "/" . $this->getServer()->getMaxPlayers(),
        null, time(), null,
        new Footer("List Players v" . $this->getDescription()->getVersion()),
        null, null, null, null, null,
        count($onlines) === 0 ? [] : [new Field("Players", $players, true)]
      )])->otherwise(function(ApiRejection $rejection){
        $this->getLogger()->error("Failed to send command response: " . $rejection->getMessage());
      });
    }
  }
}
