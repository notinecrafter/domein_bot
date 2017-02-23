<?php
class Telegram {

	private $data = array();
	private $updates = array();

	public function getToken() {
		$bot_id = file_get_contents('token.txt');
	}

	public function __construct($bot_id) {
		$this->bot_id = $bot_id;
		$this->data = $this->getData();
	}

	public function endpoint($api, array $content, $post = true) {
		$url = 'https://api.telegram.org/bot' . $this->bot_id . '/' . $api;
		if ($post)
			$reply = $this->sendAPIRequest($url, $content);
		else
			$reply = $this->sendAPIRequest($url, array(), false);
		return json_decode($reply, true);
	}

	public function getMe() {
		return $this->endpoint("getMe", array(), false);
	}

	public function sendMessage(array $content) {
		return $this->endpoint("sendMessage", $content);
	}

	public function setWebhook($url, $certificate = "") {
		if ($certificate == "") {
			$content = array('url' => $url);
		} else {
			$content = array('url' => $url, 'certificate' => $certificate);
		}
		return $this->endpoint("setWebhook", $content);
	}

	public function getData() {
		if (empty($this->data)) {
			$rawData = file_get_contents("php://input");
			return json_decode($rawData, true);
		} else {
			return $this->data;
			print_r($this->data);
		}
	}

	public function setData(array $data) {
		$this->data = $data;
	}
	public function Text() {
		return $this->data["message"]["text"];
	}
	public function FromChat() {
		return $this->data["message"]["chat"]["username"];
	}
	public function MessageID() {
		return $this->data["message"]["message_id"];
	}
	public function ForwardID() {
		return $this->data["message"]["forward_from"]["id"];
	}
	public function ChatID() {
		return $this->data["message"]["chat"]["id"];
	}

	public function Date() {
		return $this->data["message"]["date"];
	}

	public function Username() {
		return $this->data["message"]["from"]["username"];
	}

	public function UserID() {
		return $this->data["message"]["from"]["id"];
	}

	public function FirstName() {
		return $this->data["message"]["from"]["first_name"];
	}

	public function UpdateID() {
		return $this->data["update_id"];
	}

	public function UpdateCount() {
		return count($this->updates["result"]);
	}

	public function messageFromGroup() {
		if ($this->data["message"]["chat"]["title"] == "") {
			return false;
		}
		return true;
	}
	public function buildKeyBoard(array $options, $onetime = false, $resize = false, $selective = false) {
        	$replyMarkup = array(
        	    'keyboard' => $options,
        	    'one_time_keyboard' => $onetime,
        	    'resize_keyboard' => $resize,
        	    'selective' => $selective
        	);
        	$encodedMarkup = json_encode($replyMarkup, true);
        	return $encodedMarkup;
	}
	public function getUpdates($offset = 0, $limit = 100, $timeout = 0, $update = true) {
		$content = array('offset' => $offset, 'limit' => $limit, 'timeout' => $timeout);
		$this->updates = $this->endpoint("getUpdates", $content);
		if ($update) {
			if(count($this->updates["result"]) >= 1) { //for CLI working.
				$last_element_id = $this->updates["result"][count($this->updates["result"]) - 1]["update_id"] + 1;
				$content = array('offset' => $last_element_id, 'limit' => "1", 'timeout' => $timeout);
				$this->endpoint("getUpdates", $content);
			}
		}
		return $this->updates;
		print_r($this->updates);
	}

	public function serveUpdate($update) {
		$this->data = $this->updates["result"][$update];
	}

	private function sendAPIRequest($url, array $content, $post = true) {
		if (isset($content['chat_id'])) {
			$url = $url . "?chat_id=" . $content['chat_id'];
			unset($content['chat_id']);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($post) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

}
?>
