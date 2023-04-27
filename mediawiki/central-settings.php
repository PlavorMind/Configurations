<?php
//< User rights, access control and monitoring >

$wgGroupPermissions['steward']['userrights'] = true;

if ($wmgGlobalAccountMode === 'shared-db') {
  $wgGroupPermissions['steward']['editinterface'] = true;
  $wgGroupPermissions['steward']['editusercss'] = true;
}

if ($wmgGlobalAccountMode !== null) {
  $wgGroupPermissions['admin']['editinterface'] = false;
  $wgGroupPermissions['admin']['editusercss'] = false;
  $wgGroupPermissions['steward']['userrights-interwiki'] = true;
}

if ($wmgGlobalAccountMode !== 'centralauth') {
  // This permission was moved from Renameuser extension to core in MediaWiki 1.40.
  $wgGroupPermissions['steward']['renameuser'] = true;
}

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
  $wgGroupPermissions['steward'] = array_merge($wgGroupPermissions['steward'], [
    'centralauth-lock' => true,
    'centralauth-rename' => true,
    'centralauth-suppress' => true,
    'centralauth-unmerge' => true,
    'globalgroupmembership' => true,
    'globalgrouppermissions' => true
  ]);
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

$wgCUGDisableGroups = array_diff($wgCUGDisableGroups, ['steward']);

//<< Renameuser >>

if ($wmgGlobalAccountMode !== 'centralauth') {
  // This permission was moved from Renameuser extension to core in MediaWiki 1.40.
  $wgGroupPermissions['steward']['renameuser'] = true;
}
