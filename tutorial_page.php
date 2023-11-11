<?php

function tutorials_shortcode() {
    ob_start();

    // Place your tutorials page template code here
    ?>
	<div class="tutorialsWrapper">

	<form method="get" class="tutorialFilerBar">

		<label for="tutorial-type" class="tutorialTypeLabel">Filter by tutorial type:</label> 
		<div class="tutorialDropdown">
		
		<select name="tutorial-type" id="tutorial-type">
			<option value="">All</option>
			<?php
			$terms = get_terms(array(
				'taxonomy' => 'tutorial_type',
				'hide_empty' => false,
			));

			$selected_term = isset($_GET['tutorial-type']) ? $_GET['tutorial-type'] : '';

			foreach ($terms as $term) {
				$selected = $selected_term === $term->slug ? 'selected' : '';
				printf('<option value="%s" %s>%s</option>', $term->slug, $selected, $term->name);
			}
			?>
		</select>
				<button type="submit">Filter</button>
		</div>
		<div class="tutorialSearch">
				<label for="tutorial-search"></label>
		<input type="text" name="tutorial-search" id="tutorial-search" placeholder="Search tutorials" value="<?php echo isset($_GET['tutorial-search']) ? $_GET['tutorial-search'] : ''; ?>">
				<button type="submit">Search</button>
		</div>


	</form>
    <?php

    // Retrieve and display tutorials
    $args = array(
        'post_type' => 'tutorials',
        'posts_per_page' => -1,
        // Rest of the WP_Query arguments
    );


	if (isset($_GET['tutorial-type']) && !empty($_GET['tutorial-type'])) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'tutorial_type',
				'field' => 'slug',
				'terms' => $_GET['tutorial-type'],
			),
		);
	}
	
	if (isset($_GET['tutorial-search']) && !empty($_GET['tutorial-search'])) {
		$args['s'] = $_GET['tutorial-search'];
	}
	
	$query = new WP_Query($args);
	?>
	<div class="tutorialGrid">
	<?php
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$terms = get_the_terms(get_the_ID(), 'tutorial_type');
			$tutorial_item_id = 'tutorial-item-' . get_the_ID(); // Create a unique ID for each tutorial item
			?>
			   <div class="tutorialItem" id="<?php echo $tutorial_item_id; ?>"> <!-- Add the unique ID to the div -->
				<div class="tutorialHeading">
					<h2><?php the_title(); ?></h2>
				</div>
				<div class="tutorialCats">
					<p>
						<?php if ($terms && !is_wp_error($terms)) : ?>
						<?php $term_names = array_map(function($term) { return $term->name; }, $terms); ?>
						<?php echo 'Tutorial Type: ' . implode(', ', $term_names); ?>
						<?php endif; ?>
					</p>
				</div>
				<div class="tutorialVideo">
<?php if (get_post_meta(get_the_ID(), 'video_link', true)) : ?>
    <a href="<?php echo esc_url(get_post_meta(get_the_ID(), 'video_link', true)); ?>" target="_blank">Video Link</a>
<?php endif; ?>

				</div>
				<div class="notesWrapper">
					<?php $notes = get_post_meta(get_the_ID(), 'notes', true); ?>
					<?php if ($notes) : ?>
						<p>Notes: <span class="notes-content"><?php echo esc_html($notes); ?></span> <button class="update-note" data-post-id="<?php echo get_the_ID(); ?>">Edit Note</button></p>
					<?php else : ?>
						<p>Notes: <span class="notes-content"></span> <button class="update-note" data-post-id="<?php echo get_the_ID(); ?>">Add Note</button></p>
					<?php endif; ?>
				</div>
				<div class="copyUrlWrapper">
					<div class="success-message">Copied!</div>
					<button class="copyUrl" data-tutorial-id="<?php echo get_the_ID(); ?>"><img src="https://www.nwws.org/wp-content/uploads/2023/04/icons8-share-48.png"></button>
				</div>
			</div>
			<?php
		}
	} else {
		echo 'No tutorials found.';
	}
	?>
	</div>
</div>
	<?php

    return ob_get_clean();
}
add_shortcode('tutorials', 'tutorials_shortcode');



