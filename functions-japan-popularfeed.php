<?php
class jp_tc_mobile_feeds {
	public function __construct() {
		add_feed( 'jp_mobile_popular_rss', array( $this, 'popular_rss' ) );
	}
	function popular_rss() {
		header( 'Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true );
		preg_match('{(\d{1,2})/(\d{1,2})/popular.xml}', $_SERVER['REQUEST_URI'], $match);

		$max_posts = (int) $match[1];
		if ( $max_posts < 1 || $max_posts > 25 )
			wp_die('Max popular posts should be between 1 and 25.');

		$days = (int) $match[2];
		if ( $days < 1 || $days > 90 )
			wp_die('The no. of days should be between 1 and 90.');
		if ( function_exists('wpcom_vip_load_helper_stats') ) {
			wpcom_vip_load_helper_stats();
		}
		$feed_max_posts = $max_posts+20;
		$popular_data = wpcom_vip_top_posts_array( $days, $feed_max_posts ); 
		//print_r ($popular_data);
		foreach( $popular_data as $p ) {
			if ($p["post_id"] != 0)
				$popular_posts[] = $p["post_id"];
		}
		$the_query = new WP_Query( array( 'post__in' => $popular_posts, 'orderby' => 'post__in', 'posts_per_page' =>$max_posts, 'ignore_sticky_posts' => 1 ) );
		echo '<?xml version="1.0"?>';
?>
		<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
		<channel>
		<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
		<link><?php bloginfo_rss('url'); ?></link>
		<description><?php bloginfo_rss("description") ?></description>
		<image>
			<url><?php echo home_url(); ?>/wp-content/themes/vip/jptechcrunch/images/site-logo-small.png</url>
			<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
			<link><?php bloginfo_rss('url') ?></link>
		</image>
		<language><?php echo get_option('rss_language'); ?></language>
		<copyright>Copyright <?php echo date('Y'); ?> TechCrunch The contents of this feed are available for non-commercial use only.</copyright>
		<?php global $post; while($the_query->have_posts()) : $the_query->the_post(); ?>
		<item>
			<title><![CDATA[<?php the_title_rss(); ?>]]></title>
			<link><![CDATA[<?php the_permalink_rss(); ?>]]></link>
			<guid isPermaLink="true"><![CDATA[<?php the_permalink_rss(); ?>]]></guid>
			<description><![CDATA[<?php 
				if ( is_single() ) {
					if ( get_post_meta( $post->ID, '_tc_post_type', true ) != 'simplepost' && has_post_thumbnail() )
                    	the_post_thumbnail( 'full' );
					the_content(); 
				} else { echo strip_tags( get_the_excerpt() ); } ?> ]]></description>
			<?php if ( $thumb = tc_get_post_image( $post, 'full' ) ): ?>
			<enclosure url="<?php echo esc_url( $thumb ); ?>" length="<?php echo strlen($thumb);?>" type="<?php echo tc_get_image_type($thumb);?>"></enclosure>
			<?php else: ?>
			<enclosure url="" length="-1" ></enclosure>
			<?php endif; ?>
			<?php $byline = get_post_meta( get_the_ID(), 'byline', true ); ?>
			<?php $co_authors = get_coauthors(); 
				$author = get_the_author();
				if ( count($co_authors) > 0 ) {
					$co_author = $co_authors[0];
					if (  $co_author->last_name ) {
						$author = $co_author->first_name . ' ' . $co_author->last_name;
					} else { 
						$author = $co_author->display_name; 
					}
				}
			?>
			<dc:creator><![CDATA[<?php echo ( $byline ? esc_html( $byline ) : esc_html( $author ) ); ?>]]></dc:creator>
			<pubDate><?php $gmt_timestamp = get_post_time('U', true); echo date ( 'D, d M Y H:i:s O' , intval($gmt_timestamp ));?></pubDate>
			<dc:identifier>0|19962129</dc:identifier>
			<?php foreach (wp_get_post_categories($post->ID) as $categoryId): $category = get_category($categoryId); ?>
			<category domain="category:<?php echo esc_html( $category->slug ); ?>"><![CDATA[<?php echo esc_html( $category->name ); ?>]]></category>
			<?php endforeach; ?>
			<?php foreach (wp_get_post_tags($post->ID) as $tag) : ?>
			<category domain="tag:<?php echo esc_html( $tag->slug ); ?>"><![CDATA[<?php echo esc_html( $tag->name ); ?>]]></category>
			<?php endforeach; ?>
			<category domain="blogger:<?php echo esc_html( get_the_author() );?>"><![CDATA[<?php echo esc_html( get_the_author() ); ?>]]></category>
		</item>
		<?php endwhile; ?>
		</channel>
	</rss>
<?php	
	}	
}
$tc_mobile_feeds = new jp_tc_mobile_feeds;
?>