<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width">
  <meta name="theme-color" content="#000" />
  <meta name="mobile-web-app-capable" content="yes">
  <link rel="shortcut icon" href="favicon.png" type="image/x-icon" />
  <link rel="icon" type="image/png" sizes="192x192"  href="logo.svg">
  <link rel="icon" type="image/png" sizes="96x96" href="logo.svg">
  <title>Pi-hole® A black hole for Internet advertisements</title>
  <link href="style/vendor/ionicons-2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link href="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.css" rel="stylesheet">
  <link href="style/vendor/AdminLTE.min.css" rel="stylesheet" type="text/css" />
  <link href="style/vendor/skin-blue.min.css" rel="stylesheet" type="text/css" />
  <link href="style/pi-hole.css" rel="stylesheet" type="text/css" />
  <link href="style/custom.css" rel="stylesheet" type="text/css" />

  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.js"></script>
</head>
  <style type="text/css">
  body {
    background-color: #333;
    color: #fefefe;
  }
  </style>
  <body>

    <div class="container">
      <h1 class="text-center">Pi-holes</h1>

      <div class="row">
        <div class="col-sm-6">
          <a href="https://ns2.sunderedheart.woolweaver.bid" target="_blank" class="btn btn-link" id="admin-link-0">ns2</a>
          <table id="table1" class="keyval-table table table-condensed">
            <thead>
              <tr>
                <th data-field="key"></th>
                <th data-field="val"></th>
              </tr>
            </thead>
          </table>
        </div>

        <div class="col-sm-6">
          <a href="https://ns3.sunderedheart.woolweaver.bid" target="_blank" class="btn btn-link" id="admin-link-1">ns3</a>
          <table id="table2" class="keyval-table table table-condensed">
            <thead>
              <tr>
                <th data-field="key"></th>
                <th data-field="val"></th>
              </tr>
            </thead>
          </table>
        </div>

      </div>

    </div>

    <script>
      const REFRESH = 2000
  
      $(document).ready(() => {
  
        const $unique_clients        = $('#unique_clients'),
              $dns_queries_today     = $('#dns_queries_today'),
              $ads_blocked_today     = $('#ads_blocked_today'),
              $ads_percentage_today  = $('#ads_percentage_today'),
              $domains_being_blocked_pi1 = $('#domains_being_blocked_pi1'),
              $domains_being_blocked_pi2 = $('#domains_being_blocked_pi2'),
              
              $table1 = $('#table1'),
              $table2 = $('#table2'),
              
              keymap = {
                'domains_being_blocked': 'Domains',
                'dns_queries_today': 'Queries',
                'ads_blocked_today': 'Blocked',
                'ads_percentage_today': 'Percent',
                'unique_domains': 'Unique Domains',
                'queries_forwarded': 'Forwarded Queries',
                'queries_cached': "Cached Queries",
                'clients_ever_seen': null,
                'dns_queries_all_types': null,
                'unique_clients': 'Clients',
                'reply_NODATA': null,
                'reply_NXDOMAIN': null,
                'reply_CNAME': null,
                'reply_IP': null,
                'privacy_level': null,
                'status': 'Status'
              };
        
        const endpoints = [
          {},
          {}
        ]
  
        const summarypoints = [
          './data/summary-1.json',
          './data/summary-2.json'
        ]
  
        const statusAction = 'status'
  
        function requestInterval(fn, delay) {
          let start = new Date().getTime(),
              handle = {}
          function loop() {
            handle.value = requestAnimationFrame(loop)
            const current = new Date().getTime(),
            delta = current - start
            if (delta >= delay) {
              fn.call()
              start = new Date().getTime()
            }
          }
          handle.value = requestAnimationFrame(loop)
          return handle
        }
  
        function updateStatus(host) {
          requestInterval(() => {
            getData()
          }, REFRESH)
        }
  
        async function getData() {
  
          pi1data = await fetch(`${summarypoints[0]}`)
          pi2data = await fetch(`${summarypoints[1]}`)
          pi1sum = await pi1data.json()
          pi2sum = await pi2data.json()
  
          let clients = cleanNumber(pi1sum.unique_clients).toLocaleString() + ' / ' + cleanNumber(pi2sum.unique_clients).toLocaleString()
          let dns = cleanNumber(pi1sum.dns_queries_today) + cleanNumber(pi2sum.dns_queries_today)
          let blocked = cleanNumber(pi1sum.ads_blocked_today) + cleanNumber(pi2sum.ads_blocked_today)
  
          let perc = ((cleanNumber(pi1sum.ads_blocked_today)  + cleanNumber(pi2sum.ads_blocked_today)) / (cleanNumber(pi1sum.dns_queries_today)  + cleanNumber(pi2sum.dns_queries_today))) * 100
  
          let domainsPI1 = cleanNumber(pi1sum.domains_being_blocked).toLocaleString()

          let domainsPI2 =  cleanNumber(pi2sum.domains_being_blocked).toLocaleString()
  
          $unique_clients.text(clients)
          $dns_queries_today.text(dns.toLocaleString())
          $ads_blocked_today.text(blocked.toLocaleString())
          $ads_percentage_today.text(Math.round(perc*10)/10 + "%")
          $domains_being_blocked_pi1.text(domainsPI1)
          $domains_being_blocked_pi2.text(domainsPI2)
  
          $table1.bootstrapTable('load', summaryToKeyVal(pi1sum))
          $table2.bootstrapTable('load', summaryToKeyVal(pi2sum))
  
        }
  
        function cleanNumber(n) {
          return parseFloat(n.replace(',',''))
        }
  
        function summaryToKeyVal(data) {
  
          let ret = []
  
          for (let i in data) {
  
            if (i in keymap && keymap[i] !== null) {
  
              ret.push({
                key: keymap[i],
                val: data[i]
              })
  
            }
  
          }
  
          return ret
        }
  
        $table1.bootstrapTable({data: []})
        $table2.bootstrapTable({data: []})

        function parseKeyVal() {
            endpoints[0].ip   = "<?php echo getenv('PI_ONE') ?>"
            endpoints[1].ip   = "<?php echo getenv('PI_TWO') ?>"
            endpoints[0].auth = "<?php echo getenv('PI_ONE_AUTH') ?>"
            endpoints[1].auth = "<?php echo getenv('PI_TWO_AUTH') ?>"
        }
        
        console.log(endpoints[0])
        parseKeyVal()
        getData()
        updateStatus()
      })
    </script>
  </body>
</html>