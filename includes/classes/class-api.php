<?php
	
	/**
	 * Configuration Details and Defining Our Global Constants
	 * 
	 * @since 0.1.0
	 * 
	 * @param string $businessMatchupYelpAPI contains the Yelp API Key.
	 * @param string $strawAPI contains the StrawPoll API Key.
	 * @param string API_KEY contains the Yelp API Key.
	 * @param string STRAWPOLL_API_KEY contains the StrawPoll API Key.
	 * @param string API_HOST contains the URL base for the Yelp API.
	 * @param string SEARCH_PATH specifies the Yelp API Endpoint for searches.
	 * @param string BUSINESS_PATH specifies the Yelp API Endoint for business data.
	 * @param string DEFAULT_TERM specifies the term to use when one isn't provided.
	 * @param string DEFAULT_LOCATION specifies the location to use when one isn't provided.
	 * @param string SEARCH_LIMIT limits the number of business to search for to only the first 3.
	 */
	$businessMatchupYelpAPI = get_option( 'business_matchup_yelp_api' );
	$strawAPI = get_option( 'business_matchup_straw_poll_api' );
	define( 'API_KEY', $businessMatchupYelpAPI );
	define( 'STRAWPOLL_API_KEY', $strawAPI );
	define( 'API_HOST', 'https://api.yelp.com' );
	define( 'SEARCH_PATH','/v3/businesses/search' );
	define( 'BUSINESS_PATH', '/v3/businesses/' );
	define( 'DEFAULT_TERM', 'dinner' );
	define( 'DEFAULT_LOCATION','San Francisco, CA' );
	define( 'SEARCH_LIMIT', 3 );

	/**
	 * Business_Matchup_API class
	 * This class defines the actions taken to access the API's
	 * 
	 * @since 0.1.0
	 */
	class Business_Matchup_API {

		/**
		 * doAPI function
		 * This function defines the request to perform the Yelp API calls.
		 * 
		 * @since 0.1.0
		 * 
		 * @param string $api_url contains the base url of the api to call.
		 * @param string $path contains the endpoint fo the api to call.
		 * @param array $url_params contains the array of headers needed to complete the call.
		 * @return array $response contains the full array of output created by wp_remote_get.
		 */
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

		/**
		 * search function
		 * This function creates the paramaters needed to submit a search to the Yelp API.
		 * 
		 * @since 0.1.0
		 * 
		 * @param string $term contains the type of business we want to look for.
		 * @param string $businessLocation contains the City and State-Abbreviation where we want to look for those businesses.
		 * @return array $result containing the reponse array from the api call.
		 */
		function search( $term, $businessLocation ) {
			$url_params = array();
			$url_params['term'] = $term;
			$url_params['location'] = $businessLocation;
			$url_params['limit'] = SEARCH_LIMIT;
			$result = $this->doAPI(API_HOST, SEARCH_PATH, $url_params);
			return $result;
		}

		/**
		 * get_business function
		 * This function fetches the business details for a given business from the Yelp API.
		 * 
		 * @since 0.1.0
		 * 
		 * @param integer $business_id is the ID number of the business in Yelp.
		 * @return array response output from the Business_Matchup_API for the given call.
		 */
		function get_business( $business_id ) {
			$business_path = BUSINESS_PATH . urlencode($business_id);
			return $this->doAPI(API_HOST, $business_path);
		}
		
		/**
		 * query_api function
		 * This function compiles a formatted JSON object from output of our Yelp Searches.
		 * 
		 * @since 0.1.0
		 * 
		 * @param string $term contain the type of business.
		 * @param string $businessLocation contains the location of the business.
		 * @return string formatted JSON object.
		 */
		function query_api( $term, $businessLocation ) {     
			$response = json_decode(search($term, $businessLocation));
			$pretty_response = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			return $this->$pretty_response;
		}

		/**
		 * createPoll function
		 * This function creates a new Poll on StrawPoll using the StrawPoll API using the information provided in $poll_json.
		 * 
		 * @since 0.1.0
		 * 
		 * @param string $poll_json JSON object containing the details of the businesses we are creating a poll for.
		 * @return string $html contains the formatted iFrame that will be used in the content of our custom post type.
		 */
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
			$html = '<iframe src="https://strawpoll.com/embed/' . $pollID . '" class="strawpolls-content" frameborder="0" allowfullscreen></iframe>';
			return $html;
		}

		/**
		 * addPoll function
		 * This function creates the headings and formatting where our new poll will be displayed.
		 * 
		 * @since 0.1.0
		 * 
		 * @param integer $postID is the id number of the post.
		 * @param string $term is the type of business we are showing the poll about.
		 * @param string $bizLoc is the location of the businesses we are polling on.
		 * @return string $pollHTML is the formatted HTML containing our new poll.
		 */
		public function addPoll( $postID, $term, $bizLoc ) {
			$businessMatchupYelpAPI = new Business_Matchup_API();
			$businessMatchupPage = new Business_Matchup_Polls_Page();
			$response = $businessMatchupYelpAPI->search( $term, $bizLoc );
			if( null === $response) {
				return;
			}
			$response_body = wp_remote_retrieve_body( $response );
			update_post_meta( $postID, '_business_matchup_yelp_results', $response_body );
			$response_body = json_decode( get_post_meta( $postID, '_business_matchup_yelp_results', true ), true );
			$pollitems = $businessMatchupPage->buildPollItems($response_body);
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
			$pollHTML = $businessMatchupYelpAPI->createPoll( $poll_json );
			return $pollHTML;
		}

	}