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

<h1>Style Sampler</h1>

<h1>&lt;h1&gt;Heading Level 1&lt;/h1&gt;</h1>

<h2>&lt;h2&gt;Heading Level 2&lt;/h2&gt;</h2>

<h3>&lt;h3&gt;Heading Level 3&lt;/h3&gt;</h3>

<h4>&lt;h4&gt;Heading Level 4&lt;/h4&gt;</h4>

<h5>&lt;h5&gt;Heading Level 5&lt;/h5&gt;</h5>

<h6>&lt;h6&gt;Heading Level 6&lt;/h6&gt;</h6>

&lt;hr /&gt;<hr />

<p>&lt;p&gt;Lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum
dolor lorem ipsum dolor lorem ipsumdolor lorem ipsumdolor lorem ipsum
dolor lorem ipsum doloripsum.&lt;br /&gt;<br />

<a href="http://www.apsr.edu.au">Hyperlink to apsr web page</a><br />

Lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum

dolor lorem ipsum dolor lorem ipsumdolor lorem ipsumdolor lorem ipsum
dolor lorem ipsum doloripsum.&lt;br /&gt;<br />
&lt;/p&gt;</p>

<table summary="Data Table Title">
	<caption>Figure 1. Example Data Table</caption>
	<thead>
		<tr>
			<td colspan="6">Data Table Title</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>Heading A</th>
			<th>Heading B</th>
			<th>Heading C</th>
			<th>Heading D</th>
			<th>Heading E</th>
			<th>Heading F</th>
		</tr>
		<tr>
			<td>1A</td>
			<td>1B</td>
			<td>1C</td>
			<td>1D</td>
			<td>1E</td>
			<td>1F</td>
		</tr>
		<tr>
			<td>2A</td>
			<td>2B</td>
			<td>2C</td>
			<td>2D</td>
			<td>2E</td>
			<td>2F</td>
		</tr>
		<tr>
			<td>3A</td>
			<td>3B</td>
			<td>3C</td>
			<td>3D</td>
			<td>3E</td>
			<td>3F</td>
		</tr>
		<tr>
			<td>4A</td>
			<td>4B</td>
			<td>4C</td>
			<td>4D</td>
			<td>4E</td>
			<td>4F</td>
		</tr>
		<tr>
			<td>5A</td>
			<td>5B</td>
			<td>5C</td>
			<td>5D</td>
			<td>5E</td>
			<td>5F</td>
		</tr>
	</tbody>
</table>

<br />
<table class="rowNumbers" summary="Data Table Title">
	<caption>Figure 2. Example Data Table class="rowNumbers"</caption>
	<thead>
		<tr>
			<td></td>
			<td colspan="6">Data Table Title</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th></th>
			<th>Heading A</th>
			<th>Heading B</th>
			<th>Heading C</th>
			<th>Heading D</th>
			<th>Heading E</th>
			<th>Heading F</th>
		</tr>
		<tr>
			<td>1</td>
			<td>1A</td>
			<td>1B</td>
			<td>1C</td>
			<td>1D</td>
			<td>1E</td>
			<td>1F</td>
		</tr>
		<tr>
			<td>2</td>
			<td>2A</td>
			<td>2B</td>
			<td>2C</td>
			<td>2D</td>
			<td>2E</td>
			<td>2F</td>
		</tr>
		<tr>
			<td>3</td>
			<td>3A</td>
			<td>3B</td>
			<td>3C</td>
			<td>3D</td>
			<td>3E</td>
			<td>3F</td>
		</tr>
		<tr>
			<td>4</td>
			<td>4A</td>
			<td>4B</td>
			<td>4C</td>
			<td>4D</td>
			<td>4E</td>
			<td>4F</td>
		</tr>
		<tr>
			<td>5</td>
			<td>5A</td>
			<td>5B</td>
			<td>5C</td>
			<td>5D</td>
			<td>5E</td>
			<td>5F</td>
		</tr>
	</tbody>
</table>

<p>&lt;p&gt;Lorem ipsum dolor lorem Lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum
dolor lorem dolor <b>&lt;b&gt;lorem ipsum dolor&lt;/b&gt;</b> lorem ipsum dolor lorem ipsum
dolor lorem ipsum dolor lorem <i>&lt;i&gt;ipsumdolor&lt;/i&gt;</i> lorem ipsumdolor lorem ipsum
dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum.&lt;/p&gt;</p>

<p>&lt;p&gt;Lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum
dolor lorem ipsum dolor <a href="">&lt;a href=""&gt;lorem ipsum dolor&lt;/a&gt;</a> ipsumdolor lorem ipsum
dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum.&lt;/p&gt;</p>

<p>&lt;p&gt;Lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum
dolor lorem <span class="pre">&lt;span class="pre"&gt;&lt;script
type="text/javascript"&gt;alert("Hello World!");&lt;/script&gt;&lt;/span&gt;</span>

dolor sumdolor lorem ipsumdolor lorem ipsum
dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum.&lt;/p&gt;</p>

&lt;ul&gt;<ul>
    <li>Item in list</li>
    <li>Item in list</li>
    <li>Item in list</li>
    <li>Item in list</li>

    <li>Item in list</li>
    <li>Item in list</li>
    <li>Item in list</li>
</ul>&lt;\ul&gt;
<br />
&lt;ol&gt;<ol>
    <li>Item in list</li>

    <li>Item in list</li>
    <li>Item in list</li>
    <li>Item in list</li>
    <li>Item in list</li>
    <li>Item in list</li>
    <li>Item in list</li>

</ol>&lt;\ol&gt;

<pre>&lt;pre&gt;
<?php
$preText = <<<EOF
var OctetArray;
var b1, b2, b3, b4;
var intIP = -1;

OctetArray = strIPAddress.split(".");

if( OctetArray.length == 4 ){

    b1 = parseInt(OctetArray[0], 10);
    b2 = parseInt(OctetArray[1], 10);
    b3 = parseInt(OctetArray[2], 10);
    b4 = parseInt(OctetArray[3], 10);

    if( isNaN(b1) || b1 < 0 || b1 > 0xFF ){ return -1; }
    if( isNaN(b2) || b2 < 0 || b2 > 0xFF ){ return -1; }
    if( isNaN(b3) || b3 < 0 || b3 > 0xFF ){ return -1; }
    if( isNaN(b4) || b4 < 0 || b4 > 0xFF ){ return -1; }

    b1 = (b1 << 0x18) & 0xFF000000;
    b2 = (b2 << 0x10) & 0x00FF0000;
    b3 = (b3 << 0x08) & 0x0000FF00;
    b4 = (b4 << 0x00) & 0x000000FF;

    intIP = b1 | b2 | b3 | b4;
}
EOF;

printSafe($preText);
?>

&lt;/pre&gt;</pre>

<p>&lt;p&gt;Lorem ipsum dolor lorem Lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum
dolor lorem dolor <b>&lt;b&gt;lorem ipsum dolor&lt;/b&gt;</b> lorem ipsum dolor lorem ipsum
dolor. Lorem ipsum dolor lorem <i>&lt;i&gt;ipsumdolor&lt;/i&gt;</i> lorem ipsumdolor lorem ipsum
dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum.&lt;/p&gt;</p>

<p class="caption">&lt;p class="caption"&gt;Information suitable for the caption style
lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor
lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor
lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor lorem ipsum dolor.&lt;/p&gt;</p>


<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
