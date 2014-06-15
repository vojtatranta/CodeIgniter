<div class="flash" data-duration="<?= $duration ?>">
	<div class="flash-messages">
		<?php foreach ($messages as $k => $msg): ?>
			<div class="single-flash <?= $msg['status'] ?>"><?= $msg['content'] ?></div>
		<?php endforeach ?>
	</div>
</div>