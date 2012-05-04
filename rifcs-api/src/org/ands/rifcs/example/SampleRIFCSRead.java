/**
 * Date Modified: $Date: 2012-04-04 12:13:39 +1000 (Wed, 04 Apr 2012) $
 * Version: $Revision: 1695 $
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
 */
package org.ands.rifcs.example;

import java.io.FileNotFoundException;
import java.io.FileInputStream;
import java.io.IOException;
import java.net.MalformedURLException;
import java.util.Iterator;
import java.util.List;
import java.util.Map;

import javax.xml.parsers.ParserConfigurationException;


import org.ands.rifcs.base.*;
import org.ands.rifcs.ch.*;

import org.xml.sax.SAXException;

public class SampleRIFCSRead
{
	private static RIFCS rifcs = null;

    public static void main(String[] args) throws RIFCSException, FileNotFoundException, SAXException, ParserConfigurationException, IOException, MalformedURLException
    {
        RIFCSReader rr = new RIFCSReader();
        rr.mapToDOM(new FileInputStream(args[0]));
        RIFCSWrapper rw = new RIFCSWrapper(rr.getDocument());
	    rw.validate();
	    RIFCS rifcs = rw.getRIFCSObject();

	    List<RegistryObject> list = rifcs.getCollections();
	    for (Iterator<RegistryObject> i=list.iterator(); i.hasNext();)
        {
	    	RegistryObject ro = (RegistryObject)i.next();
            Collection c = (Collection)ro.getClassObject();
            Iterator j = c.getNames().iterator();
            while(j.hasNext()) 
            {
                Name n = (Name)j.next();
                if(n.getType().equals("primary"))
                {
                    Iterator k = n.getNameParts().iterator();
                    while(k.hasNext()) 
                        System.out.println((new StringBuilder()).append(((NamePart)k.next()).getValue()).append(" (").append(ro.getKey()).append(")").toString());
                }
            }
        }
    }
}