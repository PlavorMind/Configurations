<?php
function getWiki(string $defaultDomain, string $uploadDomain, array $customDomains): ?string {
  if (PHP_SAPI === 'cli') {
    if (defined('MW_WIKI_NAME')) {
      return MW_WIKI_NAME;
    }

    return null;
  }

  $currentDomain = parse_url('//' . $_SERVER['HTTP_HOST'], PHP_URL_HOST);
  $wiki = array_search($currentDomain, $customDomains, true);

  if ($wiki !== false) {
    return $wiki;
  }

  foreach ([$defaultDomain, $uploadDomain] as $expectedDomain) {
    // PCRE requires escaping "-" next to a character class.
    $regex = str_replace('%wiki%', '([\\w\\-]+)', preg_quote($expectedDomain, '/'));

    if (preg_match("/^{$regex}$/i", $currentDomain, $matches)) {
      return $matches[1];
    }
  }
}

//< Custom settings >

$wmgBaseURL = 'https://%domain%';
// 1 minute
$wmgCacheExpiry = 60;
$wmgCacheType = CACHE_MEMCACHED;
$wmgCentralWiki = 'central';
$wmgCDNBaseURL = 'https://cdn.plavor.mind.local/wikis';
$wmgCIDRLimit = [
  // ###.0.0.0/8
  'IPv4' => 8,
  // ####::/16
  'IPv6' => 16
];
$wmgCustomDomains = [];
$wmgDebugLevel = 0;
$wmgDefaultDomain = '%wiki%.w.plavor.mind.local';
$wmgUseExtensions = [
  // This extension should not be disabled on wikis with global account enabled.
  'AbuseFilter' => true,
  // This extension should not be disabled on wikis with global account enabled.
  'AntiSpoof' => true,
  'Babel' => true,
  'CategoryTree' => false,
  // This extension should not be disabled on wikis with global account enabled.
  'CheckUser' => true,
  'Cite' => true,
  'CiteThisPage' => false,
  'CodeEditor' => false,
  'CodeMirror' => false,
  'CommonsMetadata' => false,
  // This extension should not be disabled on wikis with global account enabled.
  'ConfirmEdit' => true,
  'DiscussionTools' => false,
  // This extension should not be disabled on wikis with global account enabled.
  'Echo' => true,
  'GlobalCssJs' => true,
  'GlobalUserPage' => true,
  'InputBox' => false,
  'Interwiki' => true,
  'Josa' => false,
  'Linter' => false,
  // This extension should not be disabled on wikis with global account enabled.
  'LoginNotify' => true,
  'Math' => false,
  'MultimediaViewer' => false,
  'Nuke' => true,
  // This extension should not be disabled on wikis with global account enabled.
  'OATHAuth' => true,
  'PageImages' => false,
  'ParserFunctions' => true,
  'Parsoid' => false,
  // This extension should not be disabled on wikis with global account enabled.
  'PlavorMindTools' => true,
  'Poem' => false,
  'Popups' => false,
  'QuickInstantCommons' => true,
  'ReplaceText' => false,
  'RevisionSlider' => false,
  'Scribunto' => false,
  // This extension should not be disabled on wikis with global account enabled.
  'SecureLinkFixer' => true,
  'SpamBlacklist' => false,
  'SyntaxHighlight_GeSHi' => false,
  'TemplateData' => false,
  'TemplateSandbox' => false,
  'TemplateStyles' => false,
  'TemplateWizard' => false,
  'TextExtracts' => false,
  'Thanks' => false,
  // This extension should not be disabled on wikis with global account enabled.
  'TitleBlacklist' => true,
  'TwoColConflict' => true,
  'UniversalLanguageSelector' => false,
  'UploadsLink' => false,
  'VisualEditor' => false,
  'WikiEditor' => true
];
$wmgUseSkins = [
  'MinervaNeue' => false,
  'Timeless' => false
];
$wmgWiki = getWiki($wmgDefaultDomain, 'default', $wmgCustomDomains);
$wmgWikis = ['central', 'osa'];

$wmgCentralDB = "pmw$wmgCentralWiki";

$domain = $wmgCustomDomains[$wmgCentralWiki] ?? str_replace('%wiki%', $wmgCentralWiki, $wmgDefaultDomain);
$wmgCentralBaseURL = str_replace('%domain%', $domain, $wmgBaseURL);
unset($domain);

switch (PHP_OS_FAMILY) {
  case 'Linux':
  $wmgDataDirectory = '/plavormind/web/data/mediawiki';
  break;

  case 'Windows':
  $wmgDataDirectory = 'C:/plavormind/web/data/mediawiki';
}

if (PHP_SAPI === 'cli' || $wmgDebugLevel >= 1) {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
}

if (!in_array($wmgWiki, $wmgWikis, true)) {
  exit('Cannot find this wiki.');
}

//<< Global accounts >>

$wmgGlobalAccountExemptWikis = [];
// 'centralauth', 'shared-db' or null
$wmgGlobalAccountMode = 'centralauth';

if (in_array($wmgWiki, $wmgGlobalAccountExemptWikis, true)) {
  $wmgGlobalAccountMode = null;
}

//< General Settings >

$wgSitename = 'Nameless';

//< Global Objects >

if ($wmgGlobalAccountMode === 'centralauth') {
  $wgConf->settings = [
    'wgArticlePath' => [
      'default' => '/page/$1'
    ],
    'wgServer' => []
  ];
  $domain = str_replace('%wiki%', '$wiki', $wmgDefaultDomain);
  $wgConf->settings['wgServer']['default'] = str_replace('%domain%', $domain, $wmgBaseURL);

  foreach ($wmgCustomDomains as $wiki => $domain) {
    if (!in_array($wiki, $wmgGlobalAccountExemptWikis, true)) {
      $wgConf->settings['wgServer'][$wiki] = str_replace('%domain%', $domain, $wmgBaseURL);
    }
  }

  $wgConf->siteParamsCallback = function ($siteConfiguration, string $wikiDB): array {
    $wiki = preg_replace('/^pmw/', '', $wikiDB);
    return [
      'lang' => 'en',
      'params' => [
        'wiki' => $wiki
      ],
      'suffix' => '',
      'tags' => [$wiki]
    ];
  };

  $wgConf->suffixes = [''];
  $wgConf->wikis = [];
  $wikis = array_diff($wmgWikis, $wmgGlobalAccountExemptWikis);

  foreach ($wikis as $wiki) {
    $wgConf->wikis[] = "pmw$wiki";
  }

  unset($domain, $wiki, $wikis);
}

//< Server URLs and file paths >

$wgArticlePath = '/page/$1';
$wgFileCacheDirectory = "$wmgDataDirectory/private/per-wiki/$wmgWiki/caches";
$wgScriptPath = '/mediawiki';
$wgUploadDirectory = "$wmgDataDirectory/private/per-wiki/$wmgWiki/uploads";
$wgUploadPath = "$wgScriptPath/img_auth.php";
$wgUsePathInfo = true;

$actions = [
  'delete',
  'edit',
  'history',
  'info',
  'markpatrolled',
  'protect',
  'purge',
  'raw',
  'render',
  'revert',
  'rollback',
  'submit',
  'unprotect',
  'unwatch',
  'watch'
];

foreach ($actions as $action) {
  $wgActionPaths[$action] = "/$action/$1";
}

$domain = $wmgCustomDomains[$wmgWiki] ?? str_replace('%wiki%', $wmgWiki, $wmgDefaultDomain);
$wgServer = str_replace('%domain%', $domain, $wmgBaseURL);
unset($action, $actions, $domain);

//< Files and file uploads >

$wgAllowCopyUploads = true;
$wgCopyUploadsDomains = ['openclipart.org'];
$wgCopyUploadsFromSpecialUpload = true;
$wgDeletedDirectory = "$wmgDataDirectory/private/per-wiki/$wmgWiki/deleted-uploads";
$wgEnableUploads = true;
$wgFileExtensions = ['gif', 'jpg', 'png', 'webp'];
$wgImgAuthDetails = true;
$wgMaxUploadSize = [
  // 3 MiB
  '*' => 1024 * 1024 * 3,
  // 1 MiB
  'url' => 1024 * 1024 * 1
];
$wgMediaInTargetLanguage = true;
$wgNativeImageLazyLoading = true;
// 1 MiB
$wgUploadSizeWarning = 1024 * 1024 * 1;

if (version_compare(MW_VERSION, '1.41', '>=')) {
  $wgFileExtensions[] = 'svg';
}

//<< MIME types >>

$wgVerifyMimeTypeIE = false;

//<< Images >>

//<<< SVG >>>

// 1.41+
$wgSVGNativeRendering = true;

//<<< Thumbnail settings >>>

$wgGenerateThumbnailOnParse = false;
$wgThumbnailScriptPath = "$wgScriptPath/thumb.php";

//< Email settings >

$wgEnableEmail = false;

//< Database settings >

$wgDBname = "pmw$wmgWiki";
$wgDBserver = '127.0.0.1';
$wgDBuser = 'root';

if ($wmgGlobalAccountMode !== null) {
  $wikis = array_diff($wmgWikis, $wmgGlobalAccountExemptWikis);

  foreach ($wikis as $wiki) {
    $wgLocalDatabases[] = "pmw$wiki";
  }

  unset($wiki, $wikis);
}

//<< SQLite-specific >>

$wgSQLiteDataDir = "$wmgDataDirectory/private/dbs";

//<< Shared DB settings >>

if ($wmgGlobalAccountMode === 'shared-db') {
  $wgSharedDB = $wmgCentralDB;
  $wgSharedTables = ['actor', 'user', 'user_autocreate_serial'];
}

//< Content handlers and storage >

$wgPageLanguageUseDB = true;
$wgRevisionCacheExpiry = $wmgCacheExpiry;

//< Cache >

$wgCacheDirectory = "$wmgDataDirectory/private/per-wiki/$wmgWiki/caches";
$wgFooterLinkCacheExpiry = $wmgCacheExpiry;
$wgLanguageConverterCacheType = $wmgCacheType;
$wgMainCacheType = $wmgCacheType;
$wgStatsCacheType = $wmgCacheType;
$wgUseFileCache = true;

//<< Message Cache >>

$wgMessageCacheType = $wmgCacheType;
$wgUseLocalMessageCache = true;

//<< Sidebar Cache >>

$wgEnableSidebarCache = true;
$wgSidebarCacheExpiry = $wmgCacheExpiry;

//<< Parser Cache >>

$wgOldRevisionParserCacheExpireTime = $wmgCacheExpiry;
$wgParserCacheExpireTime = $wmgCacheExpiry;
$wgParserCacheType = $wmgCacheType;

//<< Memcached settings >>

$wgSessionCacheType = $wmgCacheType;

//< Language, regional and character encoding settings >

$wgAllUnicodeFixes = true;

//<< Language-specific >>

//<<< English >>>

$wgAmericanDates = true;

//< Output format and skin settings >

//<< Output >>

$wgEditSubmitButtonLabelPublish = true;
$wgExternalInterwikiFragmentMode = 'html5';
$wgFragmentMode = ['html5'];

//<< Skins >>

// This is same as the default in MediaWiki 1.41 or newer.
$wgDefaultSkin = 'vector-2022';
$wgSkinMetaTags = ['og:title'];

unset($wgFooterIcons['poweredby']);

//< Page titles and redirects >

$wgCapitalLinks = false;

//< Interwiki links and sites >

$baseURL = str_replace('%domain%', $wmgDefaultDomain, $wmgBaseURL);
$regex = str_replace('%wiki%', '([\\w\\-]+)', preg_quote($baseURL, '/'));
$wgRedirectSources = "/^{$regex}$/i";
unset($baseURL, $regex);

//<< Interwiki cache >>

$wgInterwikiExpiry = $wmgCacheExpiry;

//< Parser >

$wgCleanSignatures = false;
$wgEnableScaryTranscluding = true;
$wgExternalLinkTarget = '_blank';
$wgMaxTemplateDepth = 5;
$wgNoFollowDomainExceptions = [];
$wgTranscludeCacheExpiry = $wmgCacheExpiry;
// Only allow HTTP and HTTPS protocols in links
$wgUrlProtocols = ['http://', 'https://'];

if (str_starts_with($wmgDefaultDomain, '%wiki%.')) {
  $wgNoFollowDomainExceptions[] = preg_replace('/^%wiki%\\./', '', $wmgDefaultDomain);
}
elseif (!isset($wmgCustomDomains[$wmgWiki])) {
  $wgNoFollowDomainExceptions[] = str_replace('%wiki%', $wmgWiki, $wmgDefaultDomain);
}

if (isset($wmgCustomDomains[$wmgWiki])) {
  $wgNoFollowDomainExceptions[] = $wmgCustomDomains[$wmgWiki];
}

//< Statistics and content analysis >

// 1 week
$wgActiveUserDays = 7;
$wgLearnerEdits = 15;
// 1 week
$wgLearnerMemberSince = 7;

//< User accounts, authentication >

$wgHiddenPrefs = ['gender', 'realname'];
$wgInvalidUsernameCharacters = '`~!@$%^&*()=+\\;:,.?';
$wgMaxNameChars = 30;
$wgMaxSigChars = 200;
$wgPasswordPolicy['policies'] = [
  'default' => [
    'MaximalPasswordLength' => [
      'forceChange' => true,
      'value' => 20
    ],
    'MinimalPasswordLength' => [
      'forceChange' => true,
      'value' => 6
    ],
    'MinimumPasswordLengthToLogin' => [
      'forceChange' => true,
      'value' => 1
    ],
    'PasswordCannotBeSubstringInUsername' => [
      'forceChange' => true,
      'value' => true
    ],
    'PasswordCannotMatchDefaults' => [
      'forceChange' => true,
      'value' => true
    ],
    'PasswordNotInCommonList' => [
      'forceChange' => true,
      'value' => true
    ]
  ],
  'moderator' => [
    'MinimumPasswordLengthToLogin' => 6
  ],
  'staff' => [
    'MinimalPasswordLength' => 8,
    'MinimumPasswordLengthToLogin' => 6
  ],
  'admin' => [
    'MinimalPasswordLength' => 10,
    'MinimumPasswordLengthToLogin' => 6
  ],
  'steward' => [
    'MinimalPasswordLength' => 12,
    'MinimumPasswordLengthToLogin' => 6
  ]
];
$wgSignatureAllowedLintErrors = [];
$wgSignatureValidation = 'disallow';

$wgDefaultUserOptions = array_merge($wgDefaultUserOptions, [
  // Recent Changes
  'hidecategorization' => 0,
  'usenewrc' => 0,

  // Watchlist
  'watchcreations' => 0,
  'watchdefault' => 0,
  'watchlisthidecategorization' => 0,
  'watchlistunwatchlinks' => 1,
  'watchuploads' => 0
]);
$wgReservedUsernames = array_merge($wgReservedUsernames, [
  'Example',
  'Flow talk page manager',
  'MediaWiki message delivery',
  'New user message',
  'User',
  'Username',
  '편집 필터'
]);

//<< Authentication >>

$wgAllowSecuritySensitiveOperationIfCannotReauthenticate = [
  'default' => false,
  'LinkAccounts' => true,
  'UnlinkAccount' => true
];
$wgReauthenticateTime = [
  // 10 minutes
  'default' => 60 * 10,
  // 1 minute
  'ChangeCredentials' => 60,
  // 1 minute
  'RemoveCredentials' => 60
];

//< User rights, access control and monitoring >

$wgAddGroups = [
  'staff' => ['moderator'],
  'admin' => ['moderator', 'staff']
];
// 10 years
$wgAutoblockExpiry = 60 * 60 * 24 * 365 * 10;
$wgDeleteRevisionsBatchSize = 500;
$wgGroupInheritsPermissions = [
  'moderator' => 'autoconfirmed',
  'staff' => 'moderator',
  'admin' => 'staff'
];
$wgGroupPermissions = [
  '*' => [
    'autocreateaccount' => true,
    'browsearchive' => true,
    'createaccount' => true,
    'deletedhistory' => true,
    'patrolmarks' => true,
    'read' => true,
    'unwatchedpages' => true,
    'writeapi' => true
  ],
  'user' => [
    'applychangetags' => true,
    'createpage' => true,
    'createtalk' => true,
    'edit' => true,
    'editmyoptions' => true,
    'editmyprivateinfo' => true,
    'editmyusercss' => true,
    'editmyuserjson' => true,
    'editmywatchlist' => true,
    'editprotected-user' => true,
    'minoredit' => true,
    'viewmyprivateinfo' => true,
    'viewmywatchlist' => true
  ],
  'autoconfirmed' => [
    'autoconfirmed' => true,
    'editmyuserjs' => true,
    'editmyuserjsredirect' => true,
    'editprotected-autoconfirmed' => true,
    'move' => true,
    'move-rootuserpages' => true,
    'movefile' => true,
    'reupload' => true,
    'sendemail' => true,
    'upload' => true
  ],
  'moderator' => [
    'autopatrol' => true,
    'block' => true,
    'delete' => true,
    'deletedtext' => true,
    'deleterevision' => true,
    'editprotected-moderator' => true,
    'move-categorypages' => true,
    'move-subpages' => true,
    'patrol' => true,
    'reupload-shared' => true,
    'rollback' => true,
    'suppressredirect' => true,
    'undelete' => true
  ],
  'staff' => [
    'changetags' => true,
    'deletelogentry' => true,
    'editcontentmodel' => true,
    'editprotected-staff' => true,
    'ipblock-exempt' => true,
    'managechangetags' => true,
    'markbotedits' => true,
    'protect' => true
  ],
  'admin' => [
    'deletechangetags' => true,
    'editinterface' => true,
    'editprotected-admin' => true,
    'editsitecss' => true,
    'editsitejson' => true,
    'editusercss' => true,
    'edituserjson' => true,
    'import' => true,
    'importupload' => true,
    'mergehistory' => true
  ]
];
$wgGroupPermissions['staff'] = array_merge($wgGroupPermissions['autoconfirmed'], $wgGroupPermissions['staff']);
$wgGroupPermissions['admin'] = array_merge($wgGroupPermissions['moderator'], $wgGroupPermissions['admin']);
$wgGroupsRemoveFromSelf = [
  'moderator' => ['moderator'],
  'staff' => ['staff'],
  'admin' => ['admin']
];
$wgHideUserContribLimit = 500;
$wgPasswordAttemptThrottle = [
  [
    'allIPs' => 'Value of "allIPs" key can be anything but null.',
    'count' => 5,
    // 10 minutes
    'seconds' => 60 * 10
  ],
  [
    'allIPs' => 'See includes/auth/Throttler.php.',
    'count' => 30,
    // 1 day
    'seconds' => 60 * 60 * 24
  ]
];
$wgRemoveGroups = [
  'staff' => ['moderator'],
  'admin' => ['moderator', 'staff']
];

if ($wmgGlobalAccountMode !== 'centralauth') {
  $wgGroupInheritsPermissions['steward'] = 'admin';
  $wgGroupPermissions['steward'] = [
    'apihighlimits' => true,
    'bigdelete' => true,
    'blockemail' => true,
    'editprotected-steward' => true,
    'editsitejs' => true,
    'edituserjs' => true,
    'hideuser' => true,
    'nominornewtalk' => true,
    'noratelimit' => true,
    'override-export-depth' => true,
    'pagelang' => true,
    'siteadmin' => true,
    'suppressionlog' => true,
    'suppressrevision' => true,
    'unblockself' => true,
    'upload_by_url' => true
  ];
  $wgGroupPermissions['steward'] = array_merge($wgGroupPermissions['staff'], $wgGroupPermissions['steward']);
}

$wgRateLimits = array_merge($wgRateLimits, [
  'edit' => [
    'subnet' => [3, 60],
    'user-global' => [3, 60],
    'autoconfirmed' => [6, 60],
    'moderator' => [10, 60],
    'staff' => [12, 60],
    'admin' => [20, 60]
  ],
  'move' => [
    'subnet' => [1, 60],
    'user-global' => [1, 60],
    'autoconfirmed' => [2, 60],
    'moderator' => [5, 60],
    'staff' => [5, 60],
    'admin' => [15, 60]
  ],
  'upload' => [
    'subnet-all' => [1, 60],
    'user-global' => [1, 60],
    'moderator' => [2, 60],
    'staff' => [2, 60],
    'admin' => [3, 60]
  ],
  'mailpassword' => [
    'subnet' => [3, 60 * 60 * 24]
  ],
  'sendemail' => [
    'subnet-all' => [3, 60 * 60 * 24],
    'user-global' => [3, 60 * 60 * 24],
    'admin' => [5, 60 * 60 * 24]
  ],
  'changeemail' => [
    'subnet-all' => [3, 60 * 60 * 24],
    'user-global' => [3, 60 * 60 * 24]
  ],
  'purge' => [
    'subnet' => [5, 60],
    'user-global' => [5, 60],
    'autoconfirmed' => [10, 60],
    'moderator' => [10, 60],
    'staff' => [10, 60],
    'admin' => [30, 60]
  ],
  'linkpurge' => [
    'subnet' => [4, 60],
    'user-global' => [4, 60],
    'autoconfirmed' => [6, 60],
    'moderator' => [6, 60],
    'staff' => [6, 60],
    'admin' => [20, 60]
  ],
  'renderfile' => [
    'subnet' => [30, 60],
    'user-global' => [30, 60],
    'autoconfirmed' => [60, 60],
    'moderator' => [60, 60],
    'staff' => [60, 60],
    'admin' => [60, 60]
  ],
  'renderfile-nonstandard' => [
    'subnet' => [10, 60],
    'user-global' => [10, 60],
    'autoconfirmed' => [12, 60],
    'moderator' => [20, 60],
    'staff' => [20, 60],
    'admin' => [20, 60]
  ],
  'stashedit' => [
    'subnet' => [5, 60],
    'user-global' => [5, 60],
    'autoconfirmed' => [10, 60],
    'moderator' => [20, 60],
    'staff' => [20, 60],
    'admin' => [30, 60]
  ],
  'stashbasehtml' => [
    'subnet' => [4, 60],
    'user-global' => [4, 60],
    'autoconfirmed' => [6, 60],
    'moderator' => [12, 60],
    'staff' => [12, 60],
    'admin' => [20, 60]
  ],
  'changetags' => [
    'subnet' => [3, 60],
    'user-global' => [3, 60],
    'autoconfirmed' => [6, 60],
    'moderator' => [15, 60],
    'staff' => [20, 60],
    'admin' => [30, 60]
  ],
  'editcontentmodel' => [
    'subnet-all' => [1, 60],
    'user-global' => [1, 60],
    'moderator' => [5, 60],
    'staff' => [10, 20],
    'admin' => [20, 60]
  ]
]);
$wgRateLimits['rollback'] = $wgRateLimits['edit'];
$wgRateLimits['confirmemail'] = $wgRateLimits['changeemail'];

//<< Access >>

// This also counts failed attempts (e. g. CAPTCHA failure).
$wgAccountCreationThrottle = [
  [
    'count' => 3,
    // 1 day
    'seconds' => 60 * 60 * 24
  ]
];
$wgApplyIpBlocksToXff = true;
// 1 week
$wgAutoConfirmAge = 60 * 60 * 24 * 7;
$wgAutoConfirmCount = 15;
$wgAvailableRights = [
  'editprotected-user',
  'editprotected-autoconfirmed',
  'editprotected-moderator',
  'editprotected-staff',
  'editprotected-admin',
  'editprotected-steward'
];
$wgBlockCIDRLimit = $wmgCIDRLimit;
$wgCascadingRestrictionLevels = [
  'editprotected-staff',
  'editprotected-admin',
  'editprotected-steward'
];
$wgDeleteRevisionsLimit = 250;
$wgRestrictionLevels = [
  '',
  'editprotected-user',
  'editprotected-autoconfirmed',
  'editprotected-moderator',
  'editprotected-staff',
  'editprotected-admin',
  'editprotected-steward'
];
$wgRestrictionTypes[] = 'delete';
$wgSemiprotectedRestrictionLevels = [
  'editprotected-user',
  'editprotected-autoconfirmed'
];

if ($wmgGlobalAccountMode !== null) {
  $wgBotPasswordsDatabase = $wmgCentralDB;
}

//< Security >

$wgAllowUserCssPrefs = false;
$wgAllowUserCss = true;
$wgAllowUserJs = true;
$wgBreakFrames = true;
// Nonce support has been removed in MediaWiki 1.41: https://gerrit.wikimedia.org/r/c/mediawiki/core/+/945958
$wgCSPHeader = [
  'default-src' => ["$wmgCDNBaseURL/"],
  'includeCORS' => false
];
$wgRestAllowCrossOriginCookieAuth = true;

//< Cookies >

// This requires HTTPS.
$wgCookieSameSite = 'None';
// 2 months
$wgExtendedLoginCookieExpiration = 60 * 60 * 24 * 30 * 2;

if ($wmgGlobalAccountMode === 'shared-db' && str_starts_with($wmgDefaultDomain, '%wiki%.') && !isset($wmgCustomDomains[$wmgWiki])) {
  $wgCookieDomain = preg_replace('/^%wiki%\\./', '', $wmgDefaultDomain);
}

//< Profiling, testing and debugging >

//<< Debug >>

$wgDebugDumpSql = true;
$wgDebugLogGroups = [
  'csp' => "$wmgDataDirectory/private/per-wiki/$wmgWiki/debug-logs/csp-report.log",
  'ratelimit' => "$wmgDataDirectory/private/per-wiki/$wmgWiki/debug-logs/rate-limit.log",
  'throttler' => "$wmgDataDirectory/private/per-wiki/$wmgWiki/debug-logs/rate-limit.log"
];

if (PHP_SAPI === 'cli' || $wmgDebugLevel >= 1) {
  $wgShowExceptionDetails = true;
}

if ($wmgDebugLevel >= 2) {
  $wgDebugComments = true;
  $wgDebugLogFile = "$wmgDataDirectory/private/per-wiki/$wmgWiki/debug-logs/main.log";
  $wgDevelopmentWarnings = true;
  $wgShowDebug = true;
  $wgShowHostnames = true;
  $wgSpecialVersionShowHooks = true;
}

//< Search >

$wgSearchSuggestCacheExpiry = $wmgCacheExpiry;

//< Edit user interface >

if (PHP_OS_FAMILY === 'Windows') {
  $wgDiff = 'C:/Program Files (x86)/GnuWin32/bin/diff.exe';
  $wgDiff3 = 'C:/Program Files (x86)/GnuWin32/bin/diff3.exe';
}

//< Maintenance Scripts setting >

$wgGitBin = false;
$wgGitRepositoryViewers['https:\\/\\/github\\.com\\/([\\w\\-.]+\\/[\\w\\-.]+)\\.git'] = 'https://github.com/$1/commit/%H';

if (PHP_SAPI !== 'cli') {
  $wgReadOnlyFile = "$wmgDataDirectory/read-only.txt";
}

//< Recent changes, new pages, watchlist and history >

$wgDisableAnonTalk = true;
$wgRCWatchCategoryMembership = true;
// 1 week
$wgWatchersMaxAge = 60 * 60 * 24 * 7;
$wgWatchlistExpiry = true;

//<< Feed >>

$wgFeed = false;

//< Copyright >

$wgRightsIcon = "$wgScriptPath/resources/assets/licenses/cc-by-sa.png";
$wgRightsText = 'Creative Commons Attribution-ShareAlike 4.0 International';
$wgRightsUrl = 'https://creativecommons.org/licenses/by-sa/4.0/';

//< Import/Export >

$wgExportAllowListContributors = true;
$wgExportMaxHistory = 50;
$wgExportPagelistLimit = 20;
$wgImportSources = ['central'];

//< Logging >

// $wgLogHeaders
// $wgLogNames

//< Special pages >

$wgRangeContributionsCIDRLimit = $wmgCIDRLimit;

//< Robot policies >

$wgDefaultRobotPolicy = 'noindex, nofollow';
// All namespaces
$wgExemptFromUserRobotsControl = range(0, 15);

//< Ajax, Action API and REST API >

//<< API >>

$wgAPICacheHelpTimeout = $wmgCacheExpiry;

//< Shell and process control >

if (PHP_OS_FAMILY === 'Windows') {
  $wgPhpCli = 'C:/plavormind/php/php.cmd';
}

//< HTTP client >

$wgAsyncHTTPTimeout = 40;
$wgHTTPImportTimeout = 30;
$wgHTTPMaxTimeout = 40;
$wgHTTPTimeout = 30;

//< Miscellaneous settings >

$wgSkinsPreferred = ['vector-2022'];

//< Extension and skin usages >

if (is_file("$wmgDataDirectory/per-wiki/$wmgWiki/extra-usages.php")) {
  include_once "$wmgDataDirectory/per-wiki/$wmgWiki/extra-usages.php";
}

//< Extensions >

//<< AbuseFilter >>

if ($wmgUseExtensions['AbuseFilter']) {
  // This extension requires running update.php.
  wfLoadExtension('AbuseFilter');
  $wgAbuseFilterActionRestrictions = [
    'block' => false,
    'blockautopromote' => false,
    'degroup' => false,
    'disallow' => false,
    'rangeblock' => false,
    'tag' => false,
    'throttle' => false,
    'warn' => false
  ];
  $wgAbuseFilterActions = [
    'block' => false,
    'blockautopromote' => false,
    'degroup' => false,
    'disallow' => true,
    'rangeblock' => false,
    'tag' => false,
    'throttle' => false,
    'warn' => true
  ];
  // $wgAbuseFilterDefaultDisallowMessage
  // $wgAbuseFilterDefaultWarningMessage
  $wgAbuseFilterEmergencyDisableCount['default'] = 10;
  $wgAbuseFilterEmergencyDisableThreshold['default'] = 0.1;
  $wgAbuseFilterNotifications = 'rcandudp';

  $wgGroupPermissions = array_replace_recursive($wgGroupPermissions, [
    'bot' => [
      // 1.41+
      'abusefilter-bypass-blocked-external-domains' => false
    ],
    'suppress' => [
      'abusefilter-hidden-log' => false,
      'abusefilter-hide-log' => false
    ],
    'sysop' => [
      'abusefilter-log-detail' => false,
      'abusefilter-log-private' => false,
      'abusefilter-modify' => false,
      // 1.41+
      'abusefilter-modify-blocked-external-domains' => false,
      'abusefilter-modify-restricted' => false,
      'abusefilter-revert' => false,
      'abusefilter-view-private' => false
    ],
    'moderator' => [
      'abusefilter-log-detail' => true
    ],
    'staff' => [
      'abusefilter-modify' => true
    ],
    'admin' => [
      'abusefilter-log-detail' => true,
      'abusefilter-modify-restricted' => true
    ]
  ]);

  if ($wmgGlobalAccountMode === 'centralauth') {
    $wgAbuseFilterCentralDB = $wmgCentralDB;
  }
  else {
    $wgGroupPermissions['steward'] = array_merge($wgGroupPermissions['steward'], [
      'abusefilter-hidden-log' => true,
      'abusefilter-hide-log' => true,
      'abusefilter-modify' => true,
      'abusefilter-privatedetails' => true,
      'abusefilter-privatedetails-log' => true,
      'abusefilter-revert' => true
    ]);

    if (version_compare(MW_VERSION, '1.41', '>=')) {
      $wgGroupPermissions['steward']['abusefilter-bypass-blocked-external-domains'] = true;
      $wgGroupPermissions['steward']['abusefilter-modify-blocked-external-domains'] = true;
    }
  }
}

//<< AntiSpoof >>

if ($wmgUseExtensions['AntiSpoof']) {
  // This extension requires running update.php.
  wfLoadExtension('AntiSpoof');

  $wgGroupPermissions['bureaucrat']['override-antispoof'] = false;
  $wgGroupPermissions['sysop']['override-antispoof'] = false;

  if ($wmgGlobalAccountMode === 'shared-db') {
    $wgSharedTables[] = 'spoofuser';
  }
}

//<< Babel >>

if ($wmgUseExtensions['Babel']) {
  // This extension requires running update.php.
  wfLoadExtension('Babel');
  $wgBabelAllowOverride = false;
  // 1.41+
  $wgBabelAutoCreate = false;
  $wgBabelCategoryNames = [
    '0' => false,
    '1' => false,
    '2' => false,
    '3' => false,
    '4' => false,
    '5' => false,
    'N' => false
  ];
  $wgBabelMainCategory = false;
  $wgBabelUseUserLanguage = true;
}

//<< CategoryTree >>

if ($wmgUseExtensions['CategoryTree']) {
  wfLoadExtension('CategoryTree');
  $wgCategoryTreeAllowTag = false;
  $wgCategoryTreeDisableCache = $wmgCacheExpiry;
  $wgCategoryTreeDynamicTag = true;
  $wgCategoryTreeMaxChildren = 50;
  $wgCategoryTreeUseCache = true;
}

//<< CentralAuth >>

if ($wmgGlobalAccountMode === 'centralauth') {
  // This extension requires running update.php.
  wfLoadExtension('CentralAuth');
  $wgCentralAuthAutoMigrate = true;
  $wgCentralAuthAutoMigrateNonGlobalAccounts = true;
  $wgCentralAuthCookies = true;
  // Removed in MediaWiki 1.41
  $wgCentralAuthCreateOnView = true;
  $wgCentralAuthDatabase = 'wiki_centralauth';
  $wgCentralAuthGlobalBlockInterwikiPrefix = 'central';
  $wgCentralAuthGlobalPasswordPolicies['steward'] = $wgPasswordPolicy['policies']['steward'];
  $wgCentralAuthLoginWiki = $wmgCentralDB;
  $wgCentralAuthOldNameAntiSpoofWiki = $wmgCentralDB;
  // Removed in MediaWiki 1.41
  $wgCentralAuthPreventUnattached = true;
  $wgCentralAuthStrict = true;
  // Removed in MediaWiki 1.41
  $wgDisableUnmergedEditing = true;
  $wgGroupPermissions = array_replace_recursive($wgGroupPermissions, [
    'sysop' => [
      'centralauth-createlocal' => false
    ],
    '*' => [
      'centralauth-merge' => false
    ],
    'user' => [
      'centralauth-merge' => true
    ],
    'steward' => [
      'centralauth-createlocal' => false,
      'centralauth-lock' => false,
      'centralauth-suppress' => false,
      'centralauth-unmerge' => false
    ]
  ]);

  if (str_starts_with($wmgDefaultDomain, '%wiki%.') && !isset($wmgCustomDomains[$wmgWiki])) {
    $wgCentralAuthCookieDomain = preg_replace('/^%wiki%\\./', '', $wmgDefaultDomain);
  }
}

//<< CheckUser >>

if ($wmgUseExtensions['CheckUser']) {
  // This extension requires running update.php.
  wfLoadExtension('CheckUser');
  $wgCheckUserCIDRLimit = $wmgCIDRLimit;
  $wgCheckUserLogLogins = true;
  $wgCheckUserMaxBlocks = 10;

  $wgGroupPermissions['checkuser']['checkuser'] = false;
  $wgGroupPermissions['checkuser']['checkuser-log'] = false;

  if ($wmgGlobalAccountMode === 'centralauth') {
    $wgCheckUserCAMultiLock = [
      'centralDB' => $wmgCentralDB,
      'groups' => ['steward']
    ];
    $wgCheckUserCAtoollink = $wmgCentralDB;
    $wgCheckUserGBtoollink = [
      'centralDB' => $wmgCentralDB,
      'groups' => ['steward']
    ];
  }
  else {
    $wgGroupPermissions['steward']['checkuser'] = true;
    $wgGroupPermissions['steward']['checkuser-log'] = true;
    $wgGroupPermissions['steward']['checkuser-temporary-account'] = true;

    if (version_compare(MW_VERSION, '1.41', '>=')) {
      $wgGroupPermissions['steward']['checkuser-temporary-account-log'] = true;
    }
  }
}

//<< Cite >>

if ($wmgUseExtensions['Cite']) {
  wfLoadExtension('Cite');
  $wgCiteBookReferencing = true;
}

//<< CiteThisPage >>

if ($wmgUseExtensions['CiteThisPage']) {
  wfLoadExtension('CiteThisPage');
}

//<< CodeEditor >>

if ($wmgUseExtensions['CodeEditor'] && $wmgUseExtensions['WikiEditor']) {
  wfLoadExtension('CodeEditor');
}

//<< CodeMirror >>

if ($wmgUseExtensions['CodeMirror'] && ($wmgUseExtensions['VisualEditor'] || $wmgUseExtensions['WikiEditor'])) {
  wfLoadExtension('CodeMirror');
}

//<< CommonsMetadata >>

if ($wmgUseExtensions['CommonsMetadata']) {
  wfLoadExtension('CommonsMetadata');
}

//<< ConfirmEdit >>

if ($wmgUseExtensions['ConfirmEdit']) {
  wfLoadExtensions(['ConfirmEdit', 'ConfirmEdit/hCaptcha']);
  // This completely blocks API login until the expiration.
  // 10 minutes
  $wgCaptchaBadLoginExpiration = 60 * 10;
  $wgCaptchaBadLoginPerUserAttempts = 10;
  // This completely blocks API login until the expiration.
  // 1 day
  $wgCaptchaBadLoginPerUserExpiration = 60 * 60 * 24;
  $wgCaptchaTriggers['create'] = true;
  $wgCaptchaTriggers['sendemail'] = true;
  $wgCaptchaTriggersOnNamespace = [
    NS_USER => [
      'create' => false
    ]
  ];

  $wgGroupPermissions['bot']['skipcaptcha'] = false;
  $wgGroupPermissions['sysop']['skipcaptcha'] = false;
  $wgGroupPermissions['autoconfirmed']['skipcaptcha'] = true;
  $wgGroupPermissions['staff']['skipcaptcha'] = true;

  if ($wmgGlobalAccountMode !== 'centralauth') {
    $wgGroupPermissions['steward']['skipcaptcha'] = true;
  }
}

//<< DiscussionTools >>

if ($wmgUseExtensions['DiscussionTools'] && $wmgUseExtensions['Linter'] && $wmgUseExtensions['VisualEditor']) {
  // This extension requires running update.php.
  wfLoadExtension('DiscussionTools');
}

//<< Echo >>

if ($wmgUseExtensions['Echo']) {
  // This extension requires running update.php.
  wfLoadExtension('Echo');
  $wgDefaultNotifyTypeAvailability = [
    'email' => false,
    'web' => true
  ];
  $wgEchoMaxMentionsCount = 10;
  $wgEchoMaxMentionsInEditSummary = 10;
  // This is also the number of maximum notifications for a single user to have.
  $wgEchoMaxUpdateCount = 100;
  $wgEchoMentionStatusNotifications = true;
  // $wgEchoNotificationIcons
  $wgEchoOnWikiBlacklist = null;
  $wgEchoPerUserWhitelistFormat = null;
  $wgEchoPollForUpdates = 30;
  $wgEchoWatchlistNotifications = true;
  $wgNotifyTypeAvailabilityByCategory = [
    'edit-user-talk' => [
      'email' => true,
      'web' => true
    ],
    // LoginNotify
    'login-fail' => [
      'email' => true,
      'web' => true
    ],
    'mention' => [
      'email' => true,
      'web' => true
    ],
    'user-rights' => [
      'email' => true,
      'web' => true
    ]
  ];

  $wgGroupPermissions['push-subscription-manager']['manage-all-push-subscriptions'] = false;

  $wgDefaultUserOptions = array_merge($wgDefaultUserOptions, [
    'echo-email-frequency' => -1,
    'echo-show-poll-updates' => 1,
    'echo-subscriptions-email-user-rights' => 0,
    'echo-subscriptions-web-edit-user-page' => 0,
    'echo-subscriptions-web-mention-failure' => 1,
    'echo-subscriptions-web-thank-you-edit' => 0
  ]);

  if ($wmgGlobalAccountMode !== null) {
    $wgDefaultUserOptions['echo-cross-wiki-notifications'] = 1;
    $wgEchoCrossWikiNotifications = true;
    $wgEchoSharedTrackingDB = $wmgCentralDB;
  }

  if ($wmgGlobalAccountMode !== 'centralauth') {
    $wgGroupPermissions['steward']['manage-all-push-subscriptions'] = true;
  }
}

//<< GlobalBlocking >>

if ($wmgGlobalAccountMode === 'shared-db') {
  $wgGroupPermissions['steward']['globalblock-exempt'] = true;
  $wgGroupPermissions['steward']['globalblock-whitelist'] = true;
}

if ($wmgGlobalAccountMode !== null) {
  // This extension requires running update.php.
  wfLoadExtension('GlobalBlocking');
  $wgGlobalBlockingCIDRLimit = $wmgCIDRLimit;
  // Removed in MediaWiki 1.42
  $wgGlobalBlockingDatabase = $wmgCentralDB;
  $wgGlobalBlockRemoteReasonUrl = "{$wmgCentralBaseURL}{$wgScriptPath}/api.php";
  $wgGroupPermissions['sysop']['globalblock-whitelist'] = false;
  $wgGroupPermissions['steward']['globalblock'] = false;
  // 1.42+
  $wgVirtualDomainsMapping['virtual-globalblocking']['db'] = $wmgCentralDB;
}

//<< GlobalCssJs >>

if ($wmgUseExtensions['GlobalCssJs'] && $wmgGlobalAccountMode !== null) {
  wfLoadExtension('GlobalCssJs');
  $wgGlobalCssJsConfig = [
    'source' => 'central',
    'wiki' => $wmgCentralDB
  ];

  $wgResourceLoaderSources['central'] = [
    'apiScript' => "{$wmgCentralBaseURL}{$wgScriptPath}/api.php",
    'loadScript' => "{$wmgCentralBaseURL}{$wgScriptPath}/load.php"
  ];
}

//<< GlobalPreferences >>

if ($wmgGlobalAccountMode === 'centralauth') {
  $wgGlobalPreferencesDB = $wmgCentralDB;
}

if ($wmgGlobalAccountMode !== null) {
  // This extension requires running update.php.
  wfLoadExtension('GlobalPreferences');
}

//<< GlobalUserPage >>

if ($wmgUseExtensions['GlobalUserPage'] && $wmgGlobalAccountMode !== null) {
  wfLoadExtension('GlobalUserPage');
  $wgGlobalUserPageAPIUrl = "{$wmgCentralBaseURL}{$wgScriptPath}/api.php";
  $wgGlobalUserPageCacheExpiry = $wmgCacheExpiry;
  $wgGlobalUserPageDBname = $wmgCentralDB;
  // $wgGlobalUserPageFooterKey
  $wgGlobalUserPageTimeout = 'default';
}

//<< InputBox >>

if ($wmgUseExtensions['InputBox']) {
  wfLoadExtension('InputBox');
}

//<< Interwiki >>

if ($wmgUseExtensions['Interwiki']) {
  wfLoadExtension('Interwiki');

  $wgGroupPermissions['admin']['interwiki'] = true;

  if ($wmgGlobalAccountMode !== null) {
    $wgInterwikiCentralDB = $wmgCentralDB;
  }
}

//<< Josa >>

if ($wmgUseExtensions['Josa']) {
  wfLoadExtension('Josa');
}

//<< Linter >>

if ($wmgUseExtensions['Linter']) {
  // This extension requires running update.php.
  wfLoadExtension('Linter');

  $wgParsoidSettings['linting'] = true;
}

//<< LoginNotify >>

if ($wmgUseExtensions['LoginNotify'] && $wmgUseExtensions['Echo']) {
  wfLoadExtension('LoginNotify');
  // Default value specified in "Extension:LoginNotify" page on MediaWiki.org does not match the actual default value.
  $wgLoginNotifyAttemptsNewIP = 3;
  // 2 week
  $wgLoginNotifyCacheLoginIPExpiry = 60 * 60 * 24 * 7 * 2;
  $wgLoginNotifyEnableForPriv = [];
  // 3 days
  $wgLoginNotifyExpiryKnownIP = 60 * 60 * 24 * 3;
  // 1 week
  $wgLoginNotifyExpiryNewIP = 60 * 60 * 24 * 7;

  $wgDefaultUserOptions['echo-subscriptions-email-login-fail'] = 0;
  $wgDefaultUserOptions['echo-subscriptions-email-login-success'] = 0;

  if ($wmgGlobalAccountMode !== null && str_starts_with($wmgDefaultDomain, '%wiki%.') && !isset($wmgCustomDomains[$wmgWiki])) {
    $wgLoginNotifyCookieDomain = preg_replace('/^%wiki%\\./', '', $wmgDefaultDomain);
  }
}

//<< Math >>

if ($wmgUseExtensions['Math']) {
  // This extension requires running update.php.
  wfLoadExtension('Math');
  $wgMathEnableWikibaseDataType = false;
  // Merge strategy of this is array_merge.
  $wgMathValidModes = ['native'];

  $wgDefaultUserOptions['math'] = 'native';
}

//<< MultimediaViewer >>

if ($wmgUseExtensions['MultimediaViewer']) {
  wfLoadExtension('MultimediaViewer');
}

//<< Nuke >>

if ($wmgUseExtensions['Nuke']) {
  wfLoadExtension('Nuke');

  $wgGroupPermissions['sysop']['nuke'] = false;
  $wgGroupPermissions['staff']['nuke'] = true;

  if ($wmgGlobalAccountMode !== 'centralauth') {
    $wgGroupPermissions['steward']['nuke'] = true;
  }
}

//<< OATHAuth >>

if ($wmgUseExtensions['OATHAuth']) {
  wfLoadExtension('OATHAuth');
  $wgOATHRequiredForGroups = ['steward'];

  $wgGroupPermissions['sysop']['oathauth-disable-for-user'] = false;
  $wgGroupPermissions['sysop']['oathauth-verify-user'] = false;
  $wgGroupPermissions['sysop']['oathauth-view-log'] = false;

  if ($wmgGlobalAccountMode !== null) {
    $wgOATHAuthAccountPrefix = 'PlavorMind wikis';
    // This setting is superseded by $wgVirtualDomainsMapping and deprecated in MediaWiki 1.42.
    $wgOATHAuthDatabase = $wmgCentralDB;
    // 1.42+
    $wgVirtualDomainsMapping['virtual-oathauth']['db'] = $wmgCentralDB;
  }

  if ($wmgGlobalAccountMode !== 'centralauth') {
    $wgGroupPermissions['steward']['oathauth-api-all'] = true;
    $wgGroupPermissions['steward']['oathauth-view-log'] = true;
  }
}

//<< PageImages >>

if ($wmgUseExtensions['PageImages']) {
  wfLoadExtension('PageImages');
  $wgPageImagesDenylistExpiry = $wmgCacheExpiry;
  $wgPageImagesExpandOpenSearchXml = true;
  $wgPageImagesNamespaces = [NS_HELP, NS_MAIN, NS_PROJECT, NS_USER];
}

//<< ParserFunctions >>

if ($wmgUseExtensions['ParserFunctions']) {
  wfLoadExtension('ParserFunctions');
  $wgPFEnableStringFunctions = true;
}

//<< Parsoid >>

if ($wmgUseExtensions['Parsoid']) {
  wfLoadExtension('Parsoid', "$IP/vendor/wikimedia/parsoid/extension.json");
  /*
  1.41+
  Removed in MediaWiki 1.42
  */
  $wgParsoidEnableQueryString = true;
}

//<< PlavorMindTools >>

if ($wmgUseExtensions['PlavorMindTools']) {
  wfLoadExtension('PlavorMindTools');
  $wgCUGCentralAuthHierarchies['steward'] = 4;
  $wgCUGDisableGroups = [
    'bot',
    'bureaucrat',
    'checkuser',
    // 1.41+
    'checkuser-temporary-account-viewer',
    'push-subscription-manager',
    'steward',
    'suppress',
    'sysop'
  ];
  $wgCUGEnable = true;
  $wgCUGHierarchies = [
    'moderator' => 1,
    'staff' => 2,
    'admin' => 3,
    'steward' => 4
  ];
  $wgRIMEnable = true;
  $wgRIMEnglishSystemUsers = true;
  $wgRIMPlavorMindSpecificMessages = true;
  $wgUHHCSPs['enforced'] = "default-src 'none'; img-src $wgServer/favicon.ico; sandbox; style-src 'unsafe-inline';";
  $wgUHHEnable = true;
  $wgUPAEnable = true;

  $wgGroupPermissions['moderator']['edit-other-user-pages'] = true;
  $wgGroupPermissions['staff']['move-user-namespace'] = true;
  $wgGroupPermissions['admin']['edit-other-user-pages'] = true;
  $wgRecentChangesFlags['minor']['letter'] = 'pmt-rc-flag-minor-edit';
  $wgRecentChangesFlags['newpage']['letter'] = 'pmt-rc-flag-new-page';

  if ($wmgGlobalAccountMode !== 'centralauth') {
    $wgGroupPermissions['steward']['move-user-namespace'] = true;
  }
}

//<< Poem >>

if ($wmgUseExtensions['Poem']) {
  wfLoadExtension('Poem');
}

//<< Popups >>

if ($wmgUseExtensions['Popups'] && $wmgUseExtensions['PageImages'] && $wmgUseExtensions['TextExtracts']) {
  wfLoadExtension('Popups');
  $wgPopupsHideOptInOnPreferencesPage = true;
  $wgPopupsReferencePreviewsBetaFeature = false;
}

//<< QuickInstantCommons >>

if ($wmgUseExtensions['QuickInstantCommons']) {
  wfLoadExtension('QuickInstantCommons');
  $wgQuickInstantCommonsPrefetchMaxLimit = 100;
}

//<< ReplaceText >>

if ($wmgUseExtensions['ReplaceText']) {
  wfLoadExtension('ReplaceText');
  $wgReplaceTextResultsLimit = 100;

  $wgGroupPermissions['sysop']['replacetext'] = false;

  if ($wmgGlobalAccountMode !== 'centralauth') {
    $wgGroupPermissions['steward']['replacetext'] = true;
  }
}

//<< RevisionSlider >>

if ($wmgUseExtensions['RevisionSlider']) {
  wfLoadExtension('RevisionSlider');
}

//<< Scribunto >>

if ($wmgUseExtensions['Scribunto']) {
  wfLoadExtension('Scribunto');
}

//<< SecureLinkFixer >>

if ($wmgUseExtensions['SecureLinkFixer']) {
  wfLoadExtension('SecureLinkFixer');
}

//<< SpamBlacklist >>

if ($wmgUseExtensions['SpamBlacklist']) {
  wfLoadExtension('SpamBlacklist');
  $wgBlacklistSettings['spam']['files'] = [];
  $wgLogSpamBlacklistHits = true;

  // 1.41+
  $wgGroupPermissions['bot']['sboverride'] = false;
  $wgGroupPermissions['user']['spamblacklistlog'] = false;
  $wgRawHtmlMessages[] = 'email-blacklist';
  $wgRawHtmlMessages[] = 'email-whitelist';
  $wgRawHtmlMessages[] = 'spam-blacklist';
  $wgRawHtmlMessages[] = 'spam-whitelist';

  if ($wmgGlobalAccountMode !== 'centralauth') {
    $wgGroupPermissions['steward']['sboverride'] = true;
    $wgGroupPermissions['steward']['spamblacklistlog'] = true;
  }
}

//<< SyntaxHighlight_GeSHi >>

if ($wmgUseExtensions['SyntaxHighlight_GeSHi']) {
  wfLoadExtension('SyntaxHighlight_GeSHi');

  if (PHP_OS_FAMILY === 'Windows') {
    $wgPygmentizePath = 'C:/Program Files/Python311/Scripts/pygmentize.exe';
  }
}

//<< TemplateData >>

if ($wmgUseExtensions['TemplateData']) {
  wfLoadExtension('TemplateData');
}

//<< TemplateSandbox >>

if ($wmgUseExtensions['TemplateSandbox']) {
  wfLoadExtension('TemplateSandbox');
}

//<< TemplateStyles >>

if ($wmgUseExtensions['TemplateStyles']) {
  wfLoadExtension('TemplateStyles');
  $wgTemplateStylesAllowedUrls = [];
}

//<< TemplateWizard >>

if ($wmgUseExtensions['TemplateWizard'] && $wmgUseExtensions['TemplateData'] && $wmgUseExtensions['WikiEditor']) {
  wfLoadExtension('TemplateWizard');
}

//<< TextExtracts >>

if ($wmgUseExtensions['TextExtracts']) {
  wfLoadExtension('TextExtracts');
  $wgExtractsExtendOpenSearchXml = true;
}

//<< Thanks >>

if ($wmgUseExtensions['Thanks'] && $wmgUseExtensions['Echo']) {
  wfLoadExtension('Thanks');
}

//<< TitleBlacklist >>

if ($wmgUseExtensions['TitleBlacklist']) {
  wfLoadExtension('TitleBlacklist');
  $wgTitleBlacklistCaching['expiry'] = $wmgCacheExpiry;
  $wgTitleBlacklistCaching['warningexpiry'] = $wmgCacheExpiry;
  $wgTitleBlacklistLogHits = true;
  $wgTitleBlacklistSources = [
    'global' => [
      'src' => "$wmgDataDirectory/private/title-blacklist.txt",
      'type' => 'file'
    ]
  ];

  $wgGroupPermissions['sysop']['tboverride'] = false;
  $wgGroupPermissions['sysop']['titleblacklistlog'] = false;
  $wgRawHtmlMessages[] = 'titleblacklist';
  $wgRawHtmlMessages[] = 'titlewhitelist';

  if ($wmgGlobalAccountMode !== null) {
    $wgTitleBlacklistUsernameSources = ['global'];
  }

  if ($wmgGlobalAccountMode !== 'centralauth') {
    $wgGroupPermissions['steward']['tboverride'] = true;
    $wgGroupPermissions['steward']['titleblacklistlog'] = true;
  }
}

//<< TwoColConflict >>

if ($wmgUseExtensions['TwoColConflict']) {
  wfLoadExtension('TwoColConflict');
  $wgTwoColConflictBetaFeature = false;
}

//<< UniversalLanguageSelector >>

if ($wmgUseExtensions['UniversalLanguageSelector']) {
  wfLoadExtension('UniversalLanguageSelector');
  $wgULSIMEEnabled = false;
  $wgULSLanguageDetection = false;
  $wgULSWebfontsEnabled = false;
}

//<< UploadsLink >>

if ($wmgUseExtensions['UploadsLink']) {
  wfLoadExtension('UploadsLink');
}

//<< VisualEditor >>

if ($wmgUseExtensions['VisualEditor']) {
  wfLoadExtension('VisualEditor');
  $wgVisualEditorAvailableNamespaces['Help'] = true;
  $wgVisualEditorAvailableNamespaces['Project'] = true;
  $wgVisualEditorEnableWikitext = true;
  $wgVisualEditorShowBetaWelcome = false;
  $wgVisualEditorUseSingleEditTab = true;

  $wgDefaultUserOptions['visualeditor-editor'] = 'wikitext';
  $wgDefaultUserOptions['visualeditor-newwikitext'] = 1;
  $wgHiddenPrefs[] = 'visualeditor-betatempdisable';
}

//<< WikiEditor >>

if ($wmgUseExtensions['WikiEditor']) {
  wfLoadExtension('WikiEditor');
  // This is same as default in MediaWiki 1.41 or newer.
  $wgWikiEditorRealtimePreview = true;
}

//< Skins >

//<< MinervaNeue >>

if ($wmgUseSkins['MinervaNeue']) {
  wfLoadSkin('MinervaNeue');
  $wgMinervaAdvancedMainMenu['base'] = true;
  $wgMinervaAlwaysShowLanguageButton = false;
  $wgMinervaHistoryInPageActions['base'] = true;
  $wgMinervaOverflowInPageActions['base'] = true;
  $wgMinervaPersonalMenu['base'] = true;
  $wgMinervaShowCategories['base'] = true;
  $wgMinervaTalkAtTop['base'] = true;
}

//<< Timeless >>

if ($wmgUseSkins['Timeless']) {
  wfLoadSkin('Timeless');
}

//<< Vector >>

wfLoadSkin('Vector');
// Removed in MediaWiki 1.41
$wgVectorDefaultSidebarVisibleForAnonymousUser = true;
$wgVectorLanguageInHeader = [
  'logged_in' => false,
  'logged_out' => false
];
$wgVectorMaxWidthOptions['exclude'] = [];
// Removed in MediaWiki 1.41
$wgVectorPageTools['logged_out'] = true;
$wgVectorResponsive = true;
$wgVectorShareUserScripts = false;
$wgVectorStickyHeader['logged_out'] = true;

$wgDefaultUserOptions['vector-limited-width'] = 0;

//< Load other settings >

if (($wmgWiki === $wmgCentralWiki || $wmgGlobalAccountMode === null) && is_file("$wmgDataDirectory/central-settings.php")) {
  include_once "$wmgDataDirectory/central-settings.php";
}

if (is_file("$wmgDataDirectory/per-wiki/$wmgWiki/settings.php")) {
  include_once "$wmgDataDirectory/per-wiki/$wmgWiki/settings.php";
}

require_once "$wmgDataDirectory/private/private-settings.php";
