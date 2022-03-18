<?php
	namespace BusinessMatchup;

	/**
	 * Business_Matchup_Page class
	 * This class contains the functions needed to create and display the content for our custom post type.
	 * 
	 * @since 0.1.0
	 */
    class Business_Matchup_Polls_Page {

		/**
		 * business_matchup_content function
		 * This function displays the gathered information from the Yelp API and Straw Poll API based on the 
		 * information provided in the custom metaboxes for the content of the Custom Post Type Front End.
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $content is the array containing the page content for the given post.
		 * @return array $content for the custom post type after generating it from the data we retrieved.
		 */
        public function business_matchup_content($content) {
			$business_matchup_page = new Business_Matchup_Polls_Page();
			global $post;
			$postID = $post->ID;
			$type = get_post_meta( $postID, '_business_matchup_type', true );
			$bizLoc = get_post_meta( $postID, '_business_matchup_business_location', true );
			$poll = get_post_meta( $postID, '_business_matchup_poll', true );
			$bizLoc_array = explode(",",$bizLoc);
			$city = $bizLoc_array[0];
			$response_body = json_decode( get_post_meta( $postID, '_business_matchup_yelp_results', true ), true );
			$pollitems = $business_matchup_page->buildPollItems($response_body);
			$content = '<section id="business-matchups">';
			if ($post->post_type == 'business-matchups') {
				$content .= '
					<div id="business-matchups-title">
						<h1> '.$type.' locations near '.$city.'</h1>
						<hr/>
					</div>
					<section id="business-matchups-content">
						<div class="cards">
							<div class="card card-1">'.$business_matchup_page->cardContent( $pollitems, 0 ).'</div>
							<div class="card card-2">'.$business_matchup_page->cardContent( $pollitems, 1 ).'</div>
							<div class="card card-3">'.$business_matchup_page->cardContent( $pollitems, 2 ).'</div>
						</div>
					</section>
				';
				$content .= '<div id="strawpolls-content">';
				$content .= $poll;
				$content .= '</div>';
				$content .= '</section>';
			}
			return $content;
		}

		/**
		 * buildPollItems function
		 * This function creates an array of poll items to pass on to the create cards function.
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $results contains the array of information retrieved from the Yelp API
		 * @return array $pollitems with a new array with the information we need when creating the cards.
		 */
		function buildPollItems( $results ) {
			$pollitems = array();
			$count = 1;
			if( !is_array( $results ) || !isset( $results['businesses'][0]['name'] ) ) {
				return;
			}
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
					$price = "$";
					if( isset($item['price'] ) ) {
						$price = $item['price'];
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
					}
					$count++;
				}
			}
			return $pollitems; 
		}

		/**
		 * cardContent function
		 * This function creates the display markup for each of the 3 cards we will be displaying.
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $pollitems contains an array of items built by buildPollItems.
		 * @param integer $index contains the current index of the card being created.
		 * @return string $html contains the generated markup to be displayed for the current card item in the index.
		 */
		function cardContent( $pollitems, $index ) {
			if( !is_array( $pollitems ) || !isset( $pollitems[0]['url'] ) ) {
				return;
			}
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
			if( empty( $details['image'] ) ) {
				$details['image']= plugin_dir_url(__FILE__).'../images/no-image.png';
			}
			$html = "<a href='" . $details['url'] . "' rel='no-follow no-opener' target='_blank'><img src='" . $details['image'] . "' alt='Yelp Image for '" . $details['name'] . "' class='yelp-image' /></a>";
			$html .= "<a href='" . $details['url'] . "' rel='no-follow no-opener' target='_blank'><h4>" . $details['name'] ."</h4></a>";
			$html .= "<div class='yelp-ratings'><img src='".plugin_dir_url(__FILE__)."../images/yelp_stars/" . $rating_images[$details['rating']] . "' alt='Rated " . $details['rating'] . "(s) on Yelp' class='yelp-stars' /><span>Based on " . $details['reviews'] . " rating(s)</span></div>";
			$html .= "<div class='yelp-biz-info'><p class='yelp-location'><a href='https://www.google.com/maps/place/" . $details['location'][0] . " " . $details['location'][1] . "' rel='no-follow no-opener' target='_blank'>" . $details['location'][0] . "<br/>" . $details['location'][1] . "</a></p><p class='yelp-phone'><a href='tel:" . $details['phone_link'] . "'>" . $details['phone'] . "</a></p></div>";
			$html .= "<img src='".plugin_dir_url(__FILE__)."../images/yelp-logo.png' alt='Yelp Logo' class='yelp-logo' />";
			return $html;
		}
        
    }