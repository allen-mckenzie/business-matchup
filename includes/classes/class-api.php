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
			$html = '<iframe src="https://strawpoll.com/embed/' . $pollID . '" style="width: 75%;height: 100%;margin: 0 16.5% !important;padding-top: 20px;min-height: 640px;" frameborder="0" allowfullscreen></iframe>';
			return $html;
		}

		public function addPoll( $postID, $term, $bizLoc ) {
			//$yelpAPI = new Yelp_API();
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

		// function buildPollItems( $results ) {
		// 	$pollitems = array();
		// 	$count = 1;
		// 	foreach( $results as $result ) {
		// 		foreach( $result as $item ) {
		// 			$name = $item['name'];
		// 			$image = $item['image_url'];
		// 			$is_closed = $item['is_closed'];
		// 			$status = "Closed";
		// 			if(!$is_closed) {
		// 				$status = "Open";
		// 			}
		// 			$url = $item['url'];
		// 			$reviews = $item['review_count'];
		// 			$tags = array();
		// 			foreach( $item['categories'] as $tag ) {
		// 				$tags[] = $tag['title'];
		// 			}
		// 			$price = "$";
		// 			if( isset($item['price'] ) ) {
		// 				$price = $item['price'];
		// 			}
		// 			$businessLocation = $item['location']['display_address'];
		// 			$phone_link = $item['phone'];
		// 			$phone = $item['display_phone'];
		// 			$rating = $item['rating'];
		// 			$pollitems[] = array(
		// 				"name" => $name,
		// 				"image" => $image,
		// 				"status" => $status,
		// 				"url" => $url,
		// 				"reviews" => $reviews,
		// 				"tags" => $tags,
		// 				"price" => $price,
		// 				"location" => $businessLocation,
		// 				"phone_link" => $phone_link,
		// 				"phone" => $phone,
		// 				"rating" => $rating
		// 			);
		// 			if( $count >= 3 ) {
		// 				return $pollitems;
		// 			}
		// 			$count++;
		// 		}
		// 	}
		// 	return $pollitems; 
		// }

		// function cardContent( $pollitems, $index ) {
		// 	$rating_images = array(
		// 		0 => 'regular_0.png',
		// 		1 => 'regular_1.png',
		// 		1.5 => 'regular_1_half.png',
		// 		2 => 'regular_2.png',
		// 		2.5 => 'regular_2_half.png',
		// 		3 => 'regular_3.png',
		// 		3.5 => 'regular_3_half.png',
		// 		4 => 'regular_4.png',
		// 		4.5 => 'regular_4_half.png',
		// 		5 => 'regular_5.png',
		// 	);
		// 	$details = $pollitems[$index];
		// 	$html = "<a href='" . $details['url'] . "' rel='no-follow no-opener' target='_blank'><img src='" . $details['image'] . "' alt='Yelp Image for '" . $details['name'] . "' class='yelp-image' /></a>";
		// 	$html .= "<a href='" . $details['url'] . "' rel='no-follow no-opener' target='_blank'><h4>" . $details['name'] ."</h4></a>";
		// 	$html .= "<div class='yelp-ratings'><img src='".plugin_dir_url(__FILE__)."../images/yelp_stars/" . $rating_images[$details['rating']] . "' alt='Rated " . $details['rating'] . "(s) on Yelp' class='yelp-stars' /><span>Based on " . $details['reviews'] . " rating(s)</span></div>";
		// 	$html .= "<div class='yelp-biz-info'><p class='yelp-location'><a href='https://www.google.com/maps/place/" . $details['location'][0] . " " . $details['location'][1] . "' rel='no-follow no-opener' target='_blank'>" . $details['location'][0] . "<br/>" . $details['location'][1] . "</a></p><p class='yelp-phone'><a href='tel:" . $details['phone_link'] . "'>" . $details['phone'] . "</a></p></div>";
		// 	$html .= "<img src='".plugin_dir_url(__FILE__)."../images/yelp-logo.png' alt='Yelp Logo' class='yelp-logo' />";
		// 	return $html;
		// }
	}