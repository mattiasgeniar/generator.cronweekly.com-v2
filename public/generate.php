<?php

require('../config.php');

$content = $_POST['markdown'];

$pathToCronWeekly = "/Users/". $user ."/Projects/Webdevelopment/ma.ttias.be/content/cronweekly/";

$lastIssue = `ls -h /Users/${user}/Projects/Webdevelopment/ma.ttias.be/content/cronweekly | grep 'issue' | tail -n 1`;
$lastIssuePieces = explode('-issue-', $lastIssue);
$lastIssueNumber = (int) str_replace('.md', '', $lastIssuePieces[1]);

$newIssueNumber = $lastIssueNumber + 1;
$newIssueFilename = $pathToCronWeekly . date("Y-m-d") ."-issue-". $newIssueNumber .".md";

$nextSunday = date("Y-m-d", strtotime("next Sunday"));
$today = date("Y-m-d");

$template = <<<TEMPLATE
---
title: 'cron.weekly issue #{$newIssueNumber}: '
author: mattias

date: {$nextSunday}T06:50:00+01:00
publishDate: {$nextSunday}T06:50:00+01:00
url: /cronweekly/issue-{$newIssueNumber}/
---

Hi everyone! 👋

Welcome to cron.weekly issue #{$newIssueNumber}.

# News & general 🗞

# Tools & Projects 🛠

# Guides & Tutorials 🎓


{$content}

TEMPLATE;

// Save the file

file_put_contents($newIssueFilename, $template);

exec('/usr/local/bin/code '. $newIssueFilename);
