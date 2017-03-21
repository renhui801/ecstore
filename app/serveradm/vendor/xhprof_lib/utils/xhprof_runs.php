<?php
//
//  Copyright (c) 2009 Facebook
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

//
// This file defines the interface iXHProfRuns and also provides a default
// implementation of the interface (class XHProfRuns).
//

/**
 * iXHProfRuns interface for getting/saving a XHProf run.
 *
 * Clients can either use the default implementation,
 * namely XHProfRuns_Default, of this interface or define
 * their own implementation.
 *
 * @author Kannan
 */
if(!defined("ECAE_MODE")) {
	if(defined("ECAE_SITE_NAME")) {
		define("ECAE_MODE",true);
	}
}

interface iXHProfRuns {

  /**
   * Returns XHProf data given a run id ($run) of a given
   * type ($type).
   *
   * Also, a brief description of the run is returned via the
   * $run_desc out parameter.
   */
  public function get_run($run_id, $type, &$run_desc);

  /**
   * Save XHProf data for a profiler run of specified type
   * ($type).
   *
   * The caller may optionally pass in run_id (which they
   * promise to be unique). If a run_id is not passed in,
   * the implementation of this method must generated a
   * unique run id for this saved XHProf run.
   *
   * Returns the run id for the saved XHProf run.
   *
   */
  public function save_run($xhprof_data, $type, $run_id = null);
}


/**
 * XHProfRuns_Default is the default implementation of the
 * iXHProfRuns interface for saving/fetching XHProf runs.
 *
 * It stores/retrieves runs to/from a filesystem directory
 * specified by the "xhprof.output_dir" ini parameter.
 *
 * @author Kannan
 */
class XHProfRuns_Default implements iXHProfRuns {

  private $dir = '';

  private function gen_run_id($type) {
    return uniqid();
  }

  private function file_name($run_id, $type) {

    $file = "$run_id.$type";

    if (!empty($this->dir)) {
      $file = $this->dir . "/" . $file;
    }
    return $file;
  }

  public function __construct($dir = null) {

    // if user hasn't passed a directory location,
    // we use the xhprof.output_dir ini setting
    // if specified, else we default to the directory
    // in which the error_log file resides.

    if (empty($dir)) {
      $dir = ini_get("xhprof.output_dir");
      if (empty($dir)) {

        // some default that at least works on unix...
        //$dir = "/tmp";
        $dir = sys_get_temp_dir();

        xhprof_error("Warning: Must specify directory location for XHProf runs. ".
                     "Trying {$dir} as default. You can either pass the " .
                     "directory location as an argument to the constructor ".
                     "for XHProfRuns_Default() or set xhprof.output_dir ".
                     "ini param.");
      }
    }
    $this->dir = $dir;
  }

  public function get_run($run_id, $type, &$run_desc) {
	  	if(defined("ECAE_MODE") && constant("ECAE_MODE")) {
			$file_name = tempnam(TMP_DIR,constant("ECAE_SITE_NAME")."-xhprof");
			if(!ecae_file_fetch($run_id,$file_name)) {
				xhprof_error("Could not find file $run_id from ECAE S3");
			  	$run_desc = "Invalid Run Id = $run_id";
				return null;
			}
		} else {
			$file_name = $this->file_name($run_id, $type);

			if (!file_exists($file_name)) {
			  xhprof_error("Could not find file $file_name");
			  $run_desc = "Invalid Run Id = $run_id";
			  return null;
			}
		}

		$contents = file_get_contents($file_name);
		$run_desc = "XHProf Run (Namespace=$type)";
		return unserialize($contents);
  }

  public function save_run($xhprof_data, $type, $run_id = null) {

    // Use PHP serialize function to store the XHProf's
    // raw profiler data.
    $xhprof_data = serialize($xhprof_data);

    if ($run_id === null) {
      $run_id = $this->gen_run_id($type);
    }
	
    $file_name = $this->file_name($run_id, $type);
    $file = fopen($file_name, 'w');
	
	if ($file) {
      fwrite($file, $xhprof_data);
      fclose($file);
    } else {
      xhprof_error("Could not open $file_name\n");
	}
	if(defined("ECAE_MODE") && constant("ECAE_MODE")) {
		// 如果是存放在ecae中返回的是ECAE S3存放id
		return ecae_file_save(
			constant("ECAE_SITE_NAME")."-private",
			$file_name,
			array(
				"path"=>"xphrof",
				"name"=>$run_id.".txt"
			)
		);
	}

    // echo "Saved run in {$file_name}.\nRun id = {$run_id}.\n";
    return $run_id;
  }
}
