<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use Liszted\Controller\Calendar;
use Liszted\Controller\ProgrammeController;
use Liszted\Controller\Search;
use Liszted\Controller\Util;
use Liszted\Entity\Role;
use Liszted\Model\CalendarModel;

$contributer_id = Search::contributer("Ted Huffman");
$company_role_id = Role::search("Company");

$programmes = Search::programmesByContributer($contributer_id);

$calendar = new CalendarModel();
foreach ($programmes as $programme) {
	$p = new ProgrammeController((int) $programme["id"], $programme);
	$p->addModel($calendar, $contributer_id, $company_role_id);
}
ksort($calendar->days);

$calendars_separate = Calendar::distinctModels($calendar);

$calendars_separate[1]->performances = [];
$calendars_separate[0]->title = "";
?>

<?php foreach ($calendars_separate as $cal) { ?>
	<?php if (!empty($cal->performances)) { ?>
		<h4><?=$cal->title?></h4>
		<div class="calendar">
		<?php
		foreach ($cal->performances as $programme) {
		?>
		<div class="programme">
			<div class="main">
				<div class="dates">
					<?=Util::simplifyDates($programme->dates, true, $programme->date_accuracy);?>
				</div>
				<div class="works">
					<?php for ($c = 0, $l = count($programme->works); $c < $l; $c++) {
						$work = $programme->works[$c];
						$contributer = $work->contributers[0] ?? null;
					?>
					<?=$work->name?> <?php if ($contributer && $contributer->last_name) { ?>(<?=$contributer->last_name?>)<?php } ?><?=$c < $l - 1 ? "<br /> " : "" ?>
					<?php } ?>
				</div>
				<div class="roles">
					<?=Util::join($programme->roles, true)?>
				</div>
				<div class="company">
					<?=Util::join($programme->companies, true)?>
				</div>
			</div>
			<div class="contributers">
				<?php
				for ($c = 0, $l = count($programme->contributers); $c < $l; $c++) {
					$contributer = $programme->contributers[$c];
				?>
				<div><?=$contributer->first_name?> <?=$contributer->last_name?> <span>(<?=$contributer->role?>)<?=$c < $l - 1 ? ", " : "" ?></span></div>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
	<?php } ?>
<?php } ?>
</div>
