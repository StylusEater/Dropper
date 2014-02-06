<?php
/**
* This file is part of Dropper.
*
* Dropper is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Dropper is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Dropper. If not, see <http://www.gnu.org/licenses/>.
*
* @author Adam M. Dutko <adam@runbymany.com>
* @link http://www.runbymany.com
* @copyright Copyright &copy; 2011 RunByMany, LLC
* @license GPLv3 or Later
*/

require_once "DropperInt.php";

/**
 * This class implements the interface to Dropper. Right now 
 * we simply require the setup function to be implemented but
 * as the DigitalOcean API solidifies we might require others 
 * to be implemented.
 * 
 * @todo Add supported DNS functions.
 * @todo Add general error detection method with callback.
 * @todo Add supported events function.
 * 
 */
class DropperImpl implements DropperInt
{
    /**
     * \private @property $_BASE The internal reference to the base URL of the 
     * API endpoint.
     */
    private $_BASE = "";
    /**
     * \private @property $_CONFIG The internal reference to the settings file.
     */
    private $_CONFIG = "";
    /**
     * \private @property $_DESTROY_ENABLED The internal reference to whether the 
     * user allowed deletion of droplets in their settings file. This helps protect
     * against accidental deletions.
     */
    private $_DESTROY_ENABLED = "";
    /**
     * \private @property $_DROPPER_API_KEY The internal reference to the unique
     * user API KEY assigned by DigitalOcean.com. 
     */
    private $_DROPPER_API_KEY = "";
    /**
     * \private @property $_DROPPER_CLIENT_ID The internal reference to the unique
     * user CLIENT ID assigned by DigitalOcean.com. 
     */
    private $_DROPPER_CLIENT_ID = "";
    /**
     * \private @property $_URI_ID The internal reference to the component of the
     * API request URI containing the user CLIENT ID.
     */
    private $_URI_ID = "";
    /**
     * \private @property $_URI_KEY The internal reference to the component of the
     * API request URI containing the user API KEY.
     */
    private $_URI_KEY = "";
    
    /** 
     * A function used to source the configuration options and
     * make sure you have the proper libraries installed.
     * 
     * @param string $config Path to the configuration file with 
     * personal API credentials. The file should be named 
     * settings.ini but it is not a strict requirement, just 
     * passing a valid path is required. Example settings are 
     * contained in example.ini in the root of the project.
     *
     * @return void
     *
     */ 
    public function setup($config) 
    {

        if (!function_exists( "curl_init" ))
        {
            die("ERROR: You must have the PHP curl extensions installed.\n");
        }

        if (!function_exists( "json_decode" ))
        {
            die("ERROR: You must have the PHP JSON extensions installed.\n");
        }

        $this->_CONFIG = $config;
        
        // Check if $config is valid file path
        $configHandle = parse_ini_file($this->_CONFIG, true);   
        
        // Go over our options
        if (count($configHandle) == 0)
        {
            die("ERROR: NO OPTIONS SET IN DROPPER CONFIGURATION!\n");
        }
        
        // Set client settings
        $this->_BASE = $configHandle["API"]["base"];
        $this->_DESTROY_ENABLED = $configHandle["DESTROY"]["enable"];
        $this->_DROPPER_API_KEY = $configHandle["API"]["key"];
        $this->_DROPPER_CLIENT_ID = $configHandle["CLIENT"]["id"];
        
        // Test client setting
        if ($this->_DROPPER_API_KEY == "")
        {
            die("ERROR: MISSING API KEY\n");
        }
        
        // Test client setting
        if ($this->_DROPPER_CLIENT_ID == "")
        {
            die("ERROR: MISSING DROPPER CLIENT ID!\n");
        }
        
        // Config URI settings after init
        $this->_URI_ID = "client_id=" . $this->_DROPPER_CLIENT_ID;
        $this->_URI_KEY = "api_key=" . $this->_DROPPER_API_KEY;
        
    }
   
    /**
      * An unused cleanup function written for the sake of being pedantic.
      *
      */  
    public function __destruct() {}
    
    /**
      * The internal function used to generate the base API URI using the
      * unique CLIENT ID and unique API KEY.
      *
      * @param string $base This is the base URI of the API. In case the 
      * endpoint changes in the future it can be overriden here but is 
      * currently blank and uses the default.
      *
      * @param string $override This is used to add additonal options 
      * not currently implemented or supported by the API.
      *
      * @return string Base URI of the API.
      *
      */ 
    private function generateBaseQueryString($base="", $override="")
    {
        
        return $this->_BASE . $base . "?" . $override . $this->_URI_ID . "&" . 
               $this->_URI_KEY;
    }
   
    /**
      * Display a link to the DigitalOcean.com API documentation.
      *  
      * @return string
      *
      */
    public function getDocumentation()
    {
        $documentation = curl_init($this->generateBaseQueryString());
        return curl_exec($documentation);
    }
   
    /**
      * Print the entire contents of the documentation page to STDOUT.
      * 
      * WARNING: This prints out the whole documentation page.
      *
      * @return string
      *
      */ 
    public function displayDocumentation()
    {
        print $this->getDocumentation() . "\n";
    }
    

    /**
      * Returns all active droplets that are currently running in your
      * account. All available API information is presented for each 
      * droplet.
      * 
      * @return JSON 
      *
      */
    public function showAllActiveDroplets()
    {
        $droplets = curl_init($this->generateBaseQueryString("droplets"));
        $response = curl_exec($droplets);
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
   
    /**
      * Returns full information for a specific droplet ID that is 
      * passed in the URL.
      * 
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @return JSON
      *
      */ 
    public function showDroplet($dropletID="")
    {
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $showDropletString = "droplets/$dropletID";
        $droplets = curl_init(
            $this->generateBaseQueryString($showDropletString)
        );
        $response = curl_exec($droplets);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to create a new droplet. 
      *
      * @param string $name The name of the droplet.
      * @param integer $image_id The ID of a particular GNU/Linux distribution.
      * @param integer $region_id The ID of the desired datacenter.
      * @param integer $size_id The size of the droplet.
      * @param integer $ssh_key_ids The ID of an ssh key registered in your account.
      * @param boolean $private_networking Enable a private network interface if the
      * selected region supports it.
      * @param boolean $backups_enabled Enables backups of the droplets data. 
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function newDroplet($name="",$image_id="",$region_id="",$size_id="",
                               $ssh_key_ids="",$private_networking=False,
                               $backups_enabled=False)
    {
        if ($name == "")
        {
            die("ERROR: Droplet name required.\n");
        }
        
        if ($image_id == "")
        {
            die("ERROR: Image ID required.\n");
        }
        
        if ($region_id == "")
        {
            die("ERROR: Region ID required.\n");
        }
        
        if ($size_id == "")
        {
            die("ERROR: Size ID required.\n");
        }
        
        $newDropletString = "droplets/new";

        if ($ssh_key_ids != "")
        {
            $newDropletParameters = "name=$name&size_id=$size_id&" .
                                    "image_id=$image_id&region_id=$region_id&" .
                                    "ssh_key_ids=$ssh_key_ids&" .
                                    "private_networking=$private_networking&" .
                                    "backups_enabled=$backups_enabled&";
        } else {
            $newDropletParameters = "name=$name&size_id=$size_id&" .
                                    "image_id=$image_id&region_id=$region_id&" .
                                    "private_networking=$private_networking&" .
                                    "backups_enabled=$backups_enabled&";
        }

        $newDroplet = curl_init(
            $this->generateBaseQueryString(
                $newDropletString, $newDropletParameters
            )
        );
        $response = curl_exec($newDroplet);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to reboot a droplet. This is the preferred method
      * to use if a server is not responding.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @todo Need to write a "test".
      * 
      * @return JSON
      *
      */
    public function rebootDroplet($dropletID="")
    {
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $rebootDropletString = "droplets/$dropletID/reboot/";
        $rebootDroplet = curl_init(
            $this->generateBaseQueryString($rebootDropletString)
        );
        $response = curl_exec($rebootDroplet);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to power cycle a droplet. This will power down 
      * the droplet and then power it up.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      *
      */
    public function powerCycleDroplet($dropletID="")
    {
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $powerCycleDropletString = "droplets/$dropletID/power_cycle/";
        $powerCycleDroplet = curl_init(
            $this->generateBaseQueryString($powerCycleDropletString)
        );
        $response = curl_exec($powerCycleDroplet);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to shutdown a running droplet. The droplet will 
      * remain in your account and you will continue to be billed.
      * 
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      *
      */
    public function shutdownDroplet($dropletID="")
    {
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $shutdownDropletString = "droplets/$dropletID/shutdown/";
        $shutdownDroplet = curl_init(
            $this->generateBaseQueryString($shutdownDropletString)
        );
        $response = curl_exec($shutdownDroplet);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to power off a running droplet. The droplet will 
      * remain in your account and you will continue to be billed.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @todo Need to write a test.
      *
      * @return JSON
      *
      */
    public function powerOffDroplet($dropletID="")
    {
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $powerOffDropletString = "droplets/$dropletID/power_off/";
        $powerOffDroplet = curl_init(
            $this->generateBaseQueryString($powerOffDropletString)
        );
        $response = curl_exec($powerOffDroplet);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to power on a powered off droplet.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @todo Need to write a test.
      *
      * @return JSON
      *
      */
    public function powerOnDroplet($dropletID="")
    {
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $powerOnDropletString = "droplets/$dropletID/power_on/";
        $powerOnDroplet = curl_init(
            $this->generateBaseQueryString($powerOnDropletString)
        );
        $response = curl_exec($powerOnDroplet);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to reset the root password for a droplet. Please be aware 
      * that this will reboot the droplet.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @todo Need to write a test.
      *
      * @return JSON
      *
      */
    public function resetRootPassword($dropletID="")
    {
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $resetRootPasswordString = "droplets/$dropletID/password_reset/";
        $resetRootPassword = curl_init(
            $this->generateBaseQueryString($resetRootPasswordString)
        );
        $response = curl_exec($resetRootPassword);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to resize a specific droplet to a different size. 
      * This will affect the number of processors and memory allocated to the 
      * droplet.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      * @param integer $size_id The size of the droplet.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function resizeDroplet($dropletID="",$size_id="")
    {
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        if ($size_id == "")
        {
            die("ERROR: Size ID required.\n");
        }
        
        $resizeDropletString = "droplets/$dropletID/resize/";
        $resizeDropletParameters = "size_id=$size_id&";
                                 
        $resizeDroplet = curl_init(
            $this->generateBaseQueryString(
                $resizeDropletString, $resizeDropletParameters
            )
        );
        $response = curl_exec($resizeDroplet);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /** 
      * Enables you to take a snapshot of the running droplet, which 
      * can later be restored or used to create a new droplet from the same 
      * image. Please be aware this may cause a reboot.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      * @param string $name The name of the snapshot.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function takeSnapshot($dropletID="",$name="")
    {
        
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        if ($name == "")
        {
            die("ERROR: Name required.\n");
        }
        
        $takeSnapshotString = "droplets/$dropletID/snapshot/";
        $takeSnapshotParameters = "name=$name&";
                                 
        $takeSnapshot = curl_init(
            $this->generateBaseQueryString(
                $takeSnapshotString, $takeSnapshotParameters
            )
        );
        $response = curl_exec($takeSnapshot);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to restore a droplet with a previous image or 
      * snapshot. This will be a mirror copy of the image or snapshot to your 
      * droplet. Be sure you have backed up any necessary information prior to 
      * restore.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      * @param integer $image_id The size of the droplet.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function restoreDroplet($dropletID="",$image_id="")
    {
        
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        if ($image_id == "")
        {
            die("ERROR: Image ID required.\n");
        }
        
        $restoreDropletString = "droplets/$dropletID/restore/";
        $restoreDropletParameters = "image_id=$image_id&";
                                 
        $restoreDroplet = curl_init(
            $this->generateBaseQueryString(
                $restoreDropletString, $restoreDropletParameters
            )
        );
        $response = curl_exec($restoreDroplet);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to reinstall a droplet with a default image. This
      * is useful if you want to start again but retain the same IP address for
      * your droplet.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      * @param integer $image_id The size of the droplet.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function rebuildDroplet($dropletID="",$image_id="")
    {
        
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        if ($image_id == "")
        {
            die("ERROR: Image ID required.\n");
        }
        
        $rebuildDropletString = "droplets/$dropletID/rebuild/";
        $rebuildDropletParameters = "image_id=$image_id&";
                                 
        $rebuildDroplet = curl_init(
            $this->generateBaseQueryString(
                $rebuildDropletString, $rebuildDropletParameters
            )
        );
        $response = curl_exec($rebuildDroplet);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables automatic backups which run in the background daily
      * to backup your droplet's data.
      *
      * @deprecated 
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function enableAutomaticBackups($dropletID="")
    {
        die("ERROR: Backups are optionally enabled on droplet creation.");
        
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $enableAutomaticBackupsString = "droplets/$dropletID/enable_backups/";
                                 
        $enableAutomaticBackups = curl_init(
            $this->generateBaseQueryString($enableAutomaticBackupsString)
        );
        $response = curl_exec($enableAutomaticBackups);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Disables automatic backups from running to backup your 
      * droplet's data.
      *
      * @deprecated 
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function disableAutomaticBackups($dropletID="")
    {
        
        die("ERROR: Backups are optionally enabled on droplet creation.");

        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $disableAutomaticBackupsString = "droplets/$dropletID/disable_backups/";
                                 
        $disableAutomaticBackups = curl_init(
            $this->generateBaseQueryString($disableAutomaticBackupsString)
        );
        $response = curl_exec($disableAutomaticBackups);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Enables you to destroy one of your droplets - this is irreversible.
      * In order to use this method you must enable it in your settings file.
      *
      * @param integer $dropletID The unique ID of a particular droplet.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function destroyDroplet($dropletID="")
    {
        
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $destroyDropletString = "droplets/$dropletID/destroy/";
                                 
        $destroyDroplet = curl_init(
            $this->generateBaseQueryString($destroyDropletString)
        );
        
        if ($this->_DESTROY_ENABLED == 1)
        {
            $response = curl_exec($destroyDroplet);
            $decodedResponse = json_decode(utf8_encode($response),1);
            return $decodedResponse;
        } else {
            die("ERROR: DESTROY FUNCTIONALITY MUST BE ENABLED IN " . 
                "SETTINGS.INI\n");
        }
    }
 
    
    /**
      * Returns all the available regions within the Digital 
      * Ocean cloud.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function allRegions()
    {
        
        $allRegionsString = "regions/";
                                 
        $allRegions = curl_init(
            $this->generateBaseQueryString($allRegionsString)
        );
        $response = curl_exec($allRegions);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    
    /** 
      * Shows all the available images that can be accessed by your
      * client ID. You will have access to all public images by default, and any 
      * snapshots or backups that you have created in your own account.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function allImages()
    {
        
        $allImagesString = "images/";
                                 
        $allImages = curl_init(
            $this->generateBaseQueryString($allImagesString)
        );
        $response = curl_exec($allImages);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Displays the attributes of an image.
      *
      * @param integer $image_id The unique ID of a particular droplet image.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function showImage($image_id="")
    {
        
        if ($image_id == "")
        {
            die("ERROR: Image ID required.\n");
        }
        
        $showImageString = "images/$image_id/";
                                 
        $showImage = curl_init(
            $this->generateBaseQueryString($showImageString)
        );
        $response = curl_exec($showImage);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Destroys one of your droplet images - this is irreversible.
      * In order to use this method you must enable it in your settings file.
      *
      * @param integer $image_id The unique ID of a particular droplet image.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function destroyImage($image_id="")
    {
        
        if ($image_id == "")
        {
            die("ERROR: Image ID required.\n");
        }
        
        $destroyImageString = "images/$image_id/destroy/";
                                 
        $destroyImage = curl_init(
            $this->generateBaseQueryString($destroyImageString)
        );
        
        if ($this->_DESTROY_ENABLED == 1)
        {
            $response = curl_exec($destroyImage);
            $decodedResponse = json_decode(utf8_encode($response),1);
            return $decodedResponse;
        } else {
            die("ERROR: DESTROY FUNCTIONALITY MUST BE ENABLED IN " . 
                "SETTINGS.INI\n");
        }
    }
    
    /**
      * Transfers an image to another region in the DigitalOcean.com cloud.
      *
      * @param integer $image_id The unique ID of a particular droplet image.
      * @param integer $region_id The ID of the desired datacenter.
      *
      * @todo Need to write a "test".
      *
      * @return JSON
      */
    public function transferImage($image_id="",$region_id="")
    {
        
        if ($image_id == "")
        {
            die("ERROR: Image ID required.\n");
        }
        
        if ($region_id == "")
        {
            die("ERROR: Region ID required.\n");
        }
        
        $transferImageString = "images/$image_id/transfer/";
        $transferImageParameters = "region_id=$region_id&";
                                 
        $transferImage = curl_init(
            $this->generateBaseQueryString(
                $transferImageString, $transferImageParameters
            )
        );

        $response = curl_exec($transferImage);
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    
    /**
      * Lists all the available public SSH keys in your account that
      * can be added to a droplet on creation.
      *
      * @return JSON
      */
    public function allSSHKeys()
    {
        
        $allSSHKeysString = "ssh_keys/";
                                 
        $allSSHKeys = curl_init(
            $this->generateBaseQueryString($allSSHKeysString)
        );
        $response = curl_exec($allSSHKeys);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Show a particular SSH key in your account that can be added to
      * a droplet.
      *
      * @param integer $key_id The unique ID of a particular key.
      *
      * @return JSON
      *
      */ 
    public function showSSHKey($key_id="")
    {
        
        if ($key_id == "")
        {
            die("ERROR: Key ID required.\n");
        }
        
        $showSSHKeyString = "ssh_keys/$key_id/";
                                 
        $showSSHKey = curl_init(
            $this->generateBaseQueryString($showSSHKeyString)
        );
        $response = curl_exec($showSSHKey);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Add a new public SSH key to your account.
      *
      * @param string $name The name of your SSH public key.
      * @param string $ssh_pub_key The contents of your public key.
      *
      * @return JSON
      *
      */
    public function addSSHKey($name="", $ssh_pub_key="")
    {
        if ($name == "")
        {
            die("ERROR: Name required.\n");
        }

        if ($ssh_pub_key == "")
        {
            die("ERROR: Public key required.\n");
        }
        
        $addSSHKeyString = "ssh_keys/new/";
        $addSSHKeyParameters = "name=$name&ssh_pub_key=$ssh_pub_key&";
                                 
        $addSSHKey = curl_init(
            $this->generateBaseQueryString(
                $addSSHKeyString, $addSSHKeyParameters
            )
        );

        $response = curl_exec($addSSHKey);
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    /**
      * Modify an existing public SSH key in your account.
      *
      * @param integer $key_id The unique ID of a particular key.
      * @param string $ssh_pub_key The contents of your public key.
      *
      * @return JSON
      *
      */
    public function editSSHKey($key_id="", $ssh_pub_key="")
    {
        if ($key_id == "")
        {
            die("ERROR: Key ID required.\n");
        }

        if ($ssh_pub_key == "")
        {
            die("ERROR: Public key required.\n");
        }

        $editSSHKeyString = "ssh_keys/$key_id/edit/";
        $editSSHKeyParameters = "ssh_pub_key=$ssh_pub_key&";
                                 
        $editSSHKey = curl_init(
            $this->generateBaseQueryString(
                $editSSHKeyString, $editSSHKeyParameters
            )
        );

        $response = curl_exec($editSSHKey);
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
        
    }
    
    /**
      * Delete a particular SSH key from your account.
      * In order to use this method you must enable it in your settings file.
      *
      * @param integer $key_id The unique ID of a particular key.
      *
      * @return JSON
      *
      */
    public function destroySSHKey($key_id="")
    {
        
        if ($key_id == "")
        {
            die("ERROR: Key ID required.\n");
        }
        
        $destroySSHKeyString = "ssh_keys/$key_id/destroy/";
                                 
        $destroySSHKey = curl_init(
            $this->generateBaseQueryString($destroySSHKeyString)
        );
        
        if ($this->_DESTROY_ENABLED == 1)
        {
            $response = curl_exec($destroySSHKey);
            $decodedResponse = json_decode(utf8_encode($response),1);
            return $decodedResponse;
        } else {
            die("ERROR: DESTROY FUNCTIONALITY MUST BE ENABLED IN " . 
                "SETTINGS.INI\n");
        }
    }
    
    
    /**
      * This method returns all the available sizes that can be used to create a 
      * droplet.
      *
      * @return JSON
      *
      */
    public function allSizes()
    {
        
        $allSizesString = "sizes/";
                                 
        $allSizes = curl_init(
            $this->generateBaseQueryString($allSizesString)
        );
        $response = curl_exec($allSizes);

        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
}

?>
