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
import java.io.IOException;

import javax.xml.parsers.ParserConfigurationException;


import org.ands.rifcs.base.*;

import org.xml.sax.SAXException;

public class SampleRIFCSBuildActivity
{

	private static RIFCS rifcs = null;
	
    public SampleRIFCSBuildActivity()
    {
    
    }

    public static void main(String args[]) throws RIFCSException, FileNotFoundException, SAXException, ParserConfigurationException, IOException
    {
        RIFCSWrapper mw = new RIFCSWrapper();
        rifcs = mw.getRIFCSObject();
        RegistryObject r = rifcs.newRegistryObject();
        r.setKey("Activity");
        r.setGroup("ANDS");
        r.setOriginatingSource("http://myrepository.au.edu");
        Activity a = r.newActivity();
        a.setType("activity");
        
        Identifier identifier = a.newIdentifier();
        identifier.setType("handle");
        identifier.setValue("hdl:7651/myhandlesuffix");
        a.addIdentifier(identifier);
        
        Name n = a.newName();
        n.setType("primary");
        NamePart np = n.newNamePart();
        np.setValue("Sample Collection");
        n.addNamePart(np);
        a.addName(n);
        
        Location l = a.newLocation();
        Address ad = l.newAddress();
        Electronic e = ad.newElectronic();
        e.setValue("http://myrepository.au.edu/collections/collection1");
        e.setType("url");
        ad.addElectronic(e);
        l.addAddress(ad);
        a.addLocation(l);
        
        Coverage cov = a.newCoverage();
        Spatial sp = cov.newSpatial();
        Temporal tmp = cov.newTemporal();
        tmp.addDate("1999-3-4", "dateFrom", "W3C");
        tmp.addDate("1999-3-4", "dateFrom", "W3C");
        sp.setValue("126.773437,-23.598894 127.652343,-27.405585 131.519531,-27.093039 131.167968,-24.081241 130.464843,-20.503868 127.828124,-19.843884 123.960937,-20.339134 123.433593,-22.141282 123.433593,-25.040485 123.785156,-28.183080 126.773437,-23.598894");
        sp.setType("kmlPolyCoords");
        cov.addSpatial(sp);
        cov.addTemporal(tmp);
        a.addCoverage(cov);
        
        RelatedObject ro = a.newRelatedObject();
        ro.setKey("collection1");
        ro.addRelation("isOutputOf", null, null, null);
        a.addRelatedObject(ro);
        RelatedObject ro2 = a.newRelatedObject();
        ro2.setKey("party1");
        ro2.addRelation("isOwnerOf", null, null, null);
        a.addRelatedObject(ro2);
        RelatedObject ro3 = a.newRelatedObject();
        ro3.setKey("service1");
        ro3.addRelation("supports", null, null, null);
        a.addRelatedObject(ro3);
        
        a.addSubject("subject1", "local", "identifier1" , "en");
        a.addSubject("subject2", "local", "identifier2" , "en");

        a.addDescription("This is a sample description", "brief", null);

        RelatedInfo ri = a.newRelatedInfo();
        ri.setIdentifier("http://external-server.edu/related-page.htm", "uri");
        ri.setTitle("A related information resource");
        ri.setNotes("Notes about the related information resource");
        a.addRelatedInfo(ri);

        
        Right right= a.newRight();
        right.setAccessRights("Access Right Value", "Access Rights Uri", "Access Right Type");
        right.setLicence("Licence Value", "Licence Uri", "Licence Type");
        right.setRightsStatement("Right Statement Value", "Right Statement Uri");
        a.addRight(right);
        right= a.newRight();
        right.setAccessRights("Access Right Value2", "Access Rights Uri2", "Access Right Type2");
        right.setLicence("Licence Value2", "Licence Uri2", "Licence Type2");
        right.setRightsStatement("Right Statement Value2", "Right Statement Uri2");
        a.addRight(right);
        a.addExistenceDate("01-01-01", "dd-mm-yy", "12-12-12", "dd-mm-yy");
        
        RelatedInfo relatedInfo = a.newRelatedInfo();
        relatedInfo.setIdentifier("related info", "text");
        relatedInfo.setNotes("Notes");
        relatedInfo.setTitle("Title");
        relatedInfo.setType("Type");
        a.addRelatedInfo(relatedInfo);
        relatedInfo = a.newRelatedInfo();
        relatedInfo.setIdentifier("related info1", "text");
        relatedInfo.setNotes("Notes1");
        relatedInfo.setTitle("Title1");
        relatedInfo.setType("Type");
        a.addRelatedInfo(relatedInfo);

        r.addActivity(a);

        rifcs.addRegistryObject(r);
        mw.write(System.out);
        mw.validate();
    }

}