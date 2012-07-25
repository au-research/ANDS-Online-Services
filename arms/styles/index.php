<?php include('header.php');?>

<div class="container-fluid" id="main-content">
<section>
    <div class="page-header">
        <h1>Typography <small>Headings, paragraph</small></h1>
    </div>
    <div class="row-fluid">
        <div class="span6">
            <p>Lorem ipsum <strong>Excepteur</strong> eu <em>labore reprehenderit</em> anim <a href="#">quis occaecat</a> esse eu amet elit sit laboris ad consectetur occaecat ad nostrud reprehenderit et elit veniam ut nulla minim ut minim ea deserunt ex ea consectetur veniam est ad tempor amet elit ut sed ut voluptate tempor nulla cillum in ut occaecat qui est adipisicing dolore mollit eu proident eiusmod dolore magna laborum in veniam nisi aliqua culpa Ut labore consectetur eu dolore dolor cupidatat esse non in reprehenderit reprehenderit in quis do aliquip pariatur pariatur qui cillum et eiusmod consequat sunt in Duis tempor consectetur dolor elit officia velit mollit magna occaecat minim adipisicing magna Ut tempor eu pariatur exercitation occaecat sit culpa eu eiusmod Ut ex esse officia nostrud tempor ut adipisicing Ut exercitation id culpa eu Excepteur amet consequat consectetur dolor et in aliquip culpa et officia sint aliqua est veniam Duis ea id minim ut culpa Duis aute in incididunt et dolore aliquip dolore magna aliqua cillum minim elit sit officia sint ullamco cillum veniam sed officia esse labore qui dolore deserunt non magna do qui pariatur magna sed nostrud nostrud exercitation nulla quis nisi elit in id esse commodo magna consequat in ex dolor exercitation sunt Ut ut fugiat aliqua in commodo aute et commodo id dolore pariatur est officia sint anim sed consectetur eiusmod dolore qui aute sed culpa ut sunt dolore voluptate veniam minim adipisicing nisi dolore. </p>
        </div>
        <div class="span6">
            <div class="well">
                <h1>h1. Heading 1</h1>
                <h2>h2. Heading 2</h2>
                <h3>h3. Heading 3</h3>
                <h4>h4. Heading 4</h4>
                <h5>h5. Heading 5</h5>
                <h6>h6. Heading 6</h6>
              </div>
        </div>
    </div>
    <hr/>
    <div class="row-fluid">
    <div class="span4">
      <h3>Unordered</h3>
      <p><code>&lt;ul&gt;</code></p>
      <ul>
        <li>Lorem ipsum dolor sit amet</li>
        <li>Consectetur adipiscing elit</li>
        <li>Integer molestie lorem at massa</li>
        <li>Facilisis in pretium nisl aliquet</li>
        <li>Nulla volutpat aliquam velit
          <ul>
            <li>Phasellus iaculis neque</li>
            <li>Purus sodales ultricies</li>
            <li>Vestibulum laoreet porttitor sem</li>
            <li>Ac tristique libero volutpat at</li>
          </ul>
        </li>
        <li>Faucibus porta lacus fringilla vel</li>
        <li>Aenean sit amet erat nunc</li>
        <li>Eget porttitor lorem</li>
      </ul>
    </div>
    <div class="span4">
      <h3>Unstyled</h3>
      <p><code>&lt;ul class="unstyled"&gt;</code></p>
      <ul class="unstyled">
        <li>Lorem ipsum dolor sit amet</li>
        <li>Consectetur adipiscing elit</li>
        <li>Integer molestie lorem at massa</li>
        <li>Facilisis in pretium nisl aliquet</li>
        <li>Nulla volutpat aliquam velit
          <ul>
            <li>Phasellus iaculis neque</li>
            <li>Purus sodales ultricies</li>
            <li>Vestibulum laoreet porttitor sem</li>
            <li>Ac tristique libero volutpat at</li>
          </ul>
        </li>
        <li>Faucibus porta lacus fringilla vel</li>
        <li>Aenean sit amet erat nunc</li>
        <li>Eget porttitor lorem</li>
      </ul>
    </div>
    <div class="span4">
      <h3>Ordered</h3>
      <p><code>&lt;ol&gt;</code></p>
      <ol>
        <li>Lorem ipsum dolor sit amet</li>
        <li>Consectetur adipiscing elit</li>
        <li>Integer molestie lorem at massa</li>
        <li>Facilisis in pretium nisl aliquet</li>
        <li>Nulla volutpat aliquam velit</li>
        <li>Faucibus porta lacus fringilla vel</li>
        <li>Aenean sit amet erat nunc</li>
        <li>Eget porttitor lorem</li>
      </ol>
    </div>
  </div>
 <hr/>
  <div class="row-fluid">
    <div class="span4">
      <h3>Description</h3>
      <p><code>&lt;dl&gt;</code></p>
      <dl>
        <dt>Description lists</dt>
        <dd>A description list is perfect for defining terms.</dd>
        <dt>Euismod</dt>
        <dd>Vestibulum id ligula porta felis euismod semper eget lacinia odio sem nec elit.</dd>
        <dd>Donec id elit non mi porta gravida at eget metus.</dd>
        <dt>Malesuada porta</dt>
        <dd>Etiam porta sem malesuada magna mollis euismod.</dd>
      </dl>
    </div>
    <div class="span8">
      <h3>Horizontal description</h3>
      <p><code>&lt;dl class="dl-horizontal"&gt;</code></p>
      <dl class="dl-horizontal">
        <dt>Description lists</dt>
        <dd>A description list is perfect for defining terms.</dd>
        <dt>Euismod</dt>
        <dd>Vestibulum id ligula porta felis euismod semper eget lacinia odio sem nec elit.</dd>
        <dd>Donec id elit non mi porta gravida at eget metus.</dd>
        <dt>Malesuada porta</dt>
        <dd>Etiam porta sem malesuada magna mollis euismod.</dd>
        <dt>Felis euismod semper eget lacinia</dt>
        <dd>Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</dd>
      </dl>
      <hr>
      <p>
        <span class="label label-info">Heads up!</span>
        Horizontal description lists will truncate terms that are too long to fit in the left column fix <code>text-overflow</code>. In narrower viewports, they will change to the default stacked layout.
      </p>
    </div>
  </div>
    
</section>


<section id="tables">
  <div class="page-header">
    <h1>Tables <small>For, you guessed it, tabular data</small></h1>
  </div>

  <h2>Table markup</h2>
  <div class="row-fluid">
    <div class="span8">
      <table class="table table-bordered table-striped">
        <colgroup>
          <col class="span1">
          <col class="span7">
        </colgroup>
        <thead>
          <tr>
            <th>Tag</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <code>&lt;table&gt;</code>
            </td>
            <td>
              Wrapping element for displaying data in a tabular format
            </td>
          </tr>
          <tr>
            <td>
              <code>&lt;thead&gt;</code>
            </td>
            <td>
              Container element for table header rows (<code>&lt;tr&gt;</code>) to label table columns
            </td>
          </tr>
          <tr>
            <td>
              <code>&lt;tbody&gt;</code>
            </td>
            <td>
              Container element for table rows (<code>&lt;tr&gt;</code>) in the body of the table
            </td>
          </tr>
          <tr>
            <td>
              <code>&lt;tr&gt;</code>
            </td>
            <td>
              Container element for a set of table cells (<code>&lt;td&gt;</code> or <code>&lt;th&gt;</code>) that appears on a single row
            </td>
          </tr>
          <tr>
            <td>
              <code>&lt;td&gt;</code>
            </td>
            <td>
              Default table cell
            </td>
          </tr>
          <tr>
            <td>
              <code>&lt;th&gt;</code>
            </td>
            <td>
              Special table cell for column (or row, depending on scope and placement) labels<br>
              Must be used within a <code>&lt;thead&gt;</code>
            </td>
          </tr>
          <tr>
            <td>
              <code>&lt;caption&gt;</code>
            </td>
            <td>
              Description or summary of what the table holds, especially useful for screen readers
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="span4">
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;table&gt;</span></li><li class="L1"><span class="pln">  </span><span class="tag">&lt;thead&gt;</span></li><li class="L2"><span class="pln">    </span><span class="tag">&lt;tr&gt;</span></li><li class="L3"><span class="pln">      </span><span class="tag">&lt;th&gt;</span><span class="pln">…</span><span class="tag">&lt;/th&gt;</span></li><li class="L4"><span class="pln">      </span><span class="tag">&lt;th&gt;</span><span class="pln">…</span><span class="tag">&lt;/th&gt;</span></li><li class="L5"><span class="pln">    </span><span class="tag">&lt;/tr&gt;</span></li><li class="L6"><span class="pln">  </span><span class="tag">&lt;/thead&gt;</span></li><li class="L7"><span class="pln">  </span><span class="tag">&lt;tbody&gt;</span></li><li class="L8"><span class="pln">    </span><span class="tag">&lt;tr&gt;</span></li><li class="L9"><span class="pln">      </span><span class="tag">&lt;td&gt;</span><span class="pln">…</span><span class="tag">&lt;/td&gt;</span></li><li class="L0"><span class="pln">      </span><span class="tag">&lt;td&gt;</span><span class="pln">…</span><span class="tag">&lt;/td&gt;</span></li><li class="L1"><span class="pln">    </span><span class="tag">&lt;/tr&gt;</span></li><li class="L2"><span class="pln">  </span><span class="tag">&lt;/tbody&gt;</span></li><li class="L3"><span class="tag">&lt;/table&gt;</span></li></ol></pre>
    </div>
  </div>

  <h2>Table options</h2>
  <table class="table table-bordered table-striped">
  <thead>
      <tr>
        <th>Name</th>
        <th>Class</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Default</td>
        <td class="muted">None</td>
        <td>No styles, just columns and rows</td>
      </tr>
      <tr>
        <td>Basic</td>
        <td>
          <code>.table</code>
        </td>
        <td>Only horizontal lines between rows</td>
      </tr>
      <tr>
        <td>Bordered</td>
        <td>
          <code>.table-bordered</code>
        </td>
        <td>Rounds corners and adds outer border</td>
      </tr>
      <tr>
        <td>Zebra-stripe</td>
        <td>
          <code>.table-striped</code>
        </td>
        <td>Adds light gray background color to odd rows (1, 3, 5, etc)</td>
      </tr>
      <tr>
        <td>Condensed</td>
        <td>
          <code>.table-condensed</code>
        </td>
        <td>Cuts vertical padding in half, from 8px to 4px, within all <code>td</code> and <code>th</code> elements</td>
      </tr>
    </tbody>
  </table>


  <h2>Example tables</h2>

  <h3>1. Default table styles</h3>
  <div class="row-fluid">
    <div class="span4">
      <p>Tables are automatically styled with only a few borders to ensure readability and maintain structure. With 2.0, the <code>.table</code> class is required.</p>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;table</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"table"</span><span class="tag">&gt;</span></li><li class="L1"><span class="pln">  …</span></li><li class="L2"><span class="tag">&lt;/table&gt;</span></li></ol></pre>
    </div>
    <div class="span8">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Username</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
          </tr>
          <tr>
            <td>2</td>
            <td>Jacob</td>
            <td>Thornton</td>
            <td>@fat</td>
          </tr>
          <tr>
            <td>3</td>
            <td>Larry</td>
            <td>the Bird</td>
            <td>@twitter</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>


  <h3>2. Striped table</h3>
  <div class="row-fluid">
    <div class="span4">
      <p>Get a little fancy with your tables by adding zebra-striping—just add the <code>.table-striped</code> class.</p>
      <p class="muted"><strong>Note:</strong> Striped tables use the <code>:nth-child</code> CSS selector and is not available in IE7-IE8.</p>
<pre class="prettyprint linenums" style="margin-bottom: 18px;"><ol class="linenums"><li class="L0"><span class="tag">&lt;table</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"table table-striped"</span><span class="tag">&gt;</span></li><li class="L1"><span class="pln">  …</span></li><li class="L2"><span class="tag">&lt;/table&gt;</span></li></ol></pre>
    </div>
    <div class="span8">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Username</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
          </tr>
          <tr>
            <td>2</td>
            <td>Jacob</td>
            <td>Thornton</td>
            <td>@fat</td>
          </tr>
          <tr>
            <td>3</td>
            <td>Larry</td>
            <td>the Bird</td>
            <td>@twitter</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>


  <h3>3. Bordered table</h3>
  <div class="row-fluid">
    <div class="span4">
      <p>Add borders around the entire table and rounded corners for aesthetic purposes.</p>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;table</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"table table-bordered"</span><span class="tag">&gt;</span></li><li class="L1"><span class="pln">  …</span></li><li class="L2"><span class="tag">&lt;/table&gt;</span></li></ol></pre>
    </div>
    <div class="span8">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Username</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td rowspan="2">1</td>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
          </tr>
          <tr>
            <td>Mark</td>
            <td>Otto</td>
            <td>@TwBootstrap</td>
          </tr>
          <tr>
            <td>2</td>
            <td>Jacob</td>
            <td>Thornton</td>
            <td>@fat</td>
          </tr>
          <tr>
            <td>3</td>
            <td colspan="2">Larry the Bird</td>
            <td>@twitter</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>


  <h3>4. Condensed table</h3>
  <div class="row-fluid">
    <div class="span4">
      <p>Make your tables more compact by adding the <code>.table-condensed</code> class to cut table cell padding in half (from 8px to 4px).</p>
<pre class="prettyprint linenums" style="margin-bottom: 18px;"><ol class="linenums"><li class="L0"><span class="tag">&lt;table</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"table table-condensed"</span><span class="tag">&gt;</span></li><li class="L1"><span class="pln">  …</span></li><li class="L2"><span class="tag">&lt;/table&gt;</span></li></ol></pre>
    </div>
    <div class="span8">
      <table class="table table-condensed">
        <thead>
          <tr>
            <th>#</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Username</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
          </tr>
          <tr>
            <td>2</td>
            <td>Jacob</td>
            <td>Thornton</td>
            <td>@fat</td>
          </tr>
          <tr>
            <td>3</td>
            <td colspan="2">Larry the Bird</td>
            <td>@twitter</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>



  <h3>5. Combine them all!</h3>
  <div class="row-fluid">
    <div class="span4">
      <p>Feel free to combine any of the table classes to achieve different looks by utilizing any of the available classes.</p>
<pre class="prettyprint linenums" style="margin-bottom: 18px;"><ol class="linenums"><li class="L0"><span class="tag">&lt;table</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"table table-striped table-bordered table-condensed"</span><span class="tag">&gt;</span></li><li class="L1"><span class="pln">  ...</span></li><li class="L2"><span class="tag">&lt;/table&gt;</span></li></ol></pre>
    </div>
    <div class="span8">
      <table class="table table-striped table-bordered table-condensed">
        <thead>
          <tr>
            <th></th>
            <th colspan="2">Full name</th>
            <th></th>
          </tr>
          <tr>
            <th>#</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Username</th>
          </tr>
        </thead>
        <tbody>
          <tr>
          </tr><tr>
            <td>1</td>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
          </tr>
          <tr>
            <td>2</td>
            <td>Jacob</td>
            <td>Thornton</td>
            <td>@fat</td>
          </tr>
          <tr>
            <td>3</td>
            <td colspan="2">Larry the Bird</td>
            <td>@twitter</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>


<section id="forms">
  <div class="page-header">
    <h1>Forms</h1>
  </div>
  <div class="row-fluid">
    <div class="span4">
      <h2>Flexible HTML and CSS</h2>
      <p>The best part about forms in Bootstrap is that all your inputs and controls look great no matter how you build them in your markup. No superfluous HTML is required, but we provide the patterns for those who require it.</p>
      <p>More complicated layouts come with succinct and scalable classes for easy styling and event binding, so you're covered at every step.</p>
    </div>
    <div class="span4">
      <h2>Four layouts included</h2>
      <p>Bootstrap comes with support for four types of form layouts:</p>
      <ul>
        <li>Vertical (default)</li>
        <li>Search</li>
        <li>Inline</li>
        <li>Horizontal</li>
      </ul>
      <p>Different types of form layouts require some changes to markup, but the controls themselves remain and behave the same.</p>
    </div>
    <div class="span4">
      <h2>Control states and more</h2>
      <p>Bootstrap's forms include styles for all the base form controls like input, textarea, and select you'd expect. But it also comes with a number of custom components like appended and prepended inputs and support for lists of checkboxes.</p>
      <p>States like error, warning, and success are included for each type of form control. Also included are styles for disabled controls.</p>
    </div>
  </div>

  <h2>Four types of forms</h2>
  <p>Bootstrap provides simple markup and styles for four styles of common web forms.</p>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Class</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Vertical (default)</th>
        <td><code>.form-vertical</code> <span class="muted">(not required)</span></td>
        <td>Stacked, left-aligned labels over controls</td>
      </tr>
      <tr>
        <th>Inline</th>
        <td><code>.form-inline</code></td>
        <td>Left-aligned label and inline-block controls for compact style</td>
      </tr>
      <tr>
        <th>Search</th>
        <td><code>.form-search</code></td>
        <td>Extra-rounded text input for a typical search aesthetic</td>
      </tr>
      <tr>
        <th>Horizontal</th>
        <td><code>.form-horizontal</code></td>
        <td>Float left, right-aligned labels on same line as controls</td>
      </tr>
    </tbody>
  </table>


  <h2>Example forms <small>using just form controls, no extra markup</small></h2>
  <div class="row-fluid">
    <div class="span6">
      <h3>Basic form</h3>
      <p>Smart and lightweight defaults without extra markup.</p>
      <form class="well">
        <label>Label name</label>
        <input type="text" class="span3" placeholder="Type something…">
        <p class="help-block">Example block-level help text here.</p>
        <label class="checkbox">
          <input type="checkbox"> Check me out
        </label>
        <button type="submit" class="btn">Submit</button>
      </form>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;form</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"well"</span><span class="tag">&gt;</span></li><li class="L1"><span class="pln">  </span><span class="tag">&lt;label&gt;</span><span class="pln">Label name</span><span class="tag">&lt;/label&gt;</span></li><li class="L2"><span class="pln">  </span><span class="tag">&lt;input</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"text"</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"span3"</span><span class="pln"> </span><span class="atn">placeholder</span><span class="pun">=</span><span class="atv">"Type something…"</span><span class="tag">&gt;</span></li><li class="L3"><span class="pln">  </span><span class="tag">&lt;span</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"help-block"</span><span class="tag">&gt;</span><span class="pln">Example block-level help text here.</span><span class="tag">&lt;/span&gt;</span></li><li class="L4"><span class="pln">  </span><span class="tag">&lt;label</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"checkbox"</span><span class="tag">&gt;</span></li><li class="L5"><span class="pln">    </span><span class="tag">&lt;input</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"checkbox"</span><span class="tag">&gt;</span><span class="pln"> Check me out</span></li><li class="L6"><span class="pln">  </span><span class="tag">&lt;/label&gt;</span></li><li class="L7"><span class="pln">  </span><span class="tag">&lt;button</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"submit"</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"btn"</span><span class="tag">&gt;</span><span class="pln">Submit</span><span class="tag">&lt;/button&gt;</span></li><li class="L8"><span class="tag">&lt;/form&gt;</span></li></ol></pre>
  </div>
  <div class="span6">
    <h3>Search form</h3>
    <p>Add <code>.form-search</code> to the form and <code>.search-query</code> to the <code>input</code>.</p>
    <form class="well form-search">
      <input type="text" class="input-medium search-query">
      <button type="submit" class="btn">Search</button>
    </form>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;form</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"well form-search"</span><span class="tag">&gt;</span></li><li class="L1"><span class="pln">  </span><span class="tag">&lt;input</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"text"</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"input-medium search-query"</span><span class="tag">&gt;</span></li><li class="L2"><span class="pln">  </span><span class="tag">&lt;button</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"submit"</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"btn"</span><span class="tag">&gt;</span><span class="pln">Search</span><span class="tag">&lt;/button&gt;</span></li><li class="L3"><span class="tag">&lt;/form&gt;</span></li></ol></pre>

      <h3>Inline form</h3>
      <p>Add <code>.form-inline</code> to finesse the vertical alignment and spacing of form controls.</p>
      <form class="well form-inline">
        <input type="text" class="input-small" placeholder="Email">
        <input type="password" class="input-small" placeholder="Password">
        <label class="checkbox">
          <input type="checkbox"> Remember me
        </label>
        <button type="submit" class="btn">Sign in</button>
      </form>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;form</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"well form-inline"</span><span class="tag">&gt;</span></li><li class="L1"><span class="pln">  </span><span class="tag">&lt;input</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"text"</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"input-small"</span><span class="pln"> </span><span class="atn">placeholder</span><span class="pun">=</span><span class="atv">"Email"</span><span class="tag">&gt;</span></li><li class="L2"><span class="pln">  </span><span class="tag">&lt;input</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"password"</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"input-small"</span><span class="pln"> </span><span class="atn">placeholder</span><span class="pun">=</span><span class="atv">"Password"</span><span class="tag">&gt;</span></li><li class="L3"><span class="pln">  </span><span class="tag">&lt;label</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"checkbox"</span><span class="tag">&gt;</span></li><li class="L4"><span class="pln">    </span><span class="tag">&lt;input</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"checkbox"</span><span class="tag">&gt;</span><span class="pln"> Remember me</span></li><li class="L5"><span class="pln">  </span><span class="tag">&lt;/label&gt;</span></li><li class="L6"><span class="pln">  </span><span class="tag">&lt;button</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"submit"</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"btn"</span><span class="tag">&gt;</span><span class="pln">Sign in</span><span class="tag">&lt;/button&gt;</span></li><li class="L7"><span class="tag">&lt;/form&gt;</span></li></ol></pre>
    </div><!-- /.span -->
  </div><!-- /row -->

  <br>

  <h2>Horizontal forms</h2>
  <div class="row-fluid">
    <div class="span4">
      <p></p>
      <p>Shown on the right are all the default form controls we support. Here's the bulleted list:</p>
      <ul>
        <li>text inputs (text, password, email, etc)</li>
        <li>checkbox</li>
        <li>radio</li>
        <li>select</li>
        <li>multiple select</li>
        <li>file input</li>
        <li>textarea</li>
      </ul>
    </div><!-- /.span -->
    <div class="span8">
      <form class="form-horizontal">
        <fieldset>
          <div class="control-group">
            <label class="control-label" for="input01">Text input</label>
            <div class="controls">
              <input type="text" class="input-xlarge" id="input01">
              <p class="help-block">In addition to freeform text, any HTML5 text-based input appears like so.</p>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="optionsCheckbox">Checkbox</label>
            <div class="controls">
              <label class="checkbox">
                <input type="checkbox" id="optionsCheckbox" value="option1">
                Option one is this and that—be sure to include why it's great
              </label>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="select01">Select list</label>
            <div class="controls">
              <select id="select01">
                <option>something</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
              </select>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="multiSelect">Multicon-select</label>
            <div class="controls">
              <select multiple="multiple" id="multiSelect">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
              </select>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="fileInput">File input</label>
            <div class="controls">
              <input class="input-file" id="fileInput" type="file">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="textarea">Textarea</label>
            <div class="controls">
              <textarea class="input-xlarge" id="textarea" rows="3"></textarea>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <button class="btn">Cancel</button>
          </div>
        </fieldset>
      </form>
      <h3>Example markup</h3>
      <p>Given the above example form layout, here's the markup associated with the first input and control group. The <code>.control-group</code>, <code>.control-label</code>, and <code>.controls</code> classes are all required for styling.</p>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;form</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"form-horizontal"</span><span class="tag">&gt;</span></li><li class="L1"><span class="pln">  </span><span class="tag">&lt;fieldset&gt;</span></li><li class="L2"><span class="pln">    </span><span class="tag">&lt;legend&gt;</span><span class="pln">Legend text</span><span class="tag">&lt;/legend&gt;</span></li><li class="L3"><span class="pln">    </span><span class="tag">&lt;div</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"control-group"</span><span class="tag">&gt;</span></li><li class="L4"><span class="pln">      </span><span class="tag">&lt;label</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"control-label"</span><span class="pln"> </span><span class="atn">for</span><span class="pun">=</span><span class="atv">"input01"</span><span class="tag">&gt;</span><span class="pln">Text input</span><span class="tag">&lt;/label&gt;</span></li><li class="L5"><span class="pln">      </span><span class="tag">&lt;div</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"controls"</span><span class="tag">&gt;</span></li><li class="L6"><span class="pln">        </span><span class="tag">&lt;input</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"text"</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"input-xlarge"</span><span class="pln"> </span><span class="atn">id</span><span class="pun">=</span><span class="atv">"input01"</span><span class="tag">&gt;</span></li><li class="L7"><span class="pln">        </span><span class="tag">&lt;p</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"help-block"</span><span class="tag">&gt;</span><span class="pln">Supporting help text</span><span class="tag">&lt;/p&gt;</span></li><li class="L8"><span class="pln">      </span><span class="tag">&lt;/div&gt;</span></li><li class="L9"><span class="pln">    </span><span class="tag">&lt;/div&gt;</span></li><li class="L0"><span class="pln">  </span><span class="tag">&lt;/fieldset&gt;</span></li><li class="L1"><span class="tag">&lt;/form&gt;</span></li></ol></pre>
    </div>
  </div>

  <br>

  <h2>Form control states</h2>
  <div class="row-fluid">
    <div class="span4">
      <p>Bootstrap features styles for browser-supported focused and <code>disabled</code> states. We remove the default Webkit <code>outline</code> and apply a <code>box-shadow</code> in its place for <code>:focus</code>.</p>
      <hr>
      <h3>Form validation</h3>
      <p>It also includes validation styles for errors, warnings, and success. To use, add the error class to the surrounding <code>.control-group</code>.</p>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;fieldset</span></li><li class="L1"><span class="pln">  </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"control-group error"</span><span class="tag">&gt;</span></li><li class="L2"><span class="pln">  …</span></li><li class="L3"><span class="tag">&lt;/fieldset&gt;</span></li></ol></pre>
    </div>
    <div class="span8">
      <form class="form-horizontal">
        <fieldset>
          <div class="control-group">
            <label class="control-label" for="focusedInput">Focused input</label>
            <div class="controls">
              <input class="input-xlarge focused" id="focusedInput" type="text" value="This is focused…">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">Uneditable input</label>
            <div class="controls">
              <span class="input-xlarge uneditable-input">Some value here</span>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="disabledInput">Disabled input</label>
            <div class="controls">
              <input class="input-xlarge disabled" id="disabledInput" type="text" placeholder="Disabled input here…" disabled="">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="optionsCheckbox2">Disabled checkbox</label>
            <div class="controls">
              <label class="checkbox">
                <input type="checkbox" id="optionsCheckbox2" value="option1" disabled="">
                This is a disabled checkbox
              </label>
            </div>
          </div>
          <div class="control-group warning">
            <label class="control-label" for="inputWarning">Input with warning</label>
            <div class="controls">
              <input type="text" id="inputWarning">
              <span class="help-inline">Something may have gone wrong</span>
            </div>
          </div>
          <div class="control-group error">
            <label class="control-label" for="inputError">Input with error</label>
            <div class="controls">
              <input type="text" id="inputError">
              <span class="help-inline">Please correct the error</span>
            </div>
          </div>
          <div class="control-group success">
            <label class="control-label" for="inputSuccess">Input with success</label>
            <div class="controls">
              <input type="text" id="inputSuccess">
              <span class="help-inline">Woohoo!</span>
            </div>
          </div>
          <div class="control-group success">
            <label class="control-label" for="selectError">Select with success</label>
            <div class="controls">
              <select id="selectError">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
              </select>
              <span class="help-inline">Woohoo!</span>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <button class="btn">Cancel</button>
          </div>
        </fieldset>
      </form>
    </div>
  </div>

  <br>

  <h2>Extending form controls</h2>
  <div class="row-fluid">
    <div class="span4">
      <h3>Prepend &amp; append inputs</h3>
      <p>Input groups—with appended or prepended text—provide an easy way to give more context for your inputs. Great examples include the @ sign for Twitter usernames or $ for finances.</p>
      <hr>
      <h3>Checkboxes and radios</h3>
      <p>Up to v1.4, Bootstrap required extra markup around checkboxes and radios to stack them. Now, it's a simple matter of repeating the <code>&lt;label class="checkbox"&gt;</code> that wraps the <code>&lt;input type="checkbox"&gt;</code>.</p>
      <p>Inline checkboxes and radios are also supported. Just add <code>.inline</code> to any <code>.checkbox</code> or <code>.radio</code> and you're done.</p>
      <hr>
      <h4>Inline forms and append/prepend</h4>
      <p>To use prepend or append inputs in an inline form, be sure to place the <code>.add-on</code> and <code>input</code> on the same line, without spaces.</p>
      <hr>
      <h4>Form help text</h4>
      <p>To add help text for your form inputs, include inline help text with <code>&lt;span class="help-inline"&gt;</code> or a help text block with <code>&lt;p class="help-block"&gt;</code> after the input element.</p>
    </div>
    <div class="span8">
      <form class="form-horizontal">
        <fieldset>
          <div class="control-group">
            <label class="control-label">Form grid sizes</label>
            <div class="controls docs-input-sizes">
              <input class="span1" type="text" placeholder=".span1">
              <input class="span2" type="text" placeholder=".span2">
              <input class="span3" type="text" placeholder=".span3">
              <select class="span1">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
              </select>
              <select class="span2">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
              </select>
              <select class="span3">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
              </select>
              <p class="help-block">Use the same <code>.span*</code> classes from the grid system for input sizes.</p>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">Alternate sizes</label>
            <div class="controls docs-input-sizes">
              <input class="input-mini" type="text" placeholder=".input-mini">
              <input class="input-small" type="text" placeholder=".input-small">
              <input class="input-medium" type="text" placeholder=".input-medium">
              <p class="help-block">You may also use static classes that don't map to the grid, adapt to the responsive CSS styles, or account for varying types of controls (e.g., <code>input</code> vs. <code>select</code>).</p>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="prependedInput">Prepended text</label>
            <div class="controls">
              <div class="input-prepend">
                <span class="add-on">@</span><input class="span2" id="prependedInput" size="16" type="text">
              </div>
              <p class="help-block">Here's some help text</p>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="appendedInput">Appended text</label>
            <div class="controls">
              <div class="input-append">
                <input class="span2" id="appendedInput" size="16" type="text"><span class="add-on">.00</span>
              </div>
              <span class="help-inline">Here's more help text</span>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="appendedPrependedInput">Append and prepend</label>
            <div class="controls">
              <div class="input-prepend input-append">
                <span class="add-on">$</span><input class="span2" id="appendedPrependedInput" size="16" type="text"><span class="add-on">.00</span>
              </div>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="appendedInputButton">Append with button</label>
            <div class="controls">
              <div class="input-append">
                <input class="span2" id="appendedInputButton" size="16" type="text"><button class="btn" type="button">Go!</button>
              </div>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="appendedInputButtons">Two-button append</label>
            <div class="controls">
              <div class="input-append">
                <input class="span2" id="appendedInputButtons" size="16" type="text"><button class="btn" type="button">Search</button><button class="btn" type="button">Options</button>
              </div>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="inlineCheckboxes">Inline checkboxes</label>
            <div class="controls">
              <label class="checkbox inline">
                <input type="checkbox" id="inlineCheckbox1" value="option1"> 1
              </label>
              <label class="checkbox inline">
                <input type="checkbox" id="inlineCheckbox2" value="option2"> 2
              </label>
              <label class="checkbox inline">
                <input type="checkbox" id="inlineCheckbox3" value="option3"> 3
              </label>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="optionsCheckboxList">Checkboxes</label>
            <div class="controls">
              <label class="checkbox">
                <input type="checkbox" name="optionsCheckboxList1" value="option1">
                Option one is this and that—be sure to include why it's great
              </label>
              <label class="checkbox">
                <input type="checkbox" name="optionsCheckboxList2" value="option2">
                Option two can also be checked and included in form results
              </label>
              <label class="checkbox">
                <input type="checkbox" name="optionsCheckboxList3" value="option3">
                Option three can—yes, you guessed it—also be checked and included in form results
              </label>
              <p class="help-block"><strong>Note:</strong> Labels surround all the options for much larger click areas and a more usable form.</p>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">Radio buttons</label>
            <div class="controls">
              <label class="radio">
                <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked="">
                Option one is this and that—be sure to include why it's great
              </label>
              <label class="radio">
                <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                Option two can be something else and selecting it will deselect option one
              </label>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <button class="btn">Cancel</button>
          </div>
        </fieldset>
      </form>
    </div>
  </div><!-- /row -->
</section>

<section id="buttons">
  <div class="page-header">
    <h1>Buttons</h1>
  </div>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Button</th>
        <th>class=""</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><button class="btn" href="#">Default</button></td>
        <td><code>btn</code></td>
        <td>Standard gray button with gradient</td>
      </tr>
      <tr>
        <td><button class="btn btn-primary" href="#">Primary</button></td>
        <td><code>btn btn-primary</code></td>
        <td>Provides extra visual weight and identifies the primary action in a set of buttons</td>
      </tr>
      <tr>
        <td><button class="btn btn-info" href="#">Info</button></td>
        <td><code>btn btn-info</code></td>
        <td>Used as an alternative to the default styles</td>
      </tr>
      <tr>
        <td><button class="btn btn-success" href="#">Success</button></td>
        <td><code>btn btn-success</code></td>
        <td>Indicates a successful or positive action</td>
      </tr>
      <tr>
        <td><button class="btn btn-warning" href="#">Warning</button></td>
        <td><code>btn btn-warning</code></td>
        <td>Indicates caution should be taken with this action</td>
      </tr>
      <tr>
        <td><button class="btn btn-danger" href="#">Danger</button></td>
        <td><code>btn btn-danger</code></td>
        <td>Indicates a dangerous or potentially negative action</td>
      </tr>
      <tr>
        <td><button class="btn btn-inverse" href="#">Inverse</button></td>
        <td><code>btn btn-inverse</code></td>
        <td>Alternate dark gray button, not tied to a semantic action or use</td>
      </tr>
    </tbody>
  </table>

  <div class="row-fluid">
    <div class="span4">
      <h3>Buttons for actions</h3>
      <p>As a convention, buttons should only be used for actions while hyperlinks are to be used for objects. For instance, "Download" should be a button while "recent activity" should be a link.</p>
      <p>Button styles can be applied to anything with the <code>.btn</code> class applied. However, typically you'll want to apply these to only <code>&lt;a&gt;</code> and <code>&lt;button&gt;</code> elements.</p>
      <h3>Cross browser compatibility</h3>
      <p>IE9 doesn't crop background gradients on rounded corners, so we remove it. Related, IE9 jankifies disabled <code>button</code> elements, rendering text gray with a nasty text-shadow that we cannot fix.</p>
    </div>
    <div class="span4">
      <h3>Multiple sizes</h3>
      <p>Fancy larger or smaller buttons? Add <code>.btn-large</code>, <code>.btn-small</code>, or <code>.btn-mini</code> for two additional sizes.</p>
      <p>
        <button class="btn btn-large btn-primary">Primary action</button>
        <button class="btn btn-large">Action</button>
      </p>
      <p>
        <button class="btn btn-small btn-primary">Primary action</button>
        <button class="btn btn-small">Action</button>
      </p>
      <p>
        <button class="btn btn-mini btn-primary">Primary action</button>
        <button class="btn btn-mini">Action</button>
      </p>
      <br>
      <h3>Disabled state</h3>
      <p>For disabled buttons, add the <code>.disabled</code> class to links and the <code>disabled</code> attribute for <code>&lt;button&gt;</code> elements.</p>
      <p>
        <a href="#" class="btn btn-large btn-primary disabled">Primary link</a>
        <a href="#" class="btn btn-large disabled">Link</a>
      </p>
      <p style="margin-bottom: 18px;">
        <button class="btn btn-large btn-primary disabled" disabled="disabled">Primary button</button>
        <button class="btn btn-large" disabled="">Button</button>
      </p>
      <p>
        <span class="label label-info">Heads up!</span>
        We use <code>.disabled</code> as a utility class here, similar to the common <code>.active</code> class, so no prefix is required.
      </p>
    </div>
    <div class="span4">
      <h3>One class, multiple tags</h3>
      <p>Use the <code>.btn</code> class on an <code>&lt;a&gt;</code>, <code>&lt;button&gt;</code>, or <code>&lt;input&gt;</code> element.</p>
<form>
<a class="btn" href="">Link</a>
<button class="btn" type="submit">Button</button>
<input class="btn" type="button" value="Input">
<input class="btn" type="submit" value="Submit">
</form>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;a</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"btn"</span><span class="pln"> </span><span class="atn">href</span><span class="pun">=</span><span class="atv">""</span><span class="tag">&gt;</span><span class="pln">Link</span><span class="tag">&lt;/a&gt;</span></li><li class="L1"><span class="tag">&lt;button</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"btn"</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"submit"</span><span class="tag">&gt;</span></li><li class="L2"><span class="pln">  Button</span></li><li class="L3"><span class="tag">&lt;/button&gt;</span></li><li class="L4"><span class="tag">&lt;input</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"btn"</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"button"</span></li><li class="L5"><span class="pln">         </span><span class="atn">value</span><span class="pun">=</span><span class="atv">"Input"</span><span class="tag">&gt;</span></li><li class="L6"><span class="tag">&lt;input</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"btn"</span><span class="pln"> </span><span class="atn">type</span><span class="pun">=</span><span class="atv">"submit"</span></li><li class="L7"><span class="pln">         </span><span class="atn">value</span><span class="pun">=</span><span class="atv">"Submit"</span><span class="tag">&gt;</span></li></ol></pre>
      <p>As a best practice, try to match the element for you context to ensure matching cross-browser rendering. If you have an <code>input</code>, use an <code>&lt;input type="submit"&gt;</code> for your button.</p>
    </div>
  </div>
</section>

<section id="icons">
  <div class="page-header">
    <h1>Icons <small>Graciously provided by <a href="http://glyphicons.com" target="_blank">Glyphicons</a></small></h1>
  </div>
  <div class="row-fluid">
    <div class="span3">
      <ul class="the-icons">
        <li><i class="icon-glass"></i> icon-glass</li>
        <li><i class="icon-music"></i> icon-music</li>
        <li><i class="icon-search"></i> icon-search</li>
        <li><i class="icon-envelope"></i> icon-envelope</li>
        <li><i class="icon-heart"></i> icon-heart</li>
        <li><i class="icon-star"></i> icon-star</li>
        <li><i class="icon-star-empty"></i> icon-star-empty</li>
        <li><i class="icon-user"></i> icon-user</li>
        <li><i class="icon-film"></i> icon-film</li>
        <li><i class="icon-th-large"></i> icon-th-large</li>
        <li><i class="icon-th"></i> icon-th</li>
        <li><i class="icon-th-list"></i> icon-th-list</li>
        <li><i class="icon-ok"></i> icon-ok</li>
        <li><i class="icon-remove"></i> icon-remove</li>
        <li><i class="icon-zoom-in"></i> icon-zoom-in</li>
        <li><i class="icon-zoom-out"></i> icon-zoom-out</li>
        <li><i class="icon-off"></i> icon-off</li>
        <li><i class="icon-signal"></i> icon-signal</li>
        <li><i class="icon-cog"></i> icon-cog</li>
        <li><i class="icon-trash"></i> icon-trash</li>
        <li><i class="icon-home"></i> icon-home</li>
        <li><i class="icon-file"></i> icon-file</li>
        <li><i class="icon-time"></i> icon-time</li>
        <li><i class="icon-road"></i> icon-road</li>
        <li><i class="icon-download-alt"></i> icon-download-alt</li>
        <li><i class="icon-download"></i> icon-download</li>
        <li><i class="icon-upload"></i> icon-upload</li>
        <li><i class="icon-inbox"></i> icon-inbox</li>
        <li><i class="icon-play-circle"></i> icon-play-circle</li>
        <li><i class="icon-repeat"></i> icon-repeat</li>
        <li><i class="icon-refresh"></i> icon-refresh</li>
        <li><i class="icon-list-alt"></i> icon-list-alt</li>
        <li><i class="icon-lock"></i> icon-lock</li>
        <li><i class="icon-flag"></i> icon-flag</li>
        <li><i class="icon-headphones"></i> icon-headphones</li>
      </ul>
    </div>
    <div class="span3">
      <ul class="the-icons">
        <li><i class="icon-volume-off"></i> icon-volume-off</li>
        <li><i class="icon-volume-down"></i> icon-volume-down</li>
        <li><i class="icon-volume-up"></i> icon-volume-up</li>
        <li><i class="icon-qrcode"></i> icon-qrcode</li>
        <li><i class="icon-barcode"></i> icon-barcode</li>
        <li><i class="icon-tag"></i> icon-tag</li>
        <li><i class="icon-tags"></i> icon-tags</li>
        <li><i class="icon-book"></i> icon-book</li>
        <li><i class="icon-bookmark"></i> icon-bookmark</li>
        <li><i class="icon-print"></i> icon-print</li>
        <li><i class="icon-camera"></i> icon-camera</li>
        <li><i class="icon-font"></i> icon-font</li>
        <li><i class="icon-bold"></i> icon-bold</li>
        <li><i class="icon-italic"></i> icon-italic</li>
        <li><i class="icon-text-height"></i> icon-text-height</li>
        <li><i class="icon-text-width"></i> icon-text-width</li>
        <li><i class="icon-align-left"></i> icon-align-left</li>
        <li><i class="icon-align-center"></i> icon-align-center</li>
        <li><i class="icon-align-right"></i> icon-align-right</li>
        <li><i class="icon-align-justify"></i> icon-align-justify</li>
        <li><i class="icon-list"></i> icon-list</li>
        <li><i class="icon-indent-left"></i> icon-indent-left</li>
        <li><i class="icon-indent-right"></i> icon-indent-right</li>
        <li><i class="icon-facetime-video"></i> icon-facetime-video</li>
        <li><i class="icon-picture"></i> icon-picture</li>
        <li><i class="icon-pencil"></i> icon-pencil</li>
        <li><i class="icon-map-marker"></i> icon-map-marker</li>
        <li><i class="icon-adjust"></i> icon-adjust</li>
        <li><i class="icon-tint"></i> icon-tint</li>
        <li><i class="icon-edit"></i> icon-edit</li>
        <li><i class="icon-share"></i> icon-share</li>
        <li><i class="icon-check"></i> icon-check</li>
        <li><i class="icon-move"></i> icon-move</li>
        <li><i class="icon-step-backward"></i> icon-step-backward</li>
        <li><i class="icon-fast-backward"></i> icon-fast-backward</li>
      </ul>
    </div>
    <div class="span3">
      <ul class="the-icons">
        <li><i class="icon-backward"></i> icon-backward</li>
        <li><i class="icon-play"></i> icon-play</li>
        <li><i class="icon-pause"></i> icon-pause</li>
        <li><i class="icon-stop"></i> icon-stop</li>
        <li><i class="icon-forward"></i> icon-forward</li>
        <li><i class="icon-fast-forward"></i> icon-fast-forward</li>
        <li><i class="icon-step-forward"></i> icon-step-forward</li>
        <li><i class="icon-eject"></i> icon-eject</li>
        <li><i class="icon-chevron-left"></i> icon-chevron-left</li>
        <li><i class="icon-chevron-right"></i> icon-chevron-right</li>
        <li><i class="icon-plus-sign"></i> icon-plus-sign</li>
        <li><i class="icon-minus-sign"></i> icon-minus-sign</li>
        <li><i class="icon-remove-sign"></i> icon-remove-sign</li>
        <li><i class="icon-ok-sign"></i> icon-ok-sign</li>
        <li><i class="icon-question-sign"></i> icon-question-sign</li>
        <li><i class="icon-info-sign"></i> icon-info-sign</li>
        <li><i class="icon-screenshot"></i> icon-screenshot</li>
        <li><i class="icon-remove-circle"></i> icon-remove-circle</li>
        <li><i class="icon-ok-circle"></i> icon-ok-circle</li>
        <li><i class="icon-ban-circle"></i> icon-ban-circle</li>
        <li><i class="icon-arrow-left"></i> icon-arrow-left</li>
        <li><i class="icon-arrow-right"></i> icon-arrow-right</li>
        <li><i class="icon-arrow-up"></i> icon-arrow-up</li>
        <li><i class="icon-arrow-down"></i> icon-arrow-down</li>
        <li><i class="icon-share-alt"></i> icon-share-alt</li>
        <li><i class="icon-resize-full"></i> icon-resize-full</li>
        <li><i class="icon-resize-small"></i> icon-resize-small</li>
        <li><i class="icon-plus"></i> icon-plus</li>
        <li><i class="icon-minus"></i> icon-minus</li>
        <li><i class="icon-asterisk"></i> icon-asterisk</li>
        <li><i class="icon-exclamation-sign"></i> icon-exclamation-sign</li>
        <li><i class="icon-gift"></i> icon-gift</li>
        <li><i class="icon-leaf"></i> icon-leaf</li>
        <li><i class="icon-fire"></i> icon-fire</li>
        <li><i class="icon-eye-open"></i> icon-eye-open</li>
      </ul>
    </div>
    <div class="span3">
      <ul class="the-icons">
        <li><i class="icon-eye-close"></i> icon-eye-close</li>
        <li><i class="icon-warning-sign"></i> icon-warning-sign</li>
        <li><i class="icon-plane"></i> icon-plane</li>
        <li><i class="icon-calendar"></i> icon-calendar</li>
        <li><i class="icon-random"></i> icon-random</li>
        <li><i class="icon-comment"></i> icon-comment</li>
        <li><i class="icon-magnet"></i> icon-magnet</li>
        <li><i class="icon-chevron-up"></i> icon-chevron-up</li>
        <li><i class="icon-chevron-down"></i> icon-chevron-down</li>
        <li><i class="icon-retweet"></i> icon-retweet</li>
        <li><i class="icon-shopping-cart"></i> icon-shopping-cart</li>
        <li><i class="icon-folder-close"></i> icon-folder-close</li>
        <li><i class="icon-folder-open"></i> icon-folder-open</li>
        <li><i class="icon-resize-vertical"></i> icon-resize-vertical</li>
        <li><i class="icon-resize-horizontal"></i> icon-resize-horizontal</li>
        <li><i class="icon-hdd"></i> icon-hdd</li>
        <li><i class="icon-bullhorn"></i> icon-bullhorn</li>
        <li><i class="icon-bell"></i> icon-bell</li>
        <li><i class="icon-certificate"></i> icon-certificate</li>
        <li><i class="icon-thumbs-up"></i> icon-thumbs-up</li>
        <li><i class="icon-thumbs-down"></i> icon-thumbs-down</li>
        <li><i class="icon-hand-right"></i> icon-hand-right</li>
        <li><i class="icon-hand-left"></i> icon-hand-left</li>
        <li><i class="icon-hand-up"></i> icon-hand-up</li>
        <li><i class="icon-hand-down"></i> icon-hand-down</li>
        <li><i class="icon-circle-arrow-right"></i> icon-circle-arrow-right</li>
        <li><i class="icon-circle-arrow-left"></i> icon-circle-arrow-left</li>
        <li><i class="icon-circle-arrow-up"></i> icon-circle-arrow-up</li>
        <li><i class="icon-circle-arrow-down"></i> icon-circle-arrow-down</li>
        <li><i class="icon-globe"></i> icon-globe</li>
        <li><i class="icon-wrench"></i> icon-wrench</li>
        <li><i class="icon-tasks"></i> icon-tasks</li>
        <li><i class="icon-filter"></i> icon-filter</li>
        <li><i class="icon-briefcase"></i> icon-briefcase</li>
        <li><i class="icon-fullscreen"></i> icon-fullscreen</li>
      </ul>
    </div>
  </div>

  <br>

  <div class="row-fluid">
    <div class="span4">
      <h3>Built as a sprite</h3>
      <p>Instead of making every icon an extra request, we've compiled them into a sprite—a bunch of images in one file that uses CSS to position the images with <code>background-position</code>. This is the same method we use on Twitter.com and it has worked well for us.</p>
      <p>All icons classes are prefixed with <code>.icon-</code> for proper namespacing and scoping, much like our other components. This will help avoid conflicts with other tools.</p>
      <p><a href="http://glyphicons.com" target="_blank">Glyphicons</a> has granted us use of the Halflings set in our open-source toolkit so long as we provide a link and credit here in the docs. Please consider doing the same in your projects.</p>
    </div>
    <div class="span4">
      <h3>How to use</h3>
      <p>Bootstrap uses an <code>&lt;i&gt;</code> tag for all icons, but they have no case class—only a shared prefix. To use, place the following code just about anywhere:</p>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;i</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"icon-search"</span><span class="tag">&gt;&lt;/i&gt;</span></li></ol></pre>
      <p>There are also styles available for inverted (white) icons, made ready with one extra class:</p>
<pre class="prettyprint linenums"><ol class="linenums"><li class="L0"><span class="tag">&lt;i</span><span class="pln"> </span><span class="atn">class</span><span class="pun">=</span><span class="atv">"icon-search icon-white"</span><span class="tag">&gt;&lt;/i&gt;</span></li></ol></pre>
      <p>There are 140 classes to choose from for your icons. Just add an <code>&lt;i&gt;</code> tag with the right classes and you're set. You can find the full list in <strong>sprites.less</strong> or right here in this document.</p>
      <p>
        <span class="label label-info">Heads up!</span>
        When using beside strings of text, as in buttons or nav links, be sure to leave a space after the <code>&lt;i&gt;</code> tag for proper spacing.
      </p>
    </div>
    <div class="span4">
      <h3>Use cases</h3>
      <p>Icons are great, but where would one use them? Here are a few ideas:</p>
      <ul>
        <li>As visuals for your sidebar navigation</li>
        <li>For a purely icon-driven navigation</li>
        <li>For buttons to help convey the meaning of an action</li>
        <li>With links to share context on a user's destination</li>
      </ul>
      <p>Essentially, anywhere you can put an <code>&lt;i&gt;</code> tag, you can put an icon.</p>
    </div>
  </div>

  <h3>Examples</h3>
  <p>Use them in buttons, button groups for a toolbar, navigation, or prepended form inputs.</p>
  <div class="row-fluid">
    <div class="span4">
      <div class="btn-toolbar" style="margin-bottom: 9px">
        <div class="btn-group">
          <a class="btn" href="#"><i class="icon-align-left"></i></a>
          <a class="btn" href="#"><i class="icon-align-center"></i></a>
          <a class="btn" href="#"><i class="icon-align-right"></i></a>
          <a class="btn" href="#"><i class="icon-align-justify"></i></a>
        </div>
        <div class="btn-group">
          <a class="btn btn-primary" href="#"><i class="icon-user icon-white"></i> User</a>
          <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
            <li><a href="#"><i class="icon-trash"></i> Delete</a></li>
            <li><a href="#"><i class="icon-ban-circle"></i> Ban</a></li>
            <li class="divider"></li>
            <li><a href="#"><i class="i"></i> Make admin</a></li>
          </ul>
        </div>
      </div>
      <p>
        <a class="btn" href="#"><i class="icon-refresh"></i> Refresh</a>
        <a class="btn btn-success" href="#"><i class="icon-shopping-cart icon-white"></i> Checkout</a>
        <a class="btn btn-danger" href="#"><i class="icon-trash icon-white"></i> Delete</a>
      </p>
      <p>
        <a class="btn btn-large" href="#"><i class="icon-comment"></i> Comment</a>
        <a class="btn btn-small" href="#"><i class="icon-cog"></i> Settings</a>
        <a class="btn btn-small btn-info" href="#"><i class="icon-info-sign icon-white"></i> More Info</a>
      </p>
    </div>
    <div class="span4">
      <div class="well" style="padding: 8px 0;">
        <ul class="nav nav-list">
          <li class="active"><a href="#"><i class="icon-home icon-white"></i> Home</a></li>
          <li><a href="#"><i class="icon-book"></i> Library</a></li>
          <li><a href="#"><i class="icon-pencil"></i> Applications</a></li>
          <li><a href="#"><i class="i"></i> Misc</a></li>
        </ul>
      </div> <!-- /well -->
    </div>
    <div class="span4">
      <form>
        <div class="control-group">
          <label class="control-label" for="inputIcon">Email address</label>
          <div class="controls">
            <div class="input-prepend">
              <span class="add-on"><i class="icon-envelope"></i></span><input class="span2" id="inputIcon" type="text">
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

</div>
<?php include('footer.php');?>