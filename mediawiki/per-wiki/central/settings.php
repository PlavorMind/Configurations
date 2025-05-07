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

//< Parser >

$wgParserEnableUserLanguage = true;

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

//< Category >

$wgSortedCategories = true;

//< Logging >

$wgFilterLogTypes['create'] = true;

//< Miscellaneous settings >

$wgEnableProtectionIndicators = true;
$wgSpecialContributeSkinsEnabled = ['vector-2022'];
$wgUseCodexSpecialBlock = true;

//< Extensions >

//<< AbuseFilter >>

$wgAbuseFilterEnableBlockedExternalDomain = true;

//<< Cite >>

// 1.44+
$wgCiteUseLegacyBacklinkLabels = false;

//<< CiteThisPage >>

$wgCiteThisPageAdditionalNamespaces[NS_PROJECT] = true;

//<< CodeMirror >>

/*
1.44+
Experimental
Merge streagy of this setting is array_merge.
*/
$wgCodeMirrorContentModels = [
  'css',
  'javascript',
  'json',
];

//<< Nuke >>

// 1.44+
$wgNukeUIType = 'codex';

//<< TemplateData >>

// 1.44+
$wgTemplateDataEnableDiscovery = true;
// Experimental
$wgTemplateDataMaxFavorites = 10;

//<< VisualEditor >>

// Experimental
$wgVisualEditorAllowExternalLinkPaste = true;
$wgVisualEditorEnableVisualSectionEditing = true;

//< Skins >

//<< MinervaNeue >>

// This is same as the default in MediaWiki 1.44 or newer.
$wgMinervaNightMode['base'] = true;
// This is same as the default in MediaWiki 1.44 or newer.
$wgMinervaNightMode['loggedin'] = true;

//<< Vector >>

$wgVectorNightMode['logged_out'] = true;
$wgVectorWrapTablesTemporary = true;
