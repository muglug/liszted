<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use Liszted\Controller\Calendar;
use Liszted\Controller\ProgrammeController;
use Liszted\Controller\Search;
use Liszted\Controller\Util;
use Liszted\Model\CalendarModel;

$contributer_id = Search::contributer("Thomas Adès");

$programmes = Search::programmesByContributer($contributer_id, 30000, true);

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

<?php $calendar_to_render = $calendars_separate[0]; ?>
	<?php if (!empty($calendar_to_render->performances)) { ?>
		<div class="calendar">
		<?php
		foreach ($calendar_to_render->performances as $programme) {
		?>
		<div class="programme">
			<div class="main">
				<div class="dates">

					<p><?=Util::simplifyDates($programme->dates, true);?></p>
				</div>
				<div class="works">
					<div class="roles">
						<p><?php if (!empty($programme->roles)) { ?><img src="/img/ades_mini.jpg" /><?php if (!empty($programme->roles[0])) { ?> as <?=Util::join($programme->roles, false)?><?php } ?><?php } ?></p>
					</div>
					<p>
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
					<?php if ($composer && $work->url) { ?><a href="<?=$work->url?>"><?php } ?><?php if ($composer && $composer->last_name) { ?><strong><?=$composer->last_name?><?php if ($arranger && $arranger->last_name) { ?> arr. <?=$arranger->last_name?><?php } ?></strong><?php } ?> <?=$work->name?><?php if ($composer && $work->url) { ?></a><?php } ?><?=$c < $l - 1 ? "<br /> " : "" ?>
					<?php } ?>
					</p>
					<div class="contributers">
				<?php
				for ($c = 0, $l = count($programme->contributers); $c < $l; $c++) {
					$contributer = $programme->contributers[$c];
				?>
					<div><?=trim($contributer->first_name . " " . $contributer->last_name)?><span><?php if ($contributer->role) { ?> (<?=$contributer->role?>)<?php } ?><?=$c < $l - 1 ? "," : "" ?></span></div>
					<?php } ?>
				</div>
				</div>

				<div class="venues">
					<p>
					<?php for ($c = 0, $l = count($programme->venues); $c < $l; $c++) {
						$venue = $programme->venues[$c];
					?>
					<?=$venue->name?><br /><?=$venue->city?><br /><?=$venue->country?><?=$c < $l - 1 ? "<br /> " : "" ?>
					<?php } ?>
					</p>
				</div>
			</div>

		</div>
		<?php } ?>
	<?php } ?>
</div>
