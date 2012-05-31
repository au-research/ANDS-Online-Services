/**
 * Date Modified: $Date: 2010-07-08 14:54:07 +1000 (Thu, 08 Jul 2010) $
 * Version: $Revision: 463 $
 * 
 * Copyright 2009 The Australian National University (ANU)
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
package org.ands.rifcs.base;

import org.w3c.dom.Node;

/**
 * Class representing registry object rights
 * 
 * @author Mahmoud Sadeghi
 * 
 */
public class Right extends RIFCSElement {

	protected RightsInfo rightsStatement = null;
	protected RightsTypedInfo licence = null;
	protected RightsTypedInfo accessRights = null;

	/**
	 * Construct a Rights object
	 * 
	 * @param n
	 *            A w3c Node, typically an Element
	 * 
	 * @exception RIFCSException
	 */
	protected Right(Node n) throws RIFCSException {
		super(n, Constants.ELEMENT_RIGHTS);
	}

	/**
	 * Set the rightsStatement
	 * 
	 * @param rightsStatement
	 * @throws RIFCSException
	 */
	public void setRightsStatement(RightsInfo rightsStatement) throws RIFCSException {
		this.rightsStatement = rightsStatement;
		this.getElement().appendChild(this.rightsStatement.getElement());
	}

	/**
	 * Set the rightsStatement Value
	 * 
	 * @param value
	 * @throws RIFCSException
	 */
	public void setRightsStatement(String value) throws RIFCSException {
		this.setRightsStatement(value, null);
	}

	/**
	 * Set the rightsStatement Value and URI 
	 * 
	 * @param value
	 * @param rightsUri
	 * @throws RIFCSException
	 */
	public void setRightsStatement(String value, String rightsUri) throws RIFCSException {
		RightsInfo rightsStatement = new RightsInfo(this.newElement(Constants.ELEMENT_RIGHTS_STATEMENT));
		rightsStatement.setValue(value);
		if (rightsUri != null)
			rightsStatement.setRightsUri(rightsUri);
		setRightsStatement(rightsStatement);
	}

	/**
	 * return the rightsStatement
	 * 
	 * @return The rightsStatement
	 */
	public RightsInfo getRightsStatement() {
		return this.rightsStatement;
	}
	/**
	 * Set the licence
	 * 
	 * @param licence
	 * @throws RIFCSException
	 */
	public void setLicence(RightsTypedInfo licence) throws RIFCSException {
		this.licence = licence;
		this.getElement().appendChild(this.licence.getElement());
	}

	/**
	 * Set the licence Value
	 * 
	 * @param value
	 * @throws RIFCSException
	 */
	public void setLicence(String value) throws RIFCSException {
		this.setLicence(value, null);
	}

	/**
	 * Set the licence Value and Type
	 * 
	 * @param value
	 * @param type
	 * @throws RIFCSException
	 */
	public void setLicence(String value, String type) throws RIFCSException {
		this.setLicence(value, type, null);
	}

	/**
	 * Set the licence Value, URI and Type 
	 * 
	 * @param value
	 * @param rightsUri
	 * @param type
	 * @throws RIFCSException
	 */
	public void setLicence(String value, String rightsUri, String type) throws RIFCSException {
		RightsTypedInfo licence = new RightsTypedInfo(this.newElement(Constants.ELEMENT_LICENCE));
		licence.setValue(value);
		if (rightsUri != null)
			licence.setRightsUri(rightsUri);
		if (type != null)
			licence.setType(type);
		setLicence(licence);
	}

	/**
	 * return the licence
	 * 
	 * @return The licence
	 */
	public RightsTypedInfo getLicence() {
		return this.licence;
	}

	/**
	 * Set the accessRights
	 * 
	 * @param accessRights
	 * @throws RIFCSException
	 */
	public void setAccessRights(RightsTypedInfo accessRights) throws RIFCSException {
		this.accessRights= accessRights;
		this.getElement().appendChild(this.accessRights.getElement());
	}

	/**
	 * Set the accessRightsValue
	 * 
	 * @param value
	 * @throws RIFCSException
	 */
	public void setAccessRights(String value) throws RIFCSException {
		this.setAccessRights(value, null);
	}

	/**
	 * Set the accessRights Value and Type
	 * 
	 * @param value
	 * @param type
	 * @throws RIFCSException
	 */
	public void setAccessRights(String value, String type) throws RIFCSException {
		this.setAccessRights(value, type, null);
	}

	/**
	 * Set the accessRights Value, URI and Type 
	 * 
	 * @param value
	 * @param rightsUri
	 * @param type
	 * @throws RIFCSException
	 */
	public void setAccessRights(String value, String rightsUri, String type) throws RIFCSException {
		RightsTypedInfo accessRights = new RightsTypedInfo(this.newElement(Constants.ELEMENT_ACCESS_RIGHTS));
		accessRights.setValue(value);
		if (rightsUri != null)
			accessRights.setRightsUri(rightsUri);
		if (type != null)
			accessRights.setType(type);
		setLicence(accessRights);
	}

	/**
	 * return the accessRights
	 * 
	 * @return The accessRights
	 */
	public RightsTypedInfo getAccessRights() {
		return this.accessRights;
	}


}