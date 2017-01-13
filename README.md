# PowerDNS API 

[![StyleCI](https://styleci.io/repos/47706537/shield?branch=master)](https://styleci.io/repos/47706537)
[![Codacy](https://img.shields.io/codacy/grade/32143961293d4ae3b25dd9e702f69c65/master.svg?style=flat-square)](https://www.codacy.com/app/yosoy/Advandz-Framework?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Advandz/Advandz-Framework&amp;utm_campaign=Badge_Grade)

An easy to implement HTTP API for PowerDNS, Simple and Lightweight.

The API only needs PHP 5.4+ and MySQL.

Upload the files to "/var/www/html/api/" and edit the "config.php" with your PowerDNS MySQL Details.

# API Documentation

Function                    | Description                 | Parameters
--------------------------- | --------------------------- | ---------------------------
add_domain                  | Add a domain to PowerDNS    | <b>domain</b> <i>string</i> A valid domain.<br> <b>solusvm_cid</b> <i>integer</i> (Optional) SolusVM Client ID.<br>
add_record                  | Add a Record to PowerDNS    | <b>domain_id</b> <i>integer</i> Domain ID.<br> <b>name</b> <i>string</i> Record Name.<br> <b>type</b> <i>string</i> Record Type.<br> <b>content</b> <i>string</i> Record Content.<br> <b>ttl</b> <i>integer</i> Record TTL.<br> <b>prio</b> <i>integer</i> Record Priority (Only for MX and SRV).<br>
get_domain_id               | Get the ID of a Domain      | <b>domain</b> <i>string</i> A valid domain.<br> 
get_domains_by_solusvmid    | Get domains by SolusVM ID   | <b>solusvm_cid</b> <i>integer</i> SolusVM Client ID.<br>
get_records_by_domain_id    | Get the records of a domain | <b>domain_id</b> <i>integer</i> Domain ID.<br>
delete_record               | Delete a Record of PowerDNS | <b>record_id</b> <i>integer</i> Record ID.<br>
delete_domain               | Delete a domain             | <b>domain_id</b> <i>integer</i> Domain ID.<br>

# Using API 

All the parameter are sent using GET to the API.

<b>Authentication</b>
<pre>http://ns1.yourserver.com/api/?key=Your-API-Key</pre>
<i>Response:</i>
<pre>{"status":"error","msg":"Action not defined."}</pre>
<hr>
<b>Using Functions</b>
<pre>http://ns1.yourserver.com/api/?key=Your-API-Key&action=Action&param1=xx....</pre>
<i>Example</i>
<pre>http://ns1.yourserver.com/api/?key=Your-API-Key&action=add_record&domain_id=12&name=demo.com&type=NS&content=google.com</pre>
<i>Response:</i>
<pre>{"status":"success","msg":"New record created successfully!"}</pre>

# Installing API
For install the API, only run this command over SSH.
<pre>
$ cd /var/www/html
$ wget https://github.com/CyanDarkInc/PowerDNS-API/archive/master.zip
$ unzip master.zip
$ mv PowerDNS-API/src api
$ rm -f master.zip
</pre>

Now you can access to the API from http://Your-PowerDNS-Server-IP/api/