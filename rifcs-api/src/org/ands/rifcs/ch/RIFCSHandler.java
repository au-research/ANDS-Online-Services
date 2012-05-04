/**
 * Date Modified: $Date: 2010-01-18 10:22:16 +1100 (Mon, 18 Jan 2010) $
 * Version: $Revision: 288 $
 * 
 * Copyright 2008 The Australian National University (ANU)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */
package org.ands.rifcs.ch;

import org.w3c.dom.Document;
/**
 * Interface which any METS Content Handler must implement
 * 
 * @author Scott Yeadon
 */
public interface RIFCSHandler
{
    /**
     * Get a DOM document resulting from a SAX parse
     *  
     *  @return
     *      The DOM document. May be null if called before parsing and empty
     *      if parsing exception caught.
     */
    public Document getDocument();
}