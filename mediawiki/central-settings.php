<?php
//< User rights, access control and monitoring >

// 1.46+
$wgGroupPermissions['steward']['ignore-restricted-groups'] = true;
$wgGroupPermissions['steward']['userrights'] = true;

if ($wmgGlobalAccountMode === 'shared-db') {
  $wgGroupPermissions['steward']['editinterface'] = true;
  $wgGroupPermissions['steward']['editusercss'] = true;
  $wgGroupPermissions['steward']['interwiki'] = true;
  $wgGroupPermissions['steward']['renameuser-global'] = true;
}

if ($wmgGlobalAccountMode !== null) {
  $wgGroupPermissions['admin']['editinterface'] = false;
  $wgGroupPermissions['admin']['editusercss'] = false;
  $wgGroupPermissions['admin']['interwiki'] = false;
  $wgGroupPermissions['steward']['userrights-interwiki'] = true;
}

if ($wmgGlobalAccountMode !== 'centralauth') {
  $wgGroupPermissions['steward']['renameuser'] = true;
}

//< Import/Export >

$wgImportSources = [];

//< Extensions >

//<< AbuseFilter >>

if ($wmgUseExtensions['AbuseFilter'] && $wmgGlobalAccountMode === 'centralauth') {
  $wgAbuseFilterIsCentral = true;

  $wgGroupPermissions['steward']['abusefilter-modify-global'] = true;
}

//<< AntiSpoof >>

if ($wmgUseExtensions['AntiSpoof']) {
  $wgGroupPermissions['steward']['override-antispoof'] = true;
}

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

//<< OATHAuth >>

if ($wmgUseExtensions['OATHAuth']) {
  $wgGroupPermissions['steward']['oathauth-disable-for-user'] = true;
  $wgGroupPermissions['steward']['oathauth-verify-user'] = true;
}

//<< PlavorMindTools >>

$wgCUGDisableGroups = array_diff($wgCUGDisableGroups, ['steward']);
