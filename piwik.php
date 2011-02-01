<?php
	
/**
 * piwik
 *
 * Bind piwik analytics script.
 *
 * @version 1.0 - 31/01/2011
 * @author  Damien Churchill <damoxc@gmail.com>
 * @website http://github.com/damoxc/roundcube_piwik
 * @license GNU GPL
 */

class piwik extends rcube_plugin {
	function init() {
		if (file_exists("./plugins/piwik/config/config.inc.php")) {
			$this->load_config('config/config.inc.php');
		} else {
			$this->load_config('config/config.inc.php.dist');
		}

		$this->add_hook('render_page', array($this, 'add_script'));
	}

	function add_script($args) {
		$rcmail = rcmail::get_instance();
		$exclude = array_flip($rcmail->config->get('piwik_exclude'));

		if (isset($exclude[$args['template']])) {
			return $args;
		}
		
		if ($rcmail->config->get('piwik_privacy')) {
			if (!empty($_SESSION['user_id'])) {
				return $args;
			}
		}

		$piwik_site_id = $rcmail->config->get('piwik_site_id');
		$piwik_url = $rcmail->config->get('piwik_url');
		$set_domain_name = '';

		if (!empty($piwik_url) && !empty($piwik_site_id)) {
			$script = '
			<!-- Piwik -->
			<script type="text/javascript">
			var pkBaseURL = (("https:" == document.location.protocol) ? "https://' . $piwik_url . '/" : "http://' . $piwik_url . '/");
			document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));
			</script><script type="text/javascript">
			try {
			var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", ' . $piwik_site_id . ');
			piwikTracker.trackPageView();
			piwikTracker.enableLinkTracking();
			} catch( err ) {}
			</script><noscript><p><img src="http://' . $piwik_url . '/piwik.php?idsite=' . $piwik_site_id .'" style="border:0" alt="" /></p></noscript>
			<!-- End Piwik Tracking Tag -->';

			$rcmail->output->add_footer($script);
		}

		return $args;
	}
}

?>
