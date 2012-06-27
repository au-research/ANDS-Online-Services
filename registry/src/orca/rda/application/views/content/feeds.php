<?php
/** 
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/ 
?>
<?php $this->load->view('tpl/header');?>
<?php $this->load->view('tpl/mid');?>
<div class="box" id="about">
<h1>Ever wished you could be notified when new collections are added to Research Data Australia? </h1>
<p>Well now you can be. Research Data Australia now includes RSS, ATOM and Twitter feeds.</p>
<h2 style="text-decoration:underline;">RSS & ATOM</h2>
<p>With the almost infinite amount of information on the web and the lack of time we have as users to explore it, 
staying informed of new information is becoming an exceedingly difficult task. RSS and ATOM syndication feeds assist 
in this task by allowing users to easily stay informed of changes to multiple websites in a single location. 
For more information on the formats please navigate to the following external links <a href="http://www.whatisrss.com/" target="_blank">RSS</a> <a href="http://www.atomenabled.org/" target="_blank">ATOM</a>.</p>
<p>The RSS and ATOM feeds within Research Data Australia are customisable and allow you to generate feeds based on the searches you conduct in Research Data Australia. Once subscribed to a feed, you will be notified when any new collections are published to Research Data Australia which matches your search query.  </p>
<p>To subscribe to a feed, conduct a search (or browse) for collections within Research Data Australia. Shown at the bottom of the list of search results are links to the RSS and ATOM feeds for the search. Simply click on the preferred format and you will be navigated to the feed. In most cases you will need to copy and paste the URL of the feed into your feed reader to subscribe to it.  Refer to your feed reader's user documentation for more information.</p>
<h2 style="text-decoration:underline;">Twitter</h2>
<p>Twitter is an online social networking application that allows users communicate and share information. 
For more information on Twitter please navigate to the following external link <a href="https://twitter.com/about" target="_blank">Twitter</a>.</p>
<p>Research Data Australia is tweeting new collection notifications through Twitter via the user account 'ResearchDataAustralia'. To follow Research Data Australia on twitter simply click the &lt;insert button&gt; button.</p>
<p>Not only are we tweeting about new collections but we are also grouping and tagging the notifications with ANZSRC subject hashtags. 
This means that if you're using 3rd party applications like <a href="http://www.tweetdeck.com" target="_blank">TweetDeck</a>,  <a href="http://hootsuite.com" target="_blank">Hootsuite</a> or  <a href="http://monitter.com" target="_blank">Monitter</a>, 
it's possible to filter the notification tweets from the 'ResearchDataAustralia' account by the ANZSRC subjects you're interested in. 
Tweets will be generated on a weekly basis for any new collections published to Research Data Australia which contain ANZSRC codes.</p>
<p>To filter the tweets in <a href="http://www.tweetdeck.com" target="_blank">TweetDeck</a>,  <a href="http://hootsuite.com" target="_blank">Hootsuite</a> or  <a href="http://monitter.com" target="_blank">Monitter</a>, 
simply add a new column/stream by conducting a search for </p>
<p><strong>#ANZSRC-&lt;code&gt; from:ResearchDataAustralia </strong></p>
<p>Where &lt;code&gt; is replaced with the ANZSRC code you'd like to filter by. </p>
<p>E.g. <strong>#ANZSRC-010102 from:ResearchDataAustralia </strong></p>
<p>To find the ANZSRC code for a specific subject you can now use the Research Data Australia Vocabulary Browser.</p>
</div>
<?php $this->load->view('tpl/footer');?>