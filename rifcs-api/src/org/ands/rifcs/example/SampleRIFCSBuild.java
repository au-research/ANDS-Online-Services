/**
 * Date Modified: $Date: 2009-08-28 10:39:55 +1000 (Fri, 28 Aug 2009) $
 * Version: $Revision: 129 $
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
import java.io.IOException;

import javax.xml.parsers.ParserConfigurationException;


import org.ands.rifcs.base.*;

import org.xml.sax.SAXException;

public class SampleRIFCSBuild
{
	private static RIFCS rifcs = null;

    public static void main(String[] args) throws RIFCSException, FileNotFoundException, SAXException, ParserConfigurationException, IOException
    {
        RIFCSWrapper mw = new RIFCSWrapper();
        rifcs = mw.getRIFCSObject();
        RegistryObject r = rifcs.newRegistryObject();

        r.setKey("collection1");
        r.setGroup("ANDS");
        r.setOriginatingSource("http://myrepository.au.edu");
        
    	Collection c = r.newCollection();
    	c.setType("collection");
    	
        c.addIdentifier("hdl:7651/myhandlesuffix", "handle");
        
        Name n = c.newName();
        n.setType("primary");
        // alternatively could use n.addNamePart("Sample Collection", null, null);
        NamePart np = n.newNamePart();
        np.setValue("Sample Collection");
        n.addNamePart(np);
        c.addName(n);

        Location l = c.newLocation();
        Address a = l.newAddress();
        Electronic e = a.newElectronic();
        e.setValue("http://myrepository.au.edu/collections/collection1");
        e.setType("url");
        a.addElectronic(e);
        l.addAddress(a);
        c.addLocation(l);
        
        RelatedObject ro = c.newRelatedObject();
        ro.setKey("activity1");
        ro.addRelation("isOutputOf", null, null, null);
        c.addRelatedObject(ro);
        
        RelatedObject ro2 = c.newRelatedObject();
        ro2.setKey("party1");
        ro2.addRelation("isOwnerOf", null, null, null);
        c.addRelatedObject(ro2);

        RelatedObject ro3 = c.newRelatedObject();
        ro3.setKey("service1");
        ro3.addRelation("supports", null, null, null);
        c.addRelatedObject(ro3);
        
        c.addSubject("subject1", "local", null);
        c.addSubject("subject2", "local", null);
        
        c.addDescription("This is a sample description", "brief", null);
        c.addRelatedInfo("http://external-server.edu/related-page.htm");
        
        r.addCollection(c);       
        rifcs.addRegistryObject(r);

	    mw.validate();

		mw.write(System.out);
    }
}