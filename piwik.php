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
		$piwik_async = $rcmail->config->get('piwik_async');
		$set_domain_name = '';

		if (!empty($piwik_url) && !empty($piwik_site_id)) {
			if ($piwik_async) {
				$srcipt = "
				<!-- Piwik -->
				<script type='text/javascript'>
				var _paq = _paq || [];
				(function(){
					var u=(('https:' == document.location.protocol) ? 'https://$piwik_url' : 'http://$piwik_url');
					_paq.push(['setSiteId', $piwik_site_id]);
					_paq.push(['setTrackerUrl', u+'piwik.php']);
					_paq.push(['trackPageView']);
					_paq.push(['enableLinkTracking']);
					var d=document,
						g=d.createElement('script'),
						s=d.getElementsByTagName('script')[0];
						g.type='text/javascript';
						g.defer=true;
						g.async=true;
						g.src=u+'piwik.js';
						s.parentNode.insertBefore(g,s);
				})();
				<!-- End Piwik Tracking Tag -->";
			} else {
				$script = "
				<!-- Piwik -->
				<script type='text/javascript'>
				var pkBaseURL = (('https:' == document.location.protocol) ? 'https://$piwik_url/' : 'http://$piwik_url');
				document.write(unescape('%3Cscript src=\"' + pkBaseURL + 'piwik.js\" type=\"text/javascript\"%3E%3C/script%3E'));
				</script><script type='text/javascript'>
				try {
				var piwikTracker = Piwik.getTracker(pkBaseURL + 'piwik.php', $piwik_site_id);
				piwikTracker.trackPageView();
				piwikTracker.enableLinkTracking();
				} catch( err ) {}
				</script><noscript><p><img src='http://$piwik_url/piwik.php?idsite=$piwik_site_id' style='border:0' alt='' /></p></noscript>
				<!-- End Piwik Tracking Tag -->";
			}

			$rcmail->output->add_footer($script);
		}

		return $args;
	}
}

?>
