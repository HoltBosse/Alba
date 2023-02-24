<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Videos {

    public $token;
    public $team_id; /* used for libraries */

	public function __construct(/* $token */) {
		/* $this->token = $token; */
        $this->token = Config::vimeo_key();
        $this->team_id = Config::vimeo_team_id();
	}

    public function get_all_videos() {
        $curl = curl_init();
        if($this->team_id) {
            curl_setopt($curl, CURLOPT_URL, "https://api.vimeo.com/users/$this->team_id/videos");
        } else {
            curl_setopt($curl, CURLOPT_URL, "https://api.vimeo.com/me/videos");
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: bearer ' . $this->token]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return json_decode($output);
    }

    public function search_all_videos($query="") {
        $curl = curl_init();
        if($this->team_id) {
            /*
                based of vimeo search
            */
            curl_setopt($curl, CURLOPT_URL, "https://api.vimeo.com/users/$this->team_id/items?query=" . urlencode($query) . "&filter=video&per_page=25");
        } else {
            curl_setopt($curl, CURLOPT_URL, "https://api.vimeo.com/me/videos?query=" . $query);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: bearer ' . $this->token]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        if($this->team_id) {
            $return_data = json_decode($output);
            $output = (object) [
                "total"=>$return_data->total,
                "page"=>$return_data->page,
                "per_page"=>$return_data->per_page,
                "paging"=>$return_data->paging,
                "data"=>[]
            ];

            foreach($return_data->data as $item) {
                $output->data[] = $item->video;
            }

            return $output;
            
        } else {
            return json_decode($output);
        }
    }

    public function get_page($page) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.vimeo.com$page");
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: bearer ' . $this->token]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return json_decode($output);
    }
}