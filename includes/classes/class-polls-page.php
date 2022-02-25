<?php

    class Yelp_Polls_Page {

        public function yelp_polls_content($content) {
			// $yelpAPI = new Yelp_API();
			global $post;
			$postID = $post->ID;
			$type = get_post_meta( $postID, '_yelp_polls_type', true );
			$bizLoc = get_post_meta( $postID, '_yelp_polls_business_location', true );
			$poll = get_post_meta( $postID, '_yelp_polls_poll', true );
			$bizLoc_array = explode(",",$bizLoc);
			$city = $bizLoc_array[0];
			$response_body = json_decode( get_post_meta( $postID, '_yelp_polls_yelp_results', true ), true );
			$pollitems = Yelp_Polls_Page::buildPollItems($response_body);
			$content = '<section id="yelp-polls">';
			if ($post->post_type == 'yelp-polls') {
				$content .= '
					<h1> '.$type.' locations near '.$city.'</h1>
					<hr/>
					<section class="yelp-polls-content">
						<div class="cards">
							<div class="card">'.Yelp_Polls_Page::cardContent( $pollitems, 0 ).'</div>
							<div class="card">'.Yelp_Polls_Page::cardContent( $pollitems, 1 ).'</div>
							<div class="card">'.Yelp_Polls_Page::cardContent( $pollitems, 2 ).'</div>
						</div>
					</section>
				';
				$content .= $poll;
				$content .= '</section>';
			}
			return $content;
		}

		function buildPollItems( $results ) {
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

		function cardContent( $pollitems, $index ) {
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
			$html .= "<a href='" . $details['url'] . "' rel='no-follow no-opener' target='_blank'><h4>" . $details['name'] ."</h4></a>";
			$html .= "<div class='yelp-ratings'><img src='".plugin_dir_url(__FILE__)."../images/yelp_stars/" . $rating_images[$details['rating']] . "' alt='Rated " . $details['rating'] . "(s) on Yelp' class='yelp-stars' /><span>Based on " . $details['reviews'] . " rating(s)</span></div>";
			$html .= "<div class='yelp-biz-info'><p class='yelp-location'><a href='https://www.google.com/maps/place/" . $details['location'][0] . " " . $details['location'][1] . "' rel='no-follow no-opener' target='_blank'>" . $details['location'][0] . "<br/>" . $details['location'][1] . "</a></p><p class='yelp-phone'><a href='tel:" . $details['phone_link'] . "'>" . $details['phone'] . "</a></p></div>";
			$html .= "<img src='".plugin_dir_url(__FILE__)."../images/yelp-logo.png' alt='Yelp Logo' class='yelp-logo' />";
			return $html;
		}
        
    }