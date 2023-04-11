<?php
//< General Settings >

$wgSitename = '오사위키덤프';

//< Server URLs and file paths >

$wgLogos = [
  '1x' => "/resources/per-wiki/$wmgWiki/logos/logo-1x.png",
  '1.5x' => "/resources/per-wiki/$wmgWiki/logos/logo-1.5x.png",
  '2x' => "/resources/per-wiki/$wmgWiki/logos/logo-2x.png",
  'icon' => "/resources/per-wiki/$wmgWiki/logos/logo-2x.png"
];

//< User rights, access control and monitoring >

$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['user']['edit'] = false;
