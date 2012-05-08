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
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:cfd="http://apsr.edu.au/namespaces/cfd">
<xsl:output method="xml" version="1.0" indent="yes" omit-xml-declaration="yes" encoding="utf-8" />
<xsl:preserve-space elements="" />
<xsl:param name="submitId" />
<xsl:param name="dateFormat" />
<xsl:param name="timeFormat" />
<xsl:param name="datetimeFormat" />

<xsl:template match="form">
   <xsl:variable name="aID"><xsl:value-of select="@id"/></xsl:variable>
   <xsl:variable name="aTitle"><xsl:value-of select="@title"/></xsl:variable>
   <form id="{$aID}" action="{$submitId}" method="post">
   <table class="formTable" summary="{$aTitle}">
      <thead>
         <tr>
            <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
            <td><xsl:value-of select="@title"/></td>
         </tr>
      </thead>
      <tbody class="formFields">
         <xsl:apply-templates select="error | text | list" />
      </tbody>
   	  <tbody>
	   	 <tr>
		   	<td> </td>
			<td>
				<xsl:apply-templates select="button" />
			</td>
   		</tr>
	   	 <tr>
		   	<td> </td>
			<td class="formNotes">
				<xsl:apply-templates select="notes" />
			</td>
   		</tr>
	   </tbody>
   </table>
   </form>
</xsl:template>

<xsl:template match="error">
   <tr>
      <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
      <td class="errorText"><xsl:value-of select="@value"/></td>
   </tr>
</xsl:template>

<xsl:template match="text">
   <xsl:variable name="aID"><xsl:value-of select="@id"/></xsl:variable>
   <xsl:variable name="aSize"><xsl:value-of select="presentation/size"/></xsl:variable>
   <xsl:variable name="aMax"><xsl:value-of select="presentation/maxlength"/></xsl:variable>
   <xsl:variable name="aCols"><xsl:value-of select="presentation/cols"/></xsl:variable>
   <xsl:variable name="aRows"><xsl:value-of select="presentation/rows"/></xsl:variable>
   <xsl:variable name="aValue">
      <xsl:choose>
         <xsl:when test="/form/cfd:cosiformdata/cfd:element[@cfd:id=$aID]">
            <xsl:value-of select="/form/cfd:cosiformdata/cfd:element[@cfd:id=$aID]/@cfd:value"/>
         </xsl:when>
         <xsl:otherwise>
            <xsl:value-of select="value/text()"/>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <xsl:variable name="aError">
      <xsl:choose>
         <xsl:when test="/form/error[@id=$aID]">errorText</xsl:when>
         <xsl:otherwise> </xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <tr>
      <td class="{$aError}">
         <xsl:if test="validation/mandatory='true'">* </xsl:if>
         <xsl:value-of select="presentation/label"/><xsl:if test="presentation/label!=''">:</xsl:if>
      </td>
      <td>
         <xsl:choose>
            <!-- horizontal rule -->
            <xsl:when test="@type='hr'">
               <hr />
            </xsl:when>
            <!-- text box -->
            <xsl:when test="@type='box'">
               <input type="text" name="{$aID}" id="{$aID}" size="{$aSize}" maxlength="{$aMax}" value="{$aValue}" />
               <xsl:if test="validation/type='date'"><script type="text/javascript">dctGetDateTimeControl('<xsl:value-of select="$aID" />', '<xsl:value-of select="$dateFormat" />')</script> <span class="inputFormat"><xsl:value-of select="$dateFormat" /></span></xsl:if>
               <xsl:if test="validation/type='time'"><script type="text/javascript">dctGetDateTimeControl('<xsl:value-of select="$aID" />', '<xsl:value-of select="$timeFormat" />')</script> <span class="inputFormat"><xsl:value-of select="$timeFormat" /></span></xsl:if>
               <xsl:if test="validation/type='datetime'"><script type="text/javascript">dctGetDateTimeControl('<xsl:value-of select="$aID" />', '<xsl:value-of select="$datetimeFormat" />')</script> <span class="inputFormat"><xsl:value-of select="$datetimeFormat" /></span></xsl:if>
	           <xsl:choose>
                  <xsl:when test="validation/min and validation/max"><span class="inputFormat">(range: <xsl:value-of select="validation/min" /> to <xsl:value-of select="validation/max" />)</span></xsl:when>
                  <xsl:when test="validation/min"><span class="inputFormat">(range: &gt;= <xsl:value-of select="validation/min" />)</span></xsl:when>
                  <xsl:when test="validation/max"><span class="inputFormat">(range: &lt;= <xsl:value-of select="validation/max" />)</span></xsl:when>
	           </xsl:choose>
            </xsl:when>
            <!-- text area -->
            <xsl:when test="@type='area'">
               <textarea name="{$aID}" id="{$aID}" cols="{$aCols}" rows="{$aRows}"><xsl:value-of select="$aValue" /></textarea>
            </xsl:when>
            <!-- file upload -->
            <xsl:when test="@type='file'">
               <input type="file" name="{$aID}" id="{$aID}" size="{$aSize}" />
            </xsl:when>
            <!-- password -->
            <xsl:when test="@type='password'">
               <input type="password" name="{$aID}" id="{$aID}" value="{$aValue}" />
            </xsl:when>
            <!-- read only -->
            <xsl:when test="@type='readonly'">
               <xsl:value-of select="$aValue" /><input type="hidden" name="{$aID}" id="{$aID}" value="{$aValue}" />
            </xsl:when>
            <!-- display only -->
            <xsl:when test="@type='displayonly'">
               <xsl:value-of select="$aValue" />
            </xsl:when>
            <!-- hidden text -->
            <xsl:when test="@type='hidden'">
               <input type="hidden" name="{$aID}" id="{$aID}" value="{$aValue}" />
            </xsl:when>
         </xsl:choose>
      </td>
   </tr>
</xsl:template>

<xsl:template match="list">
   <xsl:variable name="aID"><xsl:value-of select="@id"/></xsl:variable>
   <xsl:variable name="aSize"><xsl:value-of select="presentation/size"/></xsl:variable>
   <xsl:variable name="aError">
      <xsl:choose>
         <xsl:when test="/form/error[@id=$aID]">errorText</xsl:when>
         <xsl:otherwise> </xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <tr>
      <td class="{$aError}">
         <xsl:if test="validation/mandatory='true'">* </xsl:if>
         <xsl:value-of select="presentation/label"/><xsl:if test="presentation/label!=''">:</xsl:if>
      </td>
      <td>
         <xsl:choose>
            <!-- multi-select list -->
            <xsl:when test="@type='multiple'">
               <select name="{$aID}[]" id="{$aID}[]" multiple="multiple" size="{$aSize}">
                  <xsl:apply-templates/>
               </select>
            </xsl:when>
            <!-- radio buttons -->
            <xsl:when test="@type='radio'">
               <xsl:apply-templates/>
            </xsl:when>
            <!-- checkboxes -->
            <xsl:when test="@type='checkbox'">
               <xsl:apply-templates/>
            </xsl:when>
            <!-- dropdown list -->
            <xsl:otherwise>
               <select name="{$aID}" id="{$aID}">
                  <xsl:apply-templates/>
               </select>
            </xsl:otherwise>
         </xsl:choose>
      </td>
   </tr>
</xsl:template>

<xsl:template match="list/group">
   <xsl:variable name="aLabel"><xsl:value-of select="@label"/></xsl:variable>
   <optgroup label="{$aLabel}">
      <xsl:apply-templates/>
   </optgroup>
</xsl:template>

<xsl:template match="list/item">
   <xsl:variable name="aID"><xsl:value-of select="parent::node()/@id"/></xsl:variable>
   <xsl:variable name="aValue"><xsl:value-of select="@value"/></xsl:variable>
   <xsl:variable name="aChoosen">
      <xsl:choose>
         <xsl:when test="/form/cfd:cosiformdata/cfd:element[@cfd:id=$aID]">
            <xsl:choose>
               <xsl:when test="$aValue=/form/cfd:cosiformdata/cfd:element[@cfd:id=$aID]/@cfd:value">true</xsl:when>
               <xsl:otherwise>false</xsl:otherwise>
            </xsl:choose>
         </xsl:when>
         <xsl:otherwise>
            <xsl:choose>
               <xsl:when test="@chosen">true</xsl:when>
               <xsl:otherwise>false</xsl:otherwise>
            </xsl:choose>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <xsl:choose>

   <!-- radio buttons -->
   <xsl:when test="parent::node()/@type='radio'">
      <xsl:choose>
         <xsl:when test="$aChoosen='true'">
            <input type="radio" name="{$aID}" id="{$aID}" value="{$aValue}" checked="checked" />
         </xsl:when>
         <xsl:otherwise>
            <input type="radio" name="{$aID}" id="{$aID}" value="{$aValue}" />
         </xsl:otherwise>
      </xsl:choose>
      <xsl:value-of select="text()"/>
      <xsl:choose>
         <xsl:when test="parent::node()/presentation/layout='horizontal'">
            <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
            <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
         </xsl:when>
         <xsl:otherwise>
            <br />
         </xsl:otherwise>
      </xsl:choose>
   </xsl:when>

   <!-- checkboxes -->
   <xsl:when test="parent::node()/@type='checkbox'">
      <xsl:choose>
         <xsl:when test="$aChoosen='true'">
            <input type="checkbox" name="{$aID}[]" id="{$aID}[]" value="{$aValue}" checked="checked" />
         </xsl:when>
         <xsl:otherwise>
            <input type="checkbox" name="{$aID}[]" id="{$aID}[]" value="{$aValue}" />
         </xsl:otherwise>
      </xsl:choose>
      <xsl:value-of select="text()"/>
      <xsl:choose>
         <xsl:when test="parent::node()/presentation/layout='horizontal'">
            <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
            <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
         </xsl:when>
         <xsl:otherwise>
            <br />
        </xsl:otherwise>
      </xsl:choose>
   </xsl:when>

      <!-- dropdown lists and multi-select lists -->
      <xsl:otherwise>
         <xsl:choose>
            <xsl:when test="$aChoosen='true'">
               <option value="{$aValue}" selected="selected"><xsl:value-of select="text()"/></option>
            </xsl:when>
            <xsl:otherwise>
               <option value="{$aValue}"><xsl:value-of select="text()"/></option>
            </xsl:otherwise>
         </xsl:choose>
      </xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template match="list/group/item">
   <xsl:variable name="aID"><xsl:value-of select="../parent::node()/@id"/></xsl:variable>
   <xsl:variable name="aValue"><xsl:value-of select="@value"/></xsl:variable>
   <xsl:variable name="aChoosen">
      <xsl:choose>
         <xsl:when test="/form/cfd:cosiformdata/cfd:element[@cfd:id=$aID]">
           <xsl:choose>
               <xsl:when test="$aValue=/form/cfd:cosiformdata/cfd:element[@cfd:id=$aID]/@cfd:value">true</xsl:when>
               <xsl:otherwise>false</xsl:otherwise>
            </xsl:choose>
         </xsl:when>
         <xsl:otherwise>
            <xsl:choose>
               <xsl:when test="@chosen">true</xsl:when>
               <xsl:otherwise>false</xsl:otherwise>
            </xsl:choose>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <xsl:choose>
      <xsl:when test="$aChoosen='true'">
         <option value="{$aValue}" selected="selected"><xsl:value-of select="text()"/></option>
      </xsl:when>
      <xsl:otherwise>
         <option value="{$aValue}"><xsl:value-of select="text()"/></option>
      </xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template match="button">
   <xsl:variable name="aValue"><xsl:value-of select="text()"/></xsl:variable>
   <input type="submit" name="action" value="{$aValue}" />
   <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
   <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
</xsl:template>

<xsl:template match="notes">
   <xsl:value-of select="text()"/><br />
</xsl:template>

<xsl:template match="node()" priority="-999"/>
</xsl:stylesheet>