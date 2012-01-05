<?php
/*
Copyright 2008 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*******************************************************************************/
// DOIS environment settings.
// -----------------------------------------------------------------------------

// Service configuration.
require 'dois_environment.php';

// Required files and application initialisation: order is important.
// -----------------------------------------------------------------------------
// Include environment settings.



openDatabaseConnection($gCNN_DBS_DOIS, eCNN_DBS_DOIS);

require '../../dois/_functions/dois_functions.php';
require '../../dois/_functions/dois_data_functions.php';
require '../../dois/_functions/doi_import_functions.php';
require '../../dois/_functions/doi_export_functions.php';
?>