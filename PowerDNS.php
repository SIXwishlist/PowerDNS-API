<?php
//  CyanDark Incorporated
//  Copyright (c) 2012-2016 CyanDark, Inc. All Rights Reserved.
//
//  This software is furnished under a license and may be used and copied
//  only  in  accordance  with  the  terms  of such  license and with the
//  inclusion of the above copyright notice.  This software  or any other
//  copies thereof may not be provided or otherwise made available to any
//  other person.  No title to and  ownership of the  software is  hereby
//  transferred.
//
//  You may not reverse  engineer, decompile, defeat  license  encryption
//  mechanisms, or  disassemble this software product or software product
//  license. CyanDark may terminate this license if you don't comply with
//  any of the  terms  and conditions  set  forth in our end user license
//  agreement (EULA).  In such event, licensee  agrees to return licensor
//  or  destroy all copies  of  software  upon termination of the license

class PowerDNS{
	protected $server;
	protected $userName;
	protected $password;
	protected $dbName;
	protected $dbPort;
	
	/**
	 * Constructor
	 *
	 * @param string $UserName The PowerDNS MySQL username
	 * @param string $Password The password of the database
	 * @param string $DbName The PowerDNS database name
	 * @param string $DbPort The port used for MySQL
	 * @return stdClass The PowerDNS class to connect to DB
	 */	
	function __construct($Server, $UserName, $Password, $DbName='powerdns', $DbPort=3306){
		$this->userName = $UserName;
		$this->password = $Password;
		$this->dbName = $DbName;
		$this->dbPort = $DbPort;
	}
	
	/**
	 * Is a Valid Domain?
	 *
	 * @param string $domain The domain for verify
	 * @return boolean Result of the operation
	 */	
	function isValidDomain($domain){
		return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain) //valid chars check
				&& preg_match("/^.{1,253}$/", $domain) //overall length check
				&& preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain)); //length of each label
	}
	
	/**
	 * Add Domain
	 *
	 * @param string $domain The domain for add
	 * @param string $solusvm_cid The SolusVM Container ID
	 * @return array Result of the operation
	 */	
	function addDomain($domain, $solusvm_cid=null){
		$conn = new mysqli($this->server, $this->userName, $this->password, $this->dbName, $this->dbPort);
		if ($conn->connect_error) {
    		die("Connection failed: " . $conn->connect_error);
		}
		$sql = "INSERT INTO domains (name, type, solusvm_cid)
				VALUES ('".htmlspecialchars($domain)."', 'NATIVE', '".intval($solusvm_cid)."')";
		if ($conn->query($sql) === TRUE) {
			return array("status" => "success", "msg" => "New record created successfully!");
		} else {
			return array("status" => "error", "msg" => "Error: ".$sql." - ".$conn->error);
		}
		$conn->close();
	}

	/**
	 * Delete Domain
	 *
	 * @param string $domain_id The domain ID to delete
	 * @return array Result of the operation
	 */	
	function deleteDomain($domain_id){
		$conn = new mysqli($this->server, $this->userName, $this->password, $this->dbName, $this->dbPort);
		if ($conn->connect_error) {
    		die("Connection failed: " . $conn->connect_error);
		}
		$sql = "DELETE FROM domains WHERE id = ".intval($domain_id);
		if ($conn->query($sql) === TRUE) {
			return array("status" => "success", "msg" => "Domain deleted successfully!");
		} else {
			return array("status" => "error", "msg" => "Error: ".$sql." - ".$conn->error);
		}
		$conn->close();
	}

	/**
	 * Get Domain ID
	 *
	 * @param string $domain The domain for add
	 * @param string $solusvm_cid The SolusVM Container ID
	 * @return array Result of the operation
	 */	
	function getDomainID($domain){
		$conn = new mysqli($this->server, $this->userName, $this->password, $this->dbName, $this->dbPort);
		if ($conn->connect_error) {
    		die("Connection failed: " . $conn->connect_error);
		}
		$sql = "SELECT * FROM domains WHERE name = '".htmlspecialchars($domain)."'";
		$response = $conn->query($sql);
		if ($response->num_rows > 0) {
			while($row = $response->fetch_assoc()) {
				return array("status" => "success", "msg" => "The domain ID is ".$row["id"], "id" => $row["id"], "solusvm_cid" => $row['solusvm_cid']);
			}
		} else {
			return array("status" => "error", "msg" => "Error: Domain not found.");
		}
		$conn->close();
	}
	
	/**
	 * Add Record
	 *
	 * @param string $domain_id The domain ID for the zone
	 * @param string $name The Record Name
	 * @param string $type The Record Type
	 * @param string $content The Record Content
	 * @return array Result of the operation
	 */	
	function addRecord($domain_id, $name, $type, $content, $ttl=1440, $prio=0){
		$conn = new mysqli($this->server, $this->userName, $this->password, $this->dbName, $this->dbPort);
		if ($conn->connect_error) {
    		die("Connection failed: " . $conn->connect_error);
		}
		$sql = "INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
				VALUES (".intval($domain_id).", '".htmlspecialchars($name)."', '".htmlspecialchars($type)."', '".htmlspecialchars($content)."', ".intval($ttl).", ".intval($prio).", ".time().")";
		if ($conn->query($sql) === TRUE) {
			return array("status" => "success", "msg" => "New record created successfully!");
		} else {
			return array("status" => "error", "msg" => "Error: ".$sql." - ".$conn->error);
		}
		$conn->close();
	}

	/**
	 * Get Domains by SolusVM Container ID
	 *
	 * @param string $solusvm_cid The SolusVM Container ID
	 * @return array Result of the operation
	 */	
	function getDomainsBySolusVMID($solusvm_cid){
		$conn = new mysqli($this->server, $this->userName, $this->password, $this->dbName, $this->dbPort);
		if ($conn->connect_error) {
    		die("Connection failed: " . $conn->connect_error);
		}
		$sql = "SELECT * FROM domains WHERE solusvm_cid = ".intval($solusvm_cid);
		$domains = array();
		$response = $conn->query($sql);
		if ($response->num_rows > 0) {
			while($row = $response->fetch_assoc()) {
				$domains[] = array("domain" => $row["name"], "id" => $row["id"], "solusvm_cid" => $row["solusvm_cid"]);
			}
			return array("status" => "success", "msg" => "The domains has been listed successfully", "domains" => $domains);
		} else {
			return array("status" => "error", "msg" => "Error: Domain not found.");
		}
		$conn->close();
	}

	/**
	 * Get Records of a Domain by Domain ID
	 *
	 * @param string $solusvm_cid The SolusVM Container ID
	 * @return array Result of the operation
	 */	
	function getRecordsByDomainID($domain_id){
		$conn = new mysqli($this->server, $this->userName, $this->password, $this->dbName, $this->dbPort);
		if ($conn->connect_error) {
    		die("Connection failed: " . $conn->connect_error);
		}
		$sql = "SELECT * FROM records WHERE domain_id = ".intval($domain_id);
		$domains = array();
		$response = $conn->query($sql);
		if ($response->num_rows > 0) {
			while($row = $response->fetch_assoc()) {
				$records[] = array("id" => $row["id"], "name" => $row["name"], "type" => $row["type"], "content" => $row["content"], "ttl" => $row["ttl"], "prio" => $row["prio"]);
			}
			return array("status" => "success", "msg" => "The rercord has been listed successfully", "zones" => $records);
		} else {
			return array("status" => "error", "msg" => "Error: Records not found.");
		}
		$conn->close();
	}
}
?>