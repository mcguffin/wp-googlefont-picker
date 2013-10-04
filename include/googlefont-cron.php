<?php





function googlefont_cron_periods( $schedules ) {
	// Adds once weekly to the existing schedules.
	$day = 3600*24;
	if ( ! isset($schedules['weekly']) ) {
		$schedules['weekly'] = array(
			'interval' => $day*7,
			'display' => __( 'Once Weekly' )
		);
	}
	if ( ! isset($schedules['monthly']) ) {
		$schedules['monthly'] = array(
			'interval' => $day*30,
			'display' => __( 'Every 30 Days','googlefont' )
		);
	}
	if ( ! isset($schedules['yearly']) ) {
		$schedules['yearly'] = array(
			'interval' => $day*365,
			'display' => __( 'Once a Year', 'googlefont' )
		);
	}
	return $schedules;
}
add_filter( 'cron_schedules', 'googlefont_cron_periods' );

function googlefont_cron_refresh( ) {
	Googlefont_Api::get_instance()->refresh();
}

add_action( 'googlefont_cron_refresh' , 'googlefont_cron_refresh' );
