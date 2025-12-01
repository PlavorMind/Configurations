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

// This is same as the default in MediaWiki 1.45 or newer.
$wgEnableProtectionIndicators = true;
$wgSpecialContributeSkinsEnabled = ['vector-2022'];
$wgUseCodexSpecialBlock = true;

//< Not listed >

// 1.46+
$wgEnableWatchlistLabels = true;
// 1.45+
$wgUseLeximorph = true;

//< Extensions >

//<< AbuseFilter >>

$wgAbuseFilterEnableBlockedExternalDomain = true;

//<< Cite >>

// 1.45+
$wgCiteSubRefMergeInDevelopment = true;
$wgCiteUseLegacyBacklinkLabels = false;

//<< CiteThisPage >>

$wgCiteThisPageAdditionalNamespaces[NS_PROJECT] = true;

//<< CodeMirror >>

/*
Removed in MediaWiki 1.45
Merge streagy of this setting is array_merge.
*/
$wgCodeMirrorContentModels = [
  'css',
  'javascript',
  'json',
];
// 1.45+
$wgCodeMirrorEnabledModes['css'] = true;
// 1.45+
$wgCodeMirrorEnabledModes['javascript'] = true;
// 1.45+
$wgCodeMirrorEnabledModes['json'] = true;

//<< Nuke >>

$wgNukeUIType = 'codex';

//<< Scribunto >>

// 1.45+
$wgScribuntoUseCodeMirror = true;

//<< TemplateData >>

$wgTemplateDataEnableDiscovery = true;
// Experimental
$wgTemplateDataMaxFavorites = 10;

//<< VisualEditor >>

// Experimental
$wgVisualEditorAllowExternalLinkPaste = true;
// 1.46+
$wgVisualEditorEnableSectionEditingFullPageButtons = true;
$wgVisualEditorEnableVisualSectionEditing = true;

//< Skins >

//<< Vector >>

$wgVectorNightMode['logged_out'] = true;
$wgVectorWrapTablesTemporary = true;
