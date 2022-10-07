<?php
//< General Settings >

$wgSitename = 'PlavorMind Central';

//< Server URLs and file paths >

$wgLogos = [
  '1x' => "/resources/per-wiki/$wmgWiki/logos/logo-1x.png",
  '1.5x' => "/resources/per-wiki/$wmgWiki/logos/logo-1.5x.png",
  '2x' => "/resources/per-wiki/$wmgWiki/logos/logo-2x.png",
  'icon' => "/resources/per-wiki/$wmgWiki/logos/logo-2x.png"
];

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

if ($wmgGlobalAccountMode === 'shared-db') {
  $wgGroupPermissions['steward'] = array_merge($wgGroupPermissions['steward'], [
    'editinterface' => true,
    'editsitecss' => true,
    'editsitejson' => true,
    'editusercss' => true,
    'edituserjson' => true,
  ]);
}

if ($wmgGlobalAccountMode !== null) {
  $wgGroupPermissions['admin'] = array_merge($wgGroupPermissions['admin'], [
    'editinterface' => false,
    'editsitecss' => false,
    'editsitejson' => false,
    'editusercss' => false,
    'edituserjson' => false
  ]);
  $wgGroupPermissions['steward']['userrights'] = true;
  $wgGroupPermissions['steward']['userrights-interwiki'] = true;
}

//<< Access >>

$wgEnablePartialActionBlocks = true;
$wgNamespaceProtection = [
  NS_PROJECT => ['editprotected-admin'],
  NS_TEMPLATE => ['editprotected-admin']
];
// $wgNonincludableNamespaces

//< Copyright >

$wgUseCopyrightUpload = true;

//< Import/Export >

$wgImportSources = [];

//< Extensions >

//<< AbuseFilter >>

if ($wmgGlobalAccountMode === 'centralauth') {
  $wgAbuseFilterIsCentral = true;
  $wgGroupPermissions['steward']['abusefilter-modify-global'] = true;
}

//<< AntiSpoof >>

$wgGroupPermissions['steward']['override-antispoof'] = true;

//<< CentralAuth >>

if ($wmgGlobalAccountMode === 'centralauth') {
  $wgGroupPermissions['steward']['centralauth-lock'] = true;
  $wgGroupPermissions['steward']['centralauth-rename'] = true;
  $wgGroupPermissions['steward']['centralauth-suppress'] = true;
  $wgGroupPermissions['steward']['centralauth-unmerge'] = true;
  $wgGroupPermissions['steward']['globalgroupmembership'] = true;
  $wgGroupPermissions['steward']['globalgrouppermissions'] = true;
}

//<< GlobalBlocking >>

if ($wmgGlobalAccountMode !== null) {
  $wgGroupPermissions['steward']['globalblock'] = true;
}

//<< Interwiki >>

if ($wmgGlobalAccountMode === 'shared-db') {
  $wgGroupPermissions['steward']['interwiki'] = true;
}

if ($wmgGlobalAccountMode !== null) {
  $wgGroupPermissions['admin']['interwiki'] = false;
}

//<< OATHAuth >>

$wgGroupPermissions['steward']['oathauth-disable-for-user'] = true;
$wgGroupPermissions['steward']['oathauth-verify-user'] = true;

//<< PlavorMindTools >>

$wgPMTDisableUserGroups = array_diff($wgPMTDisableUserGroups, ['steward']);

//<< Renameuser >>

if ($wmgGlobalAccountMode === 'shared-db') {
  $wgGroupPermissions['steward']['renameuser'] = true;
}

//< Skins >

//<< Vector >>

// 1.40+
$wgVectorArticleTools = [
  'logged_in' => true,
  'logged_out' => true
];
$wgVectorVisualEnhancementNext = true;
