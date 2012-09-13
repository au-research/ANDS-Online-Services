<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

********************************************************************************
 PRODUCTION
*******************************************************************************/

// COSI
$gCNN_DBS_COSI = false;

define("eCNN_DBS_COSI", "host=".$cosi_db_host." port=".$cosi_db_port." dbname=".$cosi_db_name." user=webuser connect_timeout=60");

// ORCA
$gCNN_DBS_ORCA = false;
define("eCNN_DBS_ORCA", "host=".$orca_db_host." port=".$orca_db_port." dbname=".$orca_db_name." user=webuser connect_timeout=60");

// PIDS
$gCNN_DBS_PIDS = false;
define("eCNN_DBS_PIDS", "host=".$pids_db_host." port=".$pids_db_port." dbname=".$pids_db_name." user=webuser connect_timeout=60");

// DOIS
$gCNN_DBS_DOIS = false;
define("eCNN_DBS_DOIS", "host=".$dois_db_host." port=".$dois_db_port." dbname=".$dois_db_name." user=webuser connect_timeout=60");
