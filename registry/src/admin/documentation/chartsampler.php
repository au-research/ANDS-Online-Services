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
*******************************************************************************/
// Include required files and initialisation.
require '../../_includes/init.php';
// Page processing
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>

<h1>Chart Sampler</h1>

<p>Here are example color schemes, styles, and markup for incorporating Google Charts into
COSI applications.</p>

<p>Refer to the <a href="http://code.google.com/apis/chart/">Google Chart API Developer's Guide</a> for details.</p>

<h2>Map</h2>
<p>
map background = f7f4f2<br />
none = e0e0e0<br />
lowest = f5e287<br />
middle = f4a618<br />
highest = db1f00<br />
</p>
<pre>...chco=e0e0e0,f5e287,f4a618,db1f00&amp;chf=bg,s,f7f4f2</pre>

<h3>Origin of Visitors by Country</h3>
<div class="chart" style="width:440px; height:220px;"><img alt="Origin of Visitors by Country"  src="http://chart.apis.google.com/chart?chs=440x220&amp;cht=t&amp;chco=e0e0e0,f5e287,f4a618,db1f00&amp;chf=bg,s,f7f4f2&amp;chld=BRAUUSCAMXRUCNDEEUJPGBFRDKTWEGSAKRITDOHU&amp;chd=s:9ZzAaMm0CCBABBazaBBm&amp;chtm=world" />
</div>

<h2>Pie Chart</h2>
<p>
lowest = f5e287<br />
middle = f4a618<br />
highest = db1f00<br />
</p>
<pre>...chco=db1f00,f4a618,f5e287&amp;chf=bg,s,ffffff00</pre>

<h3>Total Items by File Format</h3>
<div class="chart" style="width:700px; height:250px;"><img alt="Total Items by File Format"  src="http://chart.apis.google.com/chart?chs=700x250&amp;cht=p3&amp;chco=db1f00,f4a618,f5e287&amp;chf=bg,s,ffffff00&amp;chl=image/jpeg / 42|text/xml / 28|application/pdf / 24|application/msword / 6|image/gif / 6|text/html / 4|text/plain / 2&amp;chd=t:37.5,25.0,21.4,5.4,5.4,3.6,1.8" /></div>

<h2>Bar Chart</h2>
<p>Ten colours per group repeated in order.</p>
<pre>...chco=ee7f2d,0b9ff0,f4a618,bbaf0b,d3420b,c75a0b,0b72ac,af7714,92860b,852d0b&amp;chf=bg,s,ffffff00</pre>
<h3>Total Items Viewed</h3>
<div class="chart" style="width:801px; height:258px"><img alt="Total Items Viewed"  src="http://chart.apis.google.com/chart?chs=793x250&amp;cht=bvg&amp;chco=ee7f2d,0b9ff0,f4a618,bbaf0b,d3420b,c75a0b,0b72ac,af7714,92860b,852d0b&amp;chxl=0:|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|Jan|Feb|Mar|Apr|May|1:|0|44|87&amp;chd=s:FKPOSLPLRSPun9,HINLILNIPGIjdn,DINNLHLKNIHaSU,EKGKHGGJHLOVaf,HOPPIMLNNPNrni&amp;chf=bg,s,ffffff00&amp;chxt=x,y&amp;chbh=10,0,5" /></div>



<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
