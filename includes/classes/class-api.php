<?php
	
	/** Configuration Details and Global Defaults */
	$yelpAPI = get_option( 'yelp_polls_yelp_api' );
	$strawAPI = get_option( 'yelp_polls_straw_poll_api' );
	define( 'API_KEY', $yelpAPI );
	define( 'STRAWPOLL_API_KEY', $strawAPI );
	define( 'API_HOST', 'https://api.yelp.com' );
	define( 'SEARCH_PATH','/v3/businesses/search' );
	define( 'BUSINESS_PATH', '/v3/businesses/' );
	define( 'DEFAULT_TERM', 'dinner' );
	define( 'DEFAULT_LOCATION','San Francisco, CA' );
	define( 'SEARCH_LIMIT', 3 );

	class Yelp_API {

		function doAPI( $api_url, $path, $url_params = array() ) {
			$query = http_build_query( $url_params );
			$api_url = $api_url."".$path."?".$query;
			$api_key = API_KEY;
			if( $api_key === '' ) {
				return;
			}
			$args = array( 
				'timeout' => 30,
				'headers' => array(
					"Host" => "api.yelp.com",
					"user-agent" => "",
					"Authorization" => "Bearer $api_key",
					"cache-control" => "no-cache"), 
				);
			$response = wp_remote_get( $api_url , $args );
			return $response;
		}

		function search( $term, $businessLocation ) {
			$url_params = array();
			$url_params['term'] = $term;
			$url_params['location'] = $businessLocation;
			$url_params['limit'] = SEARCH_LIMIT;
			$result = Yelp_API::doAPI(API_HOST, SEARCH_PATH, $url_params);
			return $result;
		}

		function get_business( $business_id ) {
			$business_path = BUSINESS_PATH . urlencode($business_id);
			return Yelp_API::doAPI(API_HOST, $business_path);
		}
	
		function query_api( $term, $businessLocation ) {     
			$response = json_decode(search($term, $businessLocation));
			$pretty_response = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			return Yelp_API::$pretty_response;
		}

		public function createPoll( $poll_json ) {
			$url = 'https://strawpoll.com/api/poll';
			$post_data = $poll_json;
			$headers = array(
				'user-agent' => '',
				'Content-Type' => 'application/json',
				'X-API-KEY' => STRAWPOLL_API_KEY
			);
			$args = array(
				'method' => 'POST',
				'headers' => $headers,
				'blocking'    => true,
				'body' => $post_data,
				'data_format' => 'body'
			);
			$response = wp_remote_request( $url, $args );
			$response_body = wp_remote_retrieve_body( $response );
			$response_body = json_decode( $response_body, true);
			$pollID = null;
			if( isset( $response_body['content_id'] ) ) {
				$pollID = $response_body['content_id'];
			}
			$html = '<iframe src="https://strawpoll.com/embed/' . $pollID . 'class="strawpolls-content" frameborder="0" allowfullscreen></iframe>';
			return $html;
		}

		public function addPoll( $postID, $term, $bizLoc ) {
			$response = Yelp_API::search( $term, $bizLoc );
			if( null === $response) {
				return;
			}
			$response_body = wp_remote_retrieve_body( $response );
			update_post_meta( $postID, '_yelp_polls_yelp_results', $response_body );
			$response_body = json_decode( get_post_meta( $postID, '_yelp_polls_yelp_results', true ), true );
			$pollitems = Yelp_Polls_Page::buildPollItems($response_body);
			$bizLoc_array = explode(",",$bizLoc);
			$city = $bizLoc_array[0];
			$answers = array();
			foreach($pollitems as $answer) {
				$answers[] = $answer['name'];
			}
			$poll_array = array( 
				"poll" => array(
					"title" => "Which of these $term locations near $city do you think is the best?",
					"answers" => $answers,
					"priv" => false,
					"ma" => false,
					"mip" => true,
					"enter_name" => true,
					"only_reg" => false
				)
			);
			$poll_json = json_encode( $poll_array );
			$pollHTML = Yelp_API::createPoll( $poll_json );
			return $pollHTML;
		}

	}