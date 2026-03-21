<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use Liszted\Controller\Calendar;
use Liszted\Controller\ProgrammeController;
use Liszted\Controller\Search;
use Liszted\Controller\Util;
use Liszted\Model\CalendarModel;

$contributer_id = Search::contributer("Thomas Adès");

$programmes = Search::programmesByContributer($contributer_id, 15, true);

$calendar = new CalendarModel();
foreach ($programmes as $programme) {
	$p = new ProgrammeController((int) $programme["id"], $programme);
	$p->addModel($calendar, $contributer_id);
}
ksort($calendar->days);

$calendars_separate = Calendar::groupedModels($calendar, true);

$calendars_separate[1]->title = "Past Performances";
$calendars_separate[0]->title = "Upcoming Performances";
?>
<div class="col span4">
<h3>Upcoming Performances</h3>
<?php $calendar_to_render = $calendars_separate[0]; ?>
	<?php if (!empty($calendar_to_render->performances)) { ?>
		<ul class="performances">
		<?php
		for ($p = 0; $p < min(10, count($calendar_to_render->performances)); $p++) {
		    $programme = $calendar_to_render->performances[$p];
		?>

		<li>
			<a href="/performances/" class="full">
			<?php for ($c = 0, $l = count($programme->works); $c < $l; $c++) {
				$work = $programme->works[$c];
				$composer = NULL;
				$arranger = NULL;

				foreach ($work->contributers as $contributer) {
					if ($contributer->role == "Composer") {
						$composer = $contributer;
					} elseif ($contributer->role == "Arranger") {
						$arranger = $contributer;
					} else {
						$composer = $contributer;
					}
				}
			?>
			<?php if ($composer && $composer->last_name) { ?><strong><?=$composer->last_name?><?php if ($arranger && $arranger->last_name) { ?> arr. <?=$arranger->last_name?><?php } ?></strong><?php } ?> <?=$work->name?><?=$c < $l - 1 ? "<br /> " : "" ?>

			<?php } ?>
			<br />
			<?php for ($c = 0, $l = count($programme->venues); $c < $l; $c++) {
				$venue = $programme->venues[$c];
			?>
			<?=$venue->city?>, <?=$venue->country?><?php if (!empty($programme->roles)) { ?><br /> with Thomas Ad&egrave;s<?php if (!empty($programme->roles[0])) { ?>, <?=Util::join($programme->roles, false)?><?php } ?><?php } ?><?=$c < $l - 1 ? "<br /> " : "" ?>
			<?php } ?>
			<br /><?=Util::simplifyDates($programme->dates, true);?></a>
		</li>
		<?php } ?>
		</ul>
	<?php } ?>
</div>
