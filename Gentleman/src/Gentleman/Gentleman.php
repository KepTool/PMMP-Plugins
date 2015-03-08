<?php

namespace Gentleman;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

class Gentleman extends PluginBase implements Listener {
	public $list, $messages;
	public $badqueue = [ ];
	public function onEnable() {
		@mkdir ( $this->getDataFolder () );
		$this->initMessage ();
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$result = $this->checkSwearWord ( "시발" );
		if ($result != false) {
			echo "욕설을 감지했습니다 ! : " . $result . "\n";
		} else {
			echo "욕설을 감지하지 못했습니다 !" . "\n";
		}
		// $this->parseXE_DB_to_YML (); //*CONVERT ONLY*
	}
	public function initMessage() {
		$this->saveResource ( "messages.yml", false );
		$this->saveResource ( "badwords.yml", false );
		$this->messages = (new Config ( $this->getDataFolder () . "messages.yml", Config::YAML ))->getAll ();
		$this->list = (new Config ( $this->getDataFolder () . "badwords.yml", Config::YAML ))->getAll ();
		$this->makeQueue ();
	}
	public function makeQueue() {
		foreach ( $this->list ["badwords"] as $badword )
			$this->badqueue [] = $this->cutWords ( $badword );
	}
	public function cutWords($str) {
		$cut_array = array ();
		for($i = 0; $i < mb_strlen ( $str, "UTF-8" ); $i ++)
			array_push ( $cut_array, mb_substr ( $str, $i, 1, 'UTF-8' ) );
		return $cut_array;
	}
	public function get($var) {
		return $this->messages [$this->messages ["default-language"] . "-" . $var];
	}
	public function parseXE_DB_to_YML() {
		$parseBadwords = file_get_contents ( $this->getDataFolder () . "badwords.txt" );
		$parseBadwords = mb_convert_encoding ( $parseBadwords, "UTF-8", "CP949" );
		$parseBadwords = explode ( ' ', $parseBadwords );
		
		$list = [ 
				"badwords" => [ ] ];
		foreach ( $parseBadwords as $badword )
			$list ["badwords"] [] = $badword;
		
		$this->list = new Config ( $this->getDataFolder () . "badwords.yml", Config::YAML, $list );
		$this->list->save ();
	}
	public function checkSwearWord($word) {
		$word = $this->cutWords ( $word );
		foreach ( $this->badqueue as $queue ) { // 비속어단어별 [바,보]
			$wordLength = count ( $queue );
			$find_count = [ ];
			foreach ( $queue as $match_alpha ) { // 비속어글자별 [바], [보]
				foreach ( $word as $used_alpha ) // 유저글자별 [ 나,는,바,보,다]
					if ($match_alpha == $used_alpha) {
						echo "찾음" . $match_alpha . " " . $used_alpha . "\n";
						$find_count [$match_alpha] = 0; // ["바"=>0 "보"=0]
						break;
					}
				if ($wordLength == count ( $find_count )) return implode ( "", $queue );
			}
		}
		return false;
	}
}

?>