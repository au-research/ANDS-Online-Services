<?xml version="1.0" encoding="UTF-8" ?>
<!-- 
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
**************************************************************************** -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" indent="yes" omit-xml-declaration="yes" encoding="utf-8" />
<xsl:preserve-space elements="" />

<xsl:template match="failure-response">
   <xsl:value-of select="code/text()"/> ERROR - <xsl:value-of select="message/text()"/>
</xsl:template>

<xsl:template match="node()" priority="-999"/>
</xsl:stylesheet>