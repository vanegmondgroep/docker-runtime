serverName                container.vanegmond.io
user                      10000
group                     10000
priority                  0
inMemBufSize              60M
swappingDir               /tmp/lshttpd/swap
autoFix503                1
gracefulRestartTimeout    300
mime                      conf/mime.properties
showVersionNumber         0
useIpInProxyHeader        1
adminEmails               support@vanegmond.io

errorlog /usr/local/lsws/logs/error.log {
  logLevel                WARN
  debugLevel              0
  rollingSize             10M
  enableStderrLog         0
}

accesslog /usr/local/lsws/logs/access.log {
  rollingSize             0M
  keepDays                0
  compressArchive         0
}

indexFiles                index.php, index.html

expires  {
  enableExpires           1
  expiresByType           image/*=A604800,text/css=A604800,application/x-javascript=A604800,application/javascript=A604800,font/*=A604800,application/x-font-ttf=A604800
}

tuning  {
  maxConnections          10000
  maxSSLConnections       10000
  connTimeout             300
  maxKeepAliveReq         10000
  keepAliveTimeout        5
  sndBufSize              0
  rcvBufSize              0
  maxReqURLLen            32768
  maxReqHeaderSize        65536
  maxReqBodySize          2047M
  maxDynRespHeaderSize    32768
  maxDynRespSize          2047M
  maxCachedFileSize       4096
  totalInMemCacheSize     20M
  maxMMapFileSize         256K
  totalMMapCacheSize      40M
  useSendfile             1
  fileETag                28
  enableGzipCompress      1
  compressibleTypes       default
  enableDynGzipCompress   1
  gzipCompressLevel       6
  gzipAutoUpdateStatic    1
  gzipStaticCompressLevel 6
  brStaticCompressLevel   6
  gzipMaxFileSize         10M
  gzipMinFileSize         300

  quicEnable              1
  quicShmDir              /dev/shm
}

fileAccessControl  {
  followSymbolLink        1
  checkSymbolLink         0
  requiredPermissionMask  000
  restrictedPermissionMask 000
}

perClientConnLimit  {
  staticReqPerSec         0
  dynReqPerSec            0
  outBandwidth            0
  inBandwidth             0
  softLimit               10000
  hardLimit               10000
  gracePeriod             15
  banPeriod               300
}

CGIRLimit  {
  maxCGIInstances         20
  minUID                  11
  minGID                  10
  priority                0
  CPUSoftLimit            10
  CPUHardLimit            50
  memSoftLimit            1460M
  memHardLimit            1470M
  procSoftLimit           400
  procHardLimit           450
}

accessDenyDir  {
  dir                     /
  dir                     /etc/*
  dir                     /dev/*
  dir                     conf/*
  dir                     admin/conf/*
}

accessControl  {
  allow                   ALL
}

extprocessor lsphp {
  type                    lsapi
  address                 uds://tmp/lshttpd/lsphp.sock
  initTimeout             60
  retryTimeout            0
  persistConn             1
  respBuffer              0
  autoStart               1
  path                    lsphp74/bin/lsphp
  backlog                 100
  instances               1
  priority                0
  memSoftLimit            2047M
  memHardLimit            2047M
  procSoftLimit           1400
  procHardLimit           1500
}

extprocessor webhooks {
  type                    proxy
  address                 http://127.0.0.1:9000
  maxConns                50
  initTimeout             60
  retryTimeout            0
  respBuffer              0
}

scripthandler  {
  add                     lsapi:lsphp php
}

module cache {
  internal            1
  checkPrivateCache   1
  checkPublicCache    1
  maxCacheObjSize     10000000
  maxStaleAge         200
  qsCache             1
  reqCookieCache      1
  respCookieCache     1
  ignoreReqCacheCtrl  1
  ignoreRespCacheCtrl 0
  enableCache         0
  expireInSeconds     3600
  enablePrivateCache  0
  privateExpireInSeconds 3600
  ls_enabled              1
}

virtualhost default {
  vhRoot                  /opt/vanegmond/app
  configFile              /opt/vanegmond/etc/litespeed/vhconf.conf
  allowSymbolLink         1
  enableScript            1
  restrained              1
}

listener http {
  address                 *:8080
  secure                  0
  map                     default *
}

listener https {
  address                 *:8443
  secure                  1
  keyFile                 $SERVER_ROOT/admin/conf/webadmin.key
  certFile                $SERVER_ROOT/admin/conf/webadmin.crt
  map                     default *
}