<?php
//< General Settings >

$wgSitename = 'PlavorMind Central';

//< Server URLs and file paths >

$wgLogos = [
  '1x' => "$wmgCDNBaseURL/per-wiki/$wmgWiki/logos/logo-1x.png",
  '1.5x' => "$wmgCDNBaseURL/per-wiki/$wmgWiki/logos/logo-1.5x.png",
  '2x' => "$wmgCDNBaseURL/per-wiki/$wmgWiki/logos/logo-2x.png",
  'icon' => "$wmgCDNBaseURL/per-wiki/$wmgWiki/logos/logo.svg",
  'svg' => "$wmgCDNBaseURL/per-wiki/$wmgWiki/logos/logo.svg"
];

//< Files and file uploads >

//<< Images >>

//<<< Thumbnail settings >>>

$wgThumbnailNamespaces = [NS_FILE, NS_HELP, NS_MAIN, NS_PROJECT, NS_USER];

//< Language, regional and character encoding settings >

if ($wmgDebugLevel >= 1) {
  $wgUseXssLanguage = true;
}

//< ResourceLoader >

$wgAllowSiteCSSOnRestrictedPages = true;

//< Page titles and redirects >

//<< Namespaces >>

$wgMetaNamespace = 'PlavorMind';
$wgNamespaceAliases = [
  '@' => NS_USER,
  'PlavorMind_Central' => NS_PROJECT,
  'PM' => NS_PROJECT
];

//< Interwiki links and sites >

$wgLocalInterwikis = ['central'];

//< User rights, access control and monitoring >

//<< Access >>

$wgEnableMultiBlocks = true;
$wgNamespaceProtection = [
  NS_PROJECT => ['editprotected-admin'],
  NS_TEMPLATE => ['editprotected-admin']
];
// $wgNonincludableNamespaces

//< Copyright >

$wgUseCopyrightUpload = true;

//< Logging >

$wgFilterLogTypes['create'] = true;

//< Miscellaneous settings >

// 1.43+
$wgEnableProtectionIndicators = true;
$wgSpecialContributeSkinsEnabled = ['vector-2022'];
$wgUseCodexSpecialBlock = true;

//< Not listed >

// 1.43+
$wgSortedCategories = true;

//< Extensions >

//<< AbuseFilter >>

$wgAbuseFilterEnableBlockedExternalDomain = true;

//<< CiteThisPage >>

$wgCiteThisPageAdditionalNamespaces[NS_PROJECT] = true;

//<< VisualEditor >>

// Experimental
$wgVisualEditorAllowExternalLinkPaste = true;
$wgVisualEditorEnableVisualSectionEditing = true;

//< Skins >

//<< MinervaNeue >>

$wgMinervaNightMode['base'] = true;
// 1.43+
$wgMinervaNightMode['loggedin'] = true;

//<< Vector >>

// 1.43+
$wgVectorAppearance['logged_in'] = true;
$wgVectorNightMode = [
  'logged_in' => true,
  'logged_out' => true
];
// 1.43+
$wgVectorWrapTablesTemporary = true;
