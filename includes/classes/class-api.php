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

		public function doAPI( $api_url, $path, $url_params = array() ) {
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

		public function search( $term, $businessLocation ) {
			$url_params = array();
			$url_params['term'] = $term;
			$url_params['location'] = $businessLocation;
			$url_params['limit'] = SEARCH_LIMIT;
			$result = $this->doAPI(API_HOST, SEARCH_PATH, $url_params);
			return $result;
		}

		public function get_business( $business_id ) {
			$business_path = BUSINESS_PATH . urlencode($business_id);
			return $this->doAPI(API_HOST, $business_path);
		}
	
		public function query_api( $term, $businessLocation ) {     
			$response = json_decode(search($term, $businessLocation));
			$pretty_response = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			return $this->$pretty_response;
		}

		public function buildPollItems( $results ) {
			$pollitems = array();
			$count = 1;
			foreach( $results as $result ) {
				foreach( $result as $item ) {
					$name = $item['name'];
					$image = $item['image_url'];
					$is_closed = $item['is_closed'];
					$status = "Closed";
					if(!$is_closed) {
						$status = "Open";
					}
					$url = $item['url'];
					$reviews = $item['review_count'];
					$tags = array();
					foreach( $item['categories'] as $tag ) {
						$tags[] = $tag['title'];
					}
					if( isset($item['price'] ) ) {
						$price = $item['price'];
					} else {
						$price = "$";
					}
					$businessLocation = $item['location']['display_address'];
					$phone_link = $item['phone'];
					$phone = $item['display_phone'];
					$rating = $item['rating'];
					$pollitems[] = array(
						"name" => $name,
						"image" => $image,
						"status" => $status,
						"url" => $url,
						"reviews" => $reviews,
						"tags" => $tags,
						"price" => $price,
						"location" => $businessLocation,
						"phone_link" => $phone_link,
						"phone" => $phone,
						"rating" => $rating
					);
					if( $count >= 3 ) {
						return $pollitems;
					} else {
						$count++;
					}
				}
			}
			return $pollitems; 
		}

		public function cardContent( $pollitems, $index ) {
			$rating_images = array(
				0 => 'regular_0.png',
				1 => 'regular_1.png',
				1.5 => 'regular_1_half.png',
				2 => 'regular_2.png',
				2.5 => 'regular_2_half.png',
				3 => 'regular_3.png',
				3.5 => 'regular_3_half.png',
				4 => 'regular_4.png',
				4.5 => 'regular_4_half.png',
				5 => 'regular_5.png',
			);
			$details = $pollitems[$index];
			$html = "<a href='" . $details['url'] . "' rel='no-follow no-opener' target='_blank'><img src='" . $details['image'] . "' alt='Yelp Image for '" . $details['name'] . "' class='yelp-image' /></a>";
			$html .= "<h4>" . $details['name'] ."</h4>";
			$html .= "<div class='yelp-ratings'><img src='".plugin_dir_url(__FILE__)."../images/yelp_stars/" . $rating_images[$details['rating']] . "' alt='Rated " . $details['rating'] . "(s) on Yelp' class='yelp-stars' /><span>Based on " . $details['reviews'] . " rating(s)</span></div>";
			$html .= "<div class='yelp-biz-info'><p class='yelp-location'><a href='" . $details['url'] . "' rel='no-follow no-opener' target='_blank'>" . $details['location'][0] . "<br/>" . $details['location'][1] . "</a></p><p class='yelp-phone'><a href='tel:" . $details['phone_link'] . "'>" . $details['phone'] . "</a></p></div>";
			$html .= "<img src='".plugin_dir_url(__FILE__)."../images/yelp-logo.png' alt='Yelp Logo' class='yelp-logo' />";
			return $html;
		}
	}