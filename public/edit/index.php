<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use Liszted\Controller\Calendar;
use Liszted\Controller\ContributerModel;
use Liszted\Controller\Form;
use Liszted\Controller\HTML;
use Liszted\Controller\ProgrammeController;
use Liszted\Controller\SeriesController;
use Liszted\Controller\UserSession;
use Liszted\Controller\Util;
use Liszted\Database\Connection;
use Liszted\Model\CalendarModel;
use Liszted\Model\ContributerModel as ContributerModelClass;

UserSession::fetch();
echo HTML::head(empty(UserSession::$id) ? "Sign in" : "Programmes");
?>
<body>
<?php
if (str_contains($_SERVER['QUERY_STRING'] ?? '', 'signout')) {
    UserSession::signout();
}

if (empty(UserSession::$id) || isset($_POST['username'])) {
    $loggedIn = false;
    $wrongPassword = false;

    if (isset($_POST['username'])) {
        $username = preg_replace('/[^a-z0-9_]/', '', $_POST['username']);
        $userEntity = \Liszted\Entity\User::findByUsername($username);

        if ($userEntity !== null) {
            if (!empty($_POST['password'])) {
                if (!empty($userEntity->hash) && Util::verifyPassword($_POST['password'], $userEntity->hash)) {
                    $loggedIn = true;
                    UserSession::set($userEntity->id);
                } else {
                    $wrongPassword = true;
                }
            } else {
                $wrongPassword = true;
            }
        } else {
            $wrongPassword = true;
        }
    }

    if (!$loggedIn) {
?>
<div class="user-chooser">
<h2>Please enter your name &amp; password</h2>
<form action="/edit/" method="post">
<?=Form::textInput("username", "", "Username")?><br /><br />
<?=Form::password("password", "", "Password")?>
<div class="warning"><?=($wrongPassword ? "Wrong username or password" : "")?></div>
<div>
<?=Form::button("sign-in", "Sign In", "big", ["type" => "submit"])?>
</div>
</form>
</div>
<?php
    }
}

if (!empty(UserSession::$id)) {
    $user = \Liszted\Entity\User::find(UserSession::$id);
?>
<div class="programme-list">
<h2>Hello, <?=htmlspecialchars($user->name ?? '', ENT_QUOTES, 'UTF-8')?> <a href="/edit/?signout" title="Sign Out">(x)</a></h2>

<a href="/edit/programme/?new" class="button" style="margin: 20px 0; float: left;">New Programme</a>
<div style="float: right;margin-top: 20px;">
Showing <select id="event-filter">
<option value="new">Future Events</option>
<option value="old">Past Events</option>
</select>
</div>
<div class="programmes">
<?php
    $programmes = Connection::fetchAll(
        "SELECT *,
            (SELECT start FROM performance WHERE programme = programme.id ORDER BY start ASC LIMIT 1) AS start
        FROM programme
        WHERE (SELECT user FROM series WHERE series.id = programme.series) = ?
          AND (SELECT start FROM performance WHERE programme = programme.id ORDER BY start DESC LIMIT 1) > NOW()
        ORDER BY start",
        [UserSession::$id]
    );

    $calendar = new CalendarModel();
    foreach ($programmes as $prog) {
        $series = new SeriesController((int) $prog['series']);
        $programme = new ProgrammeController((int) $prog['id'], $prog);
        $programme->addModel($calendar);
    }
    ksort($calendar->days);

    $calendars = Calendar::distinctModels($calendar);
    $calendars[0]->title = "new";
    $calendars[1]->title = "old";
?>

<?php foreach ($calendars as $cal): ?>
    <?php if (!empty($cal->performances)): ?>
        <div class="calendar <?=htmlspecialchars($cal->title ?? '', ENT_QUOTES, 'UTF-8')?>">
        <?php foreach ($cal->performances as $prog): ?>
        <div class="programme">
            <div class="main">
                <div class="work-list">
                    <a href="/edit/programme/?id=<?=$prog->id?>">
                    <?php if (count($prog->works) > 0): ?>
                        <?php foreach ($prog->works as $c => $work): ?>
                            <?php $contributer = !empty($work->contributers) ? $work->contributers[0] : new ContributerModelClass(); ?>
                            <?php if ($contributer->last_name): ?><strong><?=htmlspecialchars($contributer->last_name, ENT_QUOTES, 'UTF-8')?></strong><?php endif; ?> <?=htmlspecialchars($work->name ?? '', ENT_QUOTES, 'UTF-8')?>
                            <?=$c < count($prog->works) - 1 ? "<br /> " : ""?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        [No works listed]
                    <?php endif; ?>
                    </a>
                </div>
                <div class="dates">
                    <?=Util::simplifyDates($prog->dates, true, $prog->date_accuracy)?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
</div>
</div>
</div>
<?php } ?>
<script src="/js/global.js"></script>
<script src="/js/editApi.js"></script>
<script src="/js/util.patterns.js"></script>
<script src="/js/edit.js"></script>
</body>
</html>
