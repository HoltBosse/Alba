<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Videos {

    public $token;

	public function __construct(/* $token */) {
		/* $this->token = $token; */
        $this->token = Config::vimeo_key();
	}

    public function get_all_videos() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.vimeo.com/me/videos");
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: bearer ' . $this->token]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return json_decode($output);
    }

    public function search_all_videos($query="") {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.vimeo.com/me/videos?query=" . $query);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: bearer ' . $this->token]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return json_decode($output);
    }
}