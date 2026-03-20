<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use Liszted\Controller\Calendar;
use Liszted\Controller\ProgrammeController;
use Liszted\Controller\Search;
use Liszted\Controller\Util;
use Liszted\Model\CalendarModel;

$contributer_id = Search::contributer("Thomas Adès");

$work = urldecode($_GET["work"] ?? '');

$programmes = Search::programmesFeaturing($contributer_id, $work, 3000);

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

<?php foreach ($calendars_separate as $calendar_to_render) { ?>
	<?php if (!empty($calendar_to_render->performances)) { ?>
	<div class="col span4">
		<h3><?=$calendar_to_render->title?></h3>
		<ul class="performances scroll">
		<?php
		foreach ($calendar_to_render->performances as $programme) {
		?>
		<li>
				<?=Util::simplifyDates($programme->dates, false);?><br />
				<strong>
					<?php for ($c = 0, $l = count($programme->venues); $c < $l; $c++) {
						$venue = $programme->venues[$c];
					?>
					<?=$venue->name?>, <?=$venue->city?>, <?=$venue->country?><?=$c < $l - 1 ? ", " : "" ?>
					<?php } ?>
				</strong><br />
				<?php if (!empty($programme->roles)) { ?>Thomas Adès<?php if (!empty($programme->roles[0])) { ?> (<?=Util::join($programme->roles, false)?>)<?php } ?>, <?php } ?>
				<?php
				for ($c = 0, $l = count($programme->contributers); $c < $l; $c++) {
					$contributer = $programme->contributers[$c];
				?>
					<?=trim($contributer->first_name . " " . $contributer->last_name)?><?php if ($contributer->role) { ?> (<?=$contributer->role?>)<?php } ?><?=$c < $l - 1 ? ", " : "" ?><?php } ?>

		</li>
		<?php } ?>
	</ul>
	</div>
	<?php } ?>
<?php } ?>
