<?php
/**
 * An HTTP API for PowerDNS.
 *
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license http://www.wtfpl.net/about/ The Do What The Fuck You Want To Public License (WTFPL)
 * @author CyanDark, Inc <support@cyandark.com>
 */

namespace CyanDark\Api;

class PowerDNS
{
    protected $server;
    protected $username;
    protected $password;
    protected $dbname;
    protected $dbport;

    /**
     * Constructor.
     *
     * @param string $server The PowerDNS Server
     * @param string $username The PowerDNS MySQL username
     * @param string $password The password of the database
     * @param string $dbname The PowerDNS database name
     * @param int $dbport The port used for MySQL
     * @return stdClass The PowerDNS class to connect to DB
     */
    public function __construct($server, $username, $password, $dbname = 'powerdns', $dbport = 3306)
    {
        $this->server   = $server;
        $this->username = $username;
        $this->password = $password;
        $this->dbname   = $dbname;
        $this->dbport   = $dbport;
    }

    /**
     * It's a Valid Domain?
     *
     * @param string $domain The domain for verify
     * @return bool Result of the operation
     */
    public function isValidDomain($domain)
    {
        return preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain) // Valid chars check
            && preg_match('/^.{1,253}$/', $domain) // Overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain); // Length of each label
    }

    /**
     * Add Domain.
     *
     * @param string $domain The domain for add
     * @param string $solusvm_cid The SolusVM Container ID
     * @return array Result of the operation
     */
    public function addDomain($domain, $solusvm_cid = null)
    {
        $conn = new mysqli($this->server, $this->username, $this->password, $this->dbname, $this->dbport);
        if ($conn->connect_error) {
            throw new Exception('Connection failed: '.$conn->connect_error);
        }
        if (!empty($solusvm_cid)) {
            $sql = "INSERT INTO domains (name, type, solusvm_cid)
					VALUES ('".htmlspecialchars($domain)."', 'NATIVE', '".(int) $solusvm_cid."')";
        }
        $sql = "INSERT INTO domains (name, type)
				VALUES ('".htmlspecialchars($domain)."', 'NATIVE')";
        if ($conn->query($sql) === true) {
            return ['status' => 'success', 'msg' => 'New record created successfully!'];
        }

        return ['status' => 'error', 'msg' => 'Error: '.$sql.' - '.$conn->error];
        $conn->close();
    }

    /**
     * Delete Domain.
     *
     * @param string $domain_id The domain ID to delete
     * @return array Result of the operation
     */
    public function deleteDomain($domain_id)
    {
        $conn = new mysqli($this->server, $this->username, $this->password, $this->dbname, $this->dbport);
        if ($conn->connect_error) {
            throw new Exception('Connection failed: '.$conn->connect_error);
        }
        $sql  = 'DELETE FROM domains WHERE id = '.(int) $domain_id;
        $sql2 = 'DELETE FROM records WHERE domain_id = '.(int) $domain_id;
        if ($conn->query($sql) === true && $conn->query($sql2) === true) {
            return ['status' => 'success', 'msg' => 'Domain deleted successfully!'];
        }

        return ['status' => 'error', 'msg' => 'Error: '.$sql.' - '.$conn->error];
        $conn->close();
    }

    /**
     * Get Domain ID.
     *
     * @param string $domain The domain for add
     * @return array Result of the operation
     */
    public function getDomainID($domain)
    {
        $conn = new mysqli($this->server, $this->username, $this->password, $this->dbname, $this->dbport);
        if ($conn->connect_error) {
            throw new Exception('Connection failed: '.$conn->connect_error);
        }
        $sql      = "SELECT * FROM domains WHERE name = '".htmlspecialchars($domain)."'";
        $response = $conn->query($sql);
        if ($response->num_rows > 0) {
            while ($row = $response->fetch_assoc()) {
                return ['status' => 'success', 'msg' => 'The domain ID is '.$row['id'], 'id' => $row['id'], 'solusvm_cid' => $row['solusvm_cid']];
            }
        }

        return ['status' => 'error', 'msg' => 'Error: Domain not found.'];
        $conn->close();
    }

    /**
     * Add Record.
     *
     * @param string $domain_id The domain ID for the zone
     * @param string $name The Record Name
     * @param string $type The Record Type
     * @param string $content The Record Content
     * @return array Result of the operation
     */
    public function addRecord($domain_id, $name, $type, $content, $ttl=1440, $prio=0)
    {
        $conn = new mysqli($this->server, $this->username, $this->password, $this->dbname, $this->dbport);
        if ($conn->connect_error) {
            throw new Exception('Connection failed: '.$conn->connect_error);
        }
        $sql = 'INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
				VALUES ('.(int) $domain_id.", '".htmlspecialchars($name)."', '".htmlspecialchars($type)."', '".htmlspecialchars($content)."', ".(int) $ttl.', '.(int) $prio.', '.time().')';
        if ($conn->query($sql) === true) {
            return ['status' => 'success', 'msg' => 'New record created successfully!'];
        }

        return ['status' => 'error', 'msg' => 'Error: '.$sql.' - '.$conn->error];
        $conn->close();
    }

    /**
     * Delete Record.
     *
     * @param string $record_id The record ID to delete
     * @return array Result of the operation
     */
    public function deleteRecord($record_id)
    {
        $conn = new mysqli($this->server, $this->username, $this->password, $this->dbname, $this->dbport);
        if ($conn->connect_error) {
            throw new Exception('Connection failed: '.$conn->connect_error);
        }
        $sql = 'DELETE FROM records WHERE id = '.(int) $record_id;
        if ($conn->query($sql) === true) {
            return ['status' => 'success', 'msg' => 'Record deleted successfully!'];
        }

        return ['status' => 'error', 'msg' => 'Error: '.$sql.' - '.$conn->error];
        $conn->close();
    }

    /**
     * Get Domains by SolusVM Container ID.
     *
     * @param string $solusvm_cid The SolusVM Container ID
     * @return array Result of the operation
     */
    public function getDomainsBySolusVMID($solusvm_cid)
    {
        $conn = new mysqli($this->server, $this->username, $this->password, $this->dbname, $this->dbport);
        if ($conn->connect_error) {
            throw new Exception('Connection failed: '.$conn->connect_error);
        }
        $sql      = 'SELECT * FROM domains WHERE solusvm_cid = '.(int) $solusvm_cid;
        $domains  = [];
        $response = $conn->query($sql);
        if ($response->num_rows > 0) {
            while ($row = $response->fetch_assoc()) {
                $domains[] = ['domain' => $row['name'], 'id' => $row['id'], 'solusvm_cid' => $row['solusvm_cid']];
            }

            return ['status' => 'success', 'msg' => 'The domains has been listed successfully', 'domains' => $domains];
        }

        return ['status' => 'error', 'msg' => 'Error: Domain not found.'];
        $conn->close();
    }

    /**
     * Get Records of a Domain by Domain ID.
     *
     * @param string $solusvm_cid The SolusVM Container ID
     * @return array Result of the operation
     */
    public function getRecordsByDomainID($domain_id)
    {
        $conn = new mysqli($this->server, $this->username, $this->password, $this->dbname, $this->dbport);
        if ($conn->connect_error) {
            throw new Exception('Connection failed: '.$conn->connect_error);
        }
        $sql      = 'SELECT * FROM records WHERE domain_id = '.(int) $domain_id;
        $records  = [];
        $response = $conn->query($sql);
        if ($response->num_rows > 0) {
            while ($row = $response->fetch_assoc()) {
                $records[] = ['id' => $row['id'], 'name' => $row['name'], 'type' => $row['type'], 'content' => $row['content'], 'ttl' => $row['ttl'], 'prio' => $row['prio']];
            }

            return ['status' => 'success', 'msg' => 'The rercord has been listed successfully', 'zones' => $records];
        }

        return ['status' => 'error', 'msg' => 'Error: Records not found.'];
        $conn->close();
    }

    /**
     * Create solusvm_cid Row.
     *
     * @return array Result of the operation
     */
    public function createSolusVMID()
    {
        $conn = new mysqli($this->server, $this->username, $this->password, $this->dbname, $this->dbport);
        if ($conn->connect_error) {
            throw new Exception('Connection failed: '.$conn->connect_error);
        }
        $sql = 'ALTER TABLE domains ADD solusvm_cid VARCHAR(60)';
        if ($conn->query($sql) === true) {
            return ['status' => 'success', 'msg' => 'Column created successfully!'];
        }

        return ['status' => 'error', 'msg' => 'Error: '.$sql.' - '.$conn->error];
        $conn->close();
    }
}
