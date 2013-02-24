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


class DropperImpl implements DropperInt
{
    private $_BASE = "";
    private $_CONFIG = "";
    private $_DESTROY_ENABLED = "";
    private $_DROPPER_API_KEY = "";
    private $_DROPPER_CLIENT_ID = "";
    private $_URI_ID = "";
    private $_URI_KEY = "";
    
    // SETUP
    public function setup($config) 
    {
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
    
    public function __destruct() {}
    
        
    //////////////
    // API ROOT //
    //////////////
    private function generateBaseQueryString($base="", $override="")
    {
        
        return $this->_BASE . $base . "?" . $override . $this->_URI_ID . "&" . 
               $this->_URI_KEY;
    }
    
    public function getDocumentation()
    {
        // TODO: Detect when curl_init not available
        $documentation = curl_init($this->generateBaseQueryString());
        return curl_exec($documentation);
    }
    
    // WARNING: This prints out the whole documentation page.
    public function displayDocumentation()
    {
        print $this->getDocumentation() . "\n";
    }
    

    //////////////
    // DROPLETS //
    //////////////
    
    // This method returns all active droplets that are currently running in 
    // your account. All available API information is presented for each 
    // droplet.
    public function showAllActiveDroplets()
    {
        // TODO: Detect when curl_init not available
        $droplets = curl_init($this->generateBaseQueryString("droplets"));
        $response = curl_exec($droplets);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method returns full information for a specific droplet ID that is 
    // passed in the URL.
    public function showDroplet($dropletID="")
    {
        // TODO: Detect when curl_init not available
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $showDropletString = "droplets/$dropletID";
        $droplets = curl_init(
            $this->generateBaseQueryString($showDropletString)
        );
        $response = curl_exec($droplets);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to create a new droplet. See the required 
    // parameters section below for an explanation of the variables that are 
    // needed to create a new droplet.
    //
    // TODO: Need parameters from DigitalOcean.
    // TODO: Need to write a "test".
    //
    public function newDroplet($name="",$image_id="",$region_id="",$size_id="",
                               $ssh_key_ids="")
    {
        // TODO: Detect when curl_init not available
        if ($name == "")
        {
            die("ERROR: Droplet name required.\n");
        }
        
        if ($image_id == "")
        {
            die("ERROR: Image ID required.\n");
        }
        
        // New York = 1
        // Amsterdam = 2
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
                                    "ssh_key_ids=$ssh_key_ids&";
        } else {
            $newDropletParameters = "name=$name&size_id=$size_id&" .
                                    "image_id=$image_id&region_id=$region_id&";
        }

        $newDroplet = curl_init(
            $this->generateBaseQueryString(
                $newDropletString, $newDropletParameters
            )
        );
        $response = curl_exec($newDroplet);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to reboot a droplet. This is the preferred method
    //  to use if a server is not responding.
    //
    // TODO: Need to write a "test".
    public function rebootDroplet($dropletID="")
    {
        // TODO: Detect when curl_init not available
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $rebootDropletString = "droplets/$dropletID/reboot/";
        $rebootDroplet = curl_init(
            $this->generateBaseQueryString($rebootDropletString)
        );
        $response = curl_exec($rebootDroplet);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to power cycle a droplet. This will turn off the 
    // droplet and then turn it back on.
    //
    // TODO: Need to write a "test".
    public function powerCycleDroplet($dropletID="")
    {
        // TODO: Detect when curl_init not available
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $powerCycleDropletString = "droplets/$dropletID/power_cycle/";
        $powerCycleDroplet = curl_init(
            $this->generateBaseQueryString($powerCycleDropletString)
        );
        $response = curl_exec($powerCycleDroplet);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to shutdown a running droplet. The droplet will 
    // remain in your account.
    //
    // TODO: Need to write a "test".
    public function shutdownDroplet($dropletID="")
    {
        // TODO: Detect when curl_init not available
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $shutdownDropletString = "droplets/$dropletID/shutdown/";
        $shutdownDroplet = curl_init(
            $this->generateBaseQueryString($shutdownDropletString)
        );
        $response = curl_exec($shutdownDroplet);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to poweroff a running droplet. The droplet will 
    // remain in your account.
    //
    // TODO: Need to write a "test".
    public function powerOffDroplet($dropletID="")
    {
        // TODO: Detect when curl_init not available
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $powerOffDropletString = "droplets/$dropletID/power_off/";
        $powerOffDroplet = curl_init(
            $this->generateBaseQueryString($powerOffDropletString)
        );
        $response = curl_exec($powerOffDroplet);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to poweron a powered off droplet.
    //
    // TODO: Need to write a "test".
    public function powerOnDroplet($dropletID="")
    {
        // TODO: Detect when curl_init not available
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $powerOnDropletString = "droplets/$dropletID/power_on/";
        $powerOnDroplet = curl_init(
            $this->generateBaseQueryString($powerOnDropletString)
        );
        $response = curl_exec($powerOnDroplet);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method will reset the root password for a droplet. Please be aware 
    // that this will reboot the droplet to allow resetting the password.
    //
    // TODO: Need to write a "test".
    public function resetRootPassword($dropletID="")
    {
        // TODO: Detect when curl_init not available
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $resetRootPasswordString = "droplets/$dropletID/reset_root_password/";
        $resetRootPassword = curl_init(
            $this->generateBaseQueryString($resetRootPasswordString)
        );
        $response = curl_exec($resetRootPassword);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to resize a specific droplet to a different size. 
    // This will affect the number of processors and memory allocated to the 
    // droplet.
    //
    // TODO: Need parameters from DigitalOcean.
    // TODO: Need to write a "test".
    //
    public function resizeDroplet($dropletID="",$size_id="")
    {
        print "NOTICE: PENDING IMPLEMENTATION\n";
        die("DigitalOcean.com please contact adam@runbymany.com\n");
        
        // TODO: Detect when curl_init not available
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

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to take a snapshot of the running droplet, which 
    // can later be restored or used to create a new droplet from the same 
    // image. Please be aware this may cause a reboot.
    //
    // TODO: Need to write a "test".
    public function takeSnapshot($dropletID="",$name="")
    {
        
        // TODO: Detect when curl_init not available
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

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to restore a droplet with a previous image or 
    // snapshot. This will be a mirror copy of the image or snapshot to your 
    // droplet. Be sure you have backed up any necessary information prior to 
    // restore.
    //
    // TODO: Need to write a "test".
    public function restoreDroplet($dropletID="",$image_id="")
    {
        
        // TODO: Detect when curl_init not available
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

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to reinstall a droplet with a default image. This
    //  is useful if you want to start again but retain the same IP address for
    //   your droplet.
    //
    // TODO: Need to write a "test".
    public function rebuildDroplet($dropletID="",$image_id="")
    {
        
        // TODO: Detect when curl_init not available
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

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method enables automatic backups which run in the background daily
    //  to backup your droplet's data.
    //
    // TODO: Need to write a "test".
    public function enableAutomaticBackups($dropletID="")
    {
        
        // TODO: Detect when curl_init not available
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $enableAutomaticBackupsString = "droplets/$dropletID/enable_backups/";
                                 
        $enableAutomaticBackups = curl_init(
            $this->generateBaseQueryString($enableAutomaticBackupsString)
        );
        $response = curl_exec($enableAutomaticBackups);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method disables automatic backups from running to backup your 
    // droplet's data.
    //
    // TODO: Need to write a "test".
    public function disableAutomaticBackups($dropletID="")
    {
        
        // TODO: Detect when curl_init not available
        if ($dropletID == "")
        {
            die("ERROR: Droplet ID required.\n");
        }
        
        $disableAutomaticBackupsString = "droplets/$dropletID/disable_backups/";
                                 
        $disableAutomaticBackups = curl_init(
            $this->generateBaseQueryString($disableAutomaticBackupsString)
        );
        $response = curl_exec($disableAutomaticBackups);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method destroys one of your droplets - this is irreversible.
    //
    // TODO: Need to write a "test".
    public function destroyDroplet($dropletID="")
    {
        
        // TODO: Detect when curl_init not available
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
            // TODO: Detect when json_decode not available
            $decodedResponse = json_decode(utf8_encode($response),1);
            return $decodedResponse;
        } else {
            die("ERROR: DESTROY FUNCTIONALITY MUST BE ENABLED IN " . 
                "SETTINGS.INI\n");
        }
    }
 
    
    /////////////
    // REGIONS //
    /////////////
    
    // This method will return all the available regions within the Digital 
    // Ocean cloud.
    //
    // TODO: Need to write a "test".
    public function allRegions()
    {
        
        // TODO: Detect when curl_init not available
        $allRegionsString = "regions/";
                                 
        $allRegions = curl_init(
            $this->generateBaseQueryString($allRegionsString)
        );
        $response = curl_exec($allRegions);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    
    ////////////
    // IMAGES //
    ////////////
    
    // This method returns all the available images that can be accessed by your
    //  client ID. You will have access to all public images by default, and any 
    //  snapshots or backups that you have created in your own account.
    //
    // TODO: Need to write a "test".
    public function allImages()
    {
        
        // TODO: Detect when curl_init not available
        $allImagesString = "images/";
                                 
        $allImages = curl_init(
            $this->generateBaseQueryString($allImagesString)
        );
        $response = curl_exec($allImages);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method displays the attributes of an image.
    //
    // TODO: Need to write a "test".
    public function showImage($image_id="")
    {
        
        // TODO: Detect when curl_init not available
        if ($image_id == "")
        {
            die("ERROR: Image ID required.\n");
        }
        
        $showImageString = "images/$image_id/";
                                 
        $showImage = curl_init(
            $this->generateBaseQueryString($showImageString)
        );
        $response = curl_exec($showImage);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method destroys one of your droplets - this is irreversible.
    //
    // TODO: Need to write a "test".
    public function destroyImage($image_id="")
    {
        
        // TODO: Detect when curl_init not available
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
            // TODO: Detect when json_decode not available
            $decodedResponse = json_decode(utf8_encode($response),1);
            return $decodedResponse;
        } else {
            die("ERROR: DESTROY FUNCTIONALITY MUST BE ENABLED IN " . 
                "SETTINGS.INI\n");
        }
    }
    
    
    //////////////
    // SSH KEYS //
    //////////////
    
    // This method lists all the available public SSH keys in your account that
    //  can be added to a droplet.
    //
    // TODO: Need to write a "test".
    public function allSSHKeys()
    {
        
        // TODO: Detect when curl_init not available
        $allSSHKeysString = "ssh_keys/";
                                 
        $allSSHKeys = curl_init(
            $this->generateBaseQueryString($allSSHKeysString)
        );
        $response = curl_exec($allSSHKeys);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method lists all the available public SSH keys in your account that
    // can be added to a droplet.
    //
    // TODO: Need to write a "test".
    public function showSSHKey($key_id="")
    {
        
        // TODO: Detect when curl_init not available
        if ($key_id == "")
        {
            die("ERROR: Key ID required.\n");
        }
        
        $showSSHKeyString = "ssh_keys/$key_id/";
                                 
        $showSSHKey = curl_init(
            $this->generateBaseQueryString($showSSHKeyString)
        );
        $response = curl_exec($showSSHKey);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
    // This method allows you to add a new public SSH key to your account.
    //
    // TODO: Get status on implementation from digitalocean
    // TODO: Need to write a "test".
    public function addSSHKey($ssh_key_pub="")
    {
        print "NOTICE: PENDING IMPLEMENTATION\n";
        die("DigitalOcean.com please contact adam@runbymany.com\n");
        
        // TODO: Detect when curl_init not available
        if ($ssh_key_pub == "")
        {
            die("ERROR: Key ID required.\n");
        }
        
    }
    
    // This method allows you to modify an existing public SSH key in your 
    // account.
    //
    // TODO: Get status on implementation from digitalocean
    // TODO: Need to write a "test".
    public function editSSHKey($ssh_key_pub="")
    {
        print "NOTICE: PENDING IMPLEMENTATION\n";
        die("DigitalOcean.com please contact adam@runbymany.com\n");
        
        // TODO: Detect when curl_init not available
        if ($ssh_key_pub == "")
        {
            die("ERROR: Key ID required.\n");
        }
        
    }
    
    // This method will delete the SSH key from your account.
    //
    // TODO: Need to write a "test".
    public function destroySSHKey($key_id="")
    {
        
        // TODO: Detect when curl_init not available
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
            // TODO: Detect when json_decode not available
            $decodedResponse = json_decode(utf8_encode($response),1);
            return $decodedResponse;
        } else {
            die("ERROR: DESTROY FUNCTIONALITY MUST BE ENABLED IN " . 
                "SETTINGS.INI\n");
        }
    }
    
    
    ////////////
    // SIZES //
    ////////////
    
    // This method returns all the available sizes that can be used to create a 
    // droplet.
    //
    // TODO: Need to write a "test".
    public function allSizes()
    {
        
        // TODO: Detect when curl_init not available
        $allSizesString = "sizes/";
                                 
        $allSizes = curl_init(
            $this->generateBaseQueryString($allSizesString)
        );
        $response = curl_exec($allSizes);

        // TODO: Detect when json_decode not available
        $decodedResponse = json_decode(utf8_encode($response),1);
        return $decodedResponse;
    }
    
}

?>
