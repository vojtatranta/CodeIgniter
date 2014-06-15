<?php $this->load->view('common_header'); ?>

	<script type="text/javascript">
	var _adftrack = {
	    pm: 220486,
	    id: 4923807
	};
	(function(){var s=document.createElement('script');s.type='text/javascript';s.async=true;s.src='https://track.adform.net/serving/scripts/trackpoint/async/';var x = document.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);})();
	</script>
	<noscript>
	    <p style="margin:0;padding:0;border:0;">
	        <img src="https://track.adform.net/Serving/TrackPoint/?pm=220486&amp;lid=4923807" width="1" height="1" alt="" />
	    </p>
	</noscript>

	<div id="main-area" class="padding simple-page <?= (isset($pageClass)) ? $pageClass  : '' ?>">
		<div class="centered-block">
			<?= (isset($page_heading)) ? '<h2>'.$page_heading.'</h2>' : '' ?>
			<?= $content ?>
		</div><!--/.centered-block-->
	</div><!--/#main-area-->

<?php $this->load->view('common_footer'); ?>