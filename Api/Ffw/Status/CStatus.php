<?php

namespace Ffw\Status;

use Api\AppDB;

class CStatus {
	const FLAG_JSON	=	1 << 0;
	const FLAG_DIEONCOMPLETE = 1 << 1;
	const FLAG_RAW = 1 << 2;

	protected static $dieOnComplete = false;
	protected static $json = false;
	static $errorMessages = null;
	static $successMessages = null;
	static $flags = null;
	static $pushedSettings = array();

	static function clearMessages() {
		static::$errorMessages = null;
		static::$successMessages = null;
	}

	static function set($flags) {

		self::$json = ($flags & self::FLAG_JSON) > 0;
		self::$dieOnComplete = ($flags & self::FLAG_DIEONCOMPLETE) > 0;
		self::$flags = $flags;
	}

	static function get() {
		return self::$flags;
	}

	static function pushSettings($newFlags) {
		array_push(self::$pushedSettings, self::$flags );
		self::set($newFlags);
	}

	static function popSettings() {
		$flags = array_pop(self::$pushedSettings);
		self::set($flags);
	}

	static function setDieOnComplete($flag) {
		self::$dieOnComplete = $flag ? true : false;
	}

	static function getErrors() {
		return self::$errorMessages;
	}

	static function getMessages() {
		return self::$successMessages;
	}

	static function pushError($message) {
		if (!is_array(self::$errorMessages)) {
			self::$errorMessages = array($message);
			return false;
		}
		array_push(self::$errorMessages, $message);
		return false;
	}

	static function popError() {
		if (is_array(self::$errorMessages) && count(self::$errorMessages) > 0)
			return array_pop(self::$errorMessages);
		return null;
	}

	static function pushStatus($message) {
		if (!is_array(self::$successMessages)) {
			self::$successMessages = array($message);
			return true;
		}
		array_push(self::$successMessages, $message);
		return true;
	}

	static function jsonError($message=null) {
		if ($message == null) {
			$message = self::popError() ?? "Operation not successful";
		}
		if (!self::$json) {
			self::pushError($message);
		} else {
			header_remove();
			header("Content-Type: application/javascript", true);
			http_response_code(200);
			echo "{\"status\":\"Error\", \"message\":\"$message\"}";
		}
		if (self::$dieOnComplete) die();

		return false;
	}

	static function jsonFatalError($message="Fatal error") {
		@header_remove();
		@header("Content-Type: application/javascript", true);
		@http_response_code(200);
		die ("{\"status\":\"Error\", \"message\":\"$message\"}");
	}

	//Returns a message only
	static function jsonSuccess($message="Operation completed successfully", $extraData=null) {
		if (!self::$json) {
			self::pushStatus($message);
		} else {
			header_remove();
            header("Content-Type: application/javascript", true);
            http_response_code(200);

			$res = ["status"=>"OK", "message"=>"$message"];

			if ($extraData != null)
				$res["results"] = $extraData;

			echo json_encode($res);
		}
		if (self::$dieOnComplete) die();
		return true;
	}

	static function jsonSuccessDB($element, $dbResult, $totalCount=0) {
		if (!self::$json) {
			self::pushStatus("Success");
			return $dbResult;
		} else {
			echo '{"status": "OK", ' . (($totalCount > 0) ? '"total":' . $totalCount . ',' : '') . ' "' . $element . '": [';
			$cm = "";
			while ($item = AppDB::fetchAssoc($dbResult)) {
				echo $cm . json_encode($item);
				$cm = ",";
			}
			echo ']}';
		}
		if (self::$dieOnComplete) die();
		return true;
	}

	static function jsonSuccessItem($element, $item, $message="") {
		if (!self::$json) {
			self::pushStatus("Success");

			if (!is_string($item) && !is_array($item))
				return AppDB::fetchAssoc($item);

			return $item;
		} else {
			$pre = '{"status": "OK", '. ($message != "" ? '"message" : "' . $message . '", ' : '') .'"' . $element . '":';
			$post=  '}';
			if (is_string($item))
				echo "$pre\"$item\"$post";
			else if (is_array($item))
				echo $pre . json_encode($item) . $post;
			else if ($item = AppDB::fetchAssoc($item)) {
				echo $pre . json_encode($item) . $post;
			} else {
				echo '{"status": "Error", "message":"Item not found! '.print_r($item, true).'"}';
			}
		}
		if (self::$dieOnComplete) die();
		return true;
	}

	static function jsonSuccessItems($element, $items, $clearPassword = false) {
		if (!self::$json) {
			self::pushStatus("Success");

			if (is_string($items) || is_array($items))
				return $items;

			$return = array();
			while ($i = AppDB::fetchAssoc($items)) {
				array_push($return, $i);
			}
			return $return;
		} else {
			$pre = '{"status": "OK", "' . $element . '":';
			$post=  '}';
			if (is_string($items))
				echo "$pre\"$items\"$post";
			else if (is_array($items))
				echo $pre . json_encode($items) . $post;
			else if ($item = AppDB::fetchAssoc($items)) {
				if ($clearPassword) {	//Clear password field
					unset($item['password']);
					echo $pre . "[" . json_encode($item);
					while ($item = AppDB::fetchAssoc($items)) {
						unset($item['password']);
						echo "," . json_encode($item);
					}
					echo "]" . $post;

				} else {
					echo $pre . "[" . json_encode($item);
					while ($item = AppDB::fetchAssoc($items)) {
						echo "," . json_encode($item);
					}
					echo "]" . $post;
				}
			} else {
				echo '{"status": "Error", "message":"Item not found"}';
			}
		}
		if (self::$dieOnComplete) die();
		return true;
	}
}

?>
