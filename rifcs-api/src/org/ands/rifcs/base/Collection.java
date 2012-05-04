/**
 * Date Modified: $Date: 2012-04-04 12:13:39 +1000 (Wed, 04 Apr 2012) $
 * Version: $Revision: 1695 $
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

import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

/**
 * Class representing a RIF-CS Collection registry object
 * 
 * @author Scott Yeadon
 * 
 */
public class Collection extends RIFCSElement {
	private List<Identifier> identifiers = new ArrayList<Identifier>();
	private List<Name> names = new ArrayList<Name>();
	private List<Location> locations = new ArrayList<Location>();
	private List<Coverage> coverages = new ArrayList<Coverage>();
	private List<RelatedObject> relatedObjects = new ArrayList<RelatedObject>();
	private List<Subject> subjects = new ArrayList<Subject>();
	private List<Description> descriptions = new ArrayList<Description>();
	private List<Right> rightsList = new ArrayList<Right>();
	private List<RelatedInfo> ris = new ArrayList<RelatedInfo>();
	private List<CitationInfo> cis = new ArrayList<CitationInfo>();

	/**
	 * Construct a Collection object
	 * 
	 * @param n
	 *            A w3c Node, typically an Element
	 * 
	 * @exception RIFCSException
	 */
	protected Collection(Node n) throws RIFCSException {
		super(n, Constants.ELEMENT_COLLECTION);
		initStructures();
	}

	/**
	 * Set the type
	 * 
	 * @param type
	 *            The type of collection being described
	 */
	public void setType(String type) {
		super.setAttributeValue(Constants.ATTRIBUTE_TYPE, type);
	}

	/**
	 * return the type
	 * 
	 * @return The type attribute value or empty string if attribute is empty or
	 *         not present
	 */
	public String getType() {
		return super.getAttributeValue(Constants.ATTRIBUTE_TYPE);
	}

	/**
	 * Set the date the collection metadata was recorded in the system from
	 * which the RIF-CS is being constructed
	 * 
	 * @param date
	 *            A date object representing the date the collection metadata
	 *            was recorded in the catalog system
	 */
	public void setDateAccessioned(Date date) {
		super.setAttributeValue(Constants.ATTRIBUTE_DATE_ACCESSIONED, RegistryObject.formatDate(date));
	}

	/**
	 * Set the date the collection metadata was recorded in the system from
	 * which the RIF-CS is being constructed
	 * 
	 * @param date
	 *            A string in UTC and of one of the forms described in section
	 *            3.2.7 of the <a href="http://www.w3.org/TR/xmlschema-2/">W3C's
	 *            Schema Data Types document</a>
	 */
	public void setDateAccessioned(String date) {
		super.setAttributeValue(Constants.ATTRIBUTE_DATE_ACCESSIONED, date);
	}

	/**
	 * return the date accessioned
	 * 
	 * @return The dateAccessioned attribute value or empty string if attribute
	 *         is empty or not present
	 */
	public String getDateAccessioned() {
		return super.getAttributeValue(Constants.ATTRIBUTE_DATE_ACCESSIONED);
	}

	/**
	 * Set the date the collection metadata was modified
	 * 
	 * @param date
	 *            A date object representing the date the collection metadata
	 *            was last modified
	 */
	public void setDateModified(Date date) {
		super.setAttributeValue(Constants.ATTRIBUTE_DATE_MODIFIED, RegistryObject.formatDate(date));
	}

	/**
	 * Set the date the collection metadata was last modified
	 * 
	 * @param date
	 *            A string in UTC and of one of the forms described in section
	 *            3.2.7 of the <a href="http://www.w3.org/TR/xmlschema-2/">W3C's
	 *            Schema Data Types document</a>
	 */
	public void setDateModified(String date) {
		super.setAttributeValue(Constants.ATTRIBUTE_DATE_MODIFIED, date);
	}

	/**
	 * return the date modified
	 * 
	 * @return The dateModified attribute value or empty string if attribute is
	 *         empty or not present
	 */
	public String getDateModified() {
		return super.getAttributeValue(Constants.ATTRIBUTE_DATE_MODIFIED);
	}

	/**
	 * Create and return an empty Identifier object.
	 * 
	 * The returned object has no properties or content and is not part of the
	 * RIF-CS document, it is essentially a constructor of an object owned by
	 * the RIF-CS document. The returned object needs to be "filled out" (e.g.
	 * with properties, additional sub-elements, etc) before being added to the
	 * RIF-CS document.
	 * 
	 * @exception RIFCSException
	 * 
	 */
	public Identifier newIdentifier() throws RIFCSException {
		return new Identifier(this.newElement(Constants.ELEMENT_IDENTIFIER));
	}

	/**
	 * Add an identifier to the collection object
	 * 
	 * @param identifier
	 *            an Identifier object
	 */
	public void addIdentifier(Identifier identifier) {
		this.getElement().appendChild(identifier.getElement());
		this.identifiers.add(identifier);
	}

	/**
	 * Convenience method to add an identifier to the collection object
	 * 
	 * @param identifier
	 *            an identifier string
	 * @param type
	 *            the identifier type
	 * 
	 * @exception RIFCSException
	 * 
	 */
	public void addIdentifier(String identifier, String type) throws RIFCSException {
		Identifier i = newIdentifier();
		i.setType(type);
		i.setValue(identifier);
		this.getElement().appendChild(i.getElement());
		this.identifiers.add(i);
	}

	/**
	 * Obtain the identifiers for this collection
	 * 
	 * @return A list of Identifier objects
	 */
	public List<Identifier> getIdentifiers() {
		return identifiers;
	}

	/**
	 * Create and return an empty Name object.
	 * 
	 * The returned object has no properties or content and is not part of the
	 * RIF-CS document, it is essentially a constructor of an object owned by
	 * the RIF-CS document. The returned object needs to be "filled out" (e.g.
	 * with properties, additional sub-elements, etc) before being added to the
	 * RIF-CS document.
	 * 
	 * @exception RIFCSException
	 * 
	 */
	public Name newName() throws RIFCSException {
		return new Name(this.newElement(Constants.ELEMENT_NAME));
	}

	/**
	 * Add a name to the collection object
	 * 
	 * @param name
	 *            a Name object
	 */
	public void addName(Name name) {
		this.getElement().appendChild(name.getElement());
		this.names.add(name);
	}

	/**
	 * Obtain the names for this collection
	 * 
	 * @return A list of Name objects
	 */
	public List<Name> getNames() {
		return names;
	}

	/**
	 * Create and return an empty Location object.
	 * 
	 * The returned object has no properties or content and is not part of the
	 * RIF-CS document, it is essentially a constructor of an object owned by
	 * the RIF-CS document. The returned object needs to be "filled out" (e.g.
	 * with properties, additional sub-elements, etc) before being added to the
	 * RIF-CS document.
	 * 
	 * @exception RIFCSException
	 * 
	 */
	public Location newLocation() throws RIFCSException {
		return new Location(this.newElement(Constants.ELEMENT_LOCATION));
	}

	/**
	 * Add a location to the collection object
	 * 
	 * @param location
	 *            a Location object
	 */
	public void addLocation(Location location) {
		this.getElement().appendChild(location.getElement());
		this.locations.add(location);
	}

	/**
	 * Obtain the locations for this collection
	 * 
	 * @return A list of Location objects
	 */
	public List<Location> getLocations() {
		return locations;
	}

	/**
	 * Create and return an empty Coverage object.
	 * 
	 * The returned object has no properties or content and is not part of the
	 * RIF-CS document, it is essentially a constructor of an object owned by
	 * the RIF-CS document. The returned object needs to be "filled out" (e.g.
	 * with properties, additional sub-elements, etc) before being added to the
	 * RIF-CS document.
	 * 
	 * @exception RIFCSException
	 * 
	 */
	public Coverage newCoverage() throws RIFCSException {
		return new Coverage(this.newElement(Constants.ELEMENT_COVERAGE));
	}

	/**
	 * Add a coverage element to the collection object
	 * 
	 * @param coverage
	 *            a Coverage object
	 */
	public void addCoverage(Coverage coverage) {
		this.getElement().appendChild(coverage.getElement());
		this.coverages.add(coverage);
	}

	/**
	 * Obtain the coverage for this collection
	 * 
	 * @return A list of coverage objects
	 */
	public List<Coverage> getCoverage() {
		return coverages;
	}

	/**
	 * Create and return an empty RelatedObject object.
	 * 
	 * The returned object has no properties or content and is not part of the
	 * RIF-CS document, it is essentially a constructor of an object owned by
	 * the RIF-CS document. The returned object needs to be "filled out" (e.g.
	 * with properties, additional sub-elements, etc) before being added to the
	 * RIF-CS document.
	 * 
	 * @exception RIFCSException
	 * 
	 */
	public RelatedObject newRelatedObject() throws RIFCSException {
		return new RelatedObject(this.newElement(Constants.ELEMENT_RELATED_OBJECT));
	}

	/**
	 * Add a related object to the collection object
	 * 
	 * @param relatedObject
	 *            an RelatedObject object
	 */
	public void addRelatedObject(RelatedObject relatedObject) {
		this.getElement().appendChild(relatedObject.getElement());
		this.relatedObjects.add(relatedObject);
	}

	/**
	 * Obtain the related objects for this collection
	 * 
	 * @return A list of RelatedObject objects
	 */
	public List<RelatedObject> getRelatedObjects() {
		return relatedObjects;
	}

	/**
	 * Create and return an empty Subject object.
	 * 
	 * The returned object has no properties or content and is not part of the
	 * RIF-CS document, it is essentially a constructor of an object owned by
	 * the RIF-CS document. The returned object needs to be "filled out" (e.g.
	 * with properties, additional sub-elements, etc) before being added to the
	 * RIF-CS document.
	 * 
	 * @exception RIFCSException
	 * 
	 */
	public Subject newSubject() throws RIFCSException {
		return new Subject(this.newElement(Constants.ELEMENT_SUBJECT));
	}

	/**
	 * Add a subject to the collection object
	 * 
	 * @param subject
	 *            a Subject object
	 */
	public void addSubject(Subject subject) {
		this.getElement().appendChild(subject.getElement());
		this.subjects.add(subject);
	}

	/**
	 * Convenience method to add a subject to the collection object
	 * 
	 * @param subject
	 *            a subject string
	 * @param type
	 *            the subject type
	 * @param language
	 *            the subject language or null
	 * 
	 * @exception RIFCSException
	 */
	public void addSubject(String subject, String type, String language) throws RIFCSException {
		Subject s = newSubject();
		s.setType(type);
		s.setValue(subject);
		if (language != null) {
			s.setLanguage(language);
		}
		this.getElement().appendChild(s.getElement());
		this.subjects.add(s);
	}

	/**
	 * Obtain the subjects for this collection
	 * 
	 * @return A list of Subject objects
	 */
	public List<Subject> getSubjects() {
		return subjects;
	}

	/**
	 * Create and return an empty Description object.
	 * 
	 * The returned object has no properties or content and is not part of the
	 * RIF-CS document, it is essentially a constructor of an object owned by
	 * the RIF-CS document. The returned object needs to be "filled out" (e.g.
	 * with properties, additional sub-elements, etc) before being added to the
	 * RIF-CS document.
	 * 
	 * @exception RIFCSException
	 * 
	 */
	public Description newDescription() throws RIFCSException {
		return new Description(this.newElement(Constants.ELEMENT_DESCRIPTION));
	}

	/**
	 * Add a description to the collection object
	 * 
	 * @param description
	 *            a Description object
	 */
	public void addDescription(Description description) {
		this.getElement().appendChild(description.getElement());
		this.descriptions.add(description);
	}

	/**
	 * Convenience method to add a description to the collection object
	 * 
	 * @param description
	 *            a description string
	 * @param type
	 *            the description type
	 * @param language
	 *            the description language or null
	 * 
	 * @exception RIFCSException
	 */
	public void addDescription(String description, String type, String language) throws RIFCSException {
		Description d = newDescription();
		d.setType(type);
		d.setValue(description);
		if (language != null) {
			d.setLanguage(language);
		}
		this.getElement().appendChild(d.getElement());
		this.descriptions.add(d);
	}

	/**
	 * Obtain the description for this collection
	 * 
	 * @return A list of Description objects
	 */
	public List<Description> getDescriptions() {
		return descriptions;
	}

	public Right newRight(){
		Right right= null;
		try {
			right = new Right(this.newElement(Constants.ELEMENT_RIGHTS));
		} catch (RIFCSException e) {
			e.printStackTrace();
		}
		return right;
	}


	/**
	 * Add a Rights element to the collection object
	 * 
	 * @param Right
	 *            a Rights object
	 */
	public void addRight(Right right) {
		this.getElement().appendChild(right.getElement());
		this.rightsList.add(right);
	}


	/**
	 * Obtain the Rights for this collection
	 * 
	 * @return A list of Rights objects
	 */
	public List<Right> getRightsList() {
		return rightsList;
	}

	/**
	 * Create and return an empty RelatedInfo object.
	 * 
	 * The returned object has no properties or content and is not part of the
	 * RIF-CS document, it is essentially a constructor of an object owned by
	 * the RIF-CS document. The returned object needs to be "filled out" (e.g.
	 * with properties, additional sub-elements, etc) before being added to the
	 * RIF-CS document.
	 * 
	 * @exception RIFCSException
	 * 
	 */
	public RelatedInfo newRelatedInfo() throws RIFCSException {
		return new RelatedInfo(this.newElement(Constants.ELEMENT_RELATED_INFO));
	}

	/**
	 * Add related info to the collection object
	 * 
	 * @param relatedInfo
	 *            a relatedInfo object
	 */
	public void addRelatedInfo(RelatedInfo relatedInfo) {
		this.getElement().appendChild(relatedInfo.getElement());
		this.ris.add(relatedInfo);
	}

	/**
	 * Convenience method to add related info containing a single URL identifier
	 * to the collection object
	 * 
	 * @param relatedInfoURI
	 *            a relatedInfo URI
	 * 
	 * @exception RIFCSException
	 * @deprecated Use the newRelatedInfo() method to construct relatedInfo.
	 *             This method will be removed in a future release. If used it
	 *             will create a single identifier of type uri within the
	 *             related info element.
	 */
	@Deprecated
	public void addRelatedInfo(String relatedInfoURI) throws RIFCSException {
		RelatedInfo ri = newRelatedInfo();
		ri.setIdentifier(relatedInfoURI, "uri");
		this.getElement().appendChild(ri.getElement());
		this.ris.add(ri);
	}

	/**
	 * Obtain the related info for this collection
	 * 
	 * @return A list of RelatedInfo objects
	 */
	public List<RelatedInfo> getRelatedInfo() {
		return ris;
	}

	public CitationInfo newCitationInfo() throws RIFCSException {
		return new CitationInfo(this.newElement(Constants.ELEMENT_CITATIONINFO));
	}

	public void addCitationInfo(CitationInfo citationInfo) {
		this.getElement().appendChild(citationInfo.getElement());
		this.cis.add(citationInfo);
	}

	public List<CitationInfo> getCitationInfos() {
		return this.cis;
	}

	/* initialisation code for existing documents */
	private void initStructures() throws RIFCSException {
		initIdentifiers();
		initNames();
		initLocations();
		initCoverage();
		initRelatedObjects();
		initSubjects();
		initDescriptions();
		initRelatedInfo();
		initCitationInfo();

	}

	private void initIdentifiers() throws RIFCSException {
		NodeList nl = super.getElements(Constants.ELEMENT_IDENTIFIER);

		for (int i = 0; i < nl.getLength(); i++) {
			identifiers.add(new Identifier(nl.item(i)));
		}
	}

	private void initNames() throws RIFCSException {
		NodeList nl = super.getElements(Constants.ELEMENT_NAME);
		for (int i = 0; i < nl.getLength(); i++) {
			names.add(new Name(nl.item(i)));
		}
	}

	private void initLocations() throws RIFCSException {
		NodeList nl = super.getElements(Constants.ELEMENT_LOCATION);

		for (int i = 0; i < nl.getLength(); i++) {
			locations.add(new Location(nl.item(i)));
		}
	}

	private void initCoverage() throws RIFCSException {
		NodeList nl = super.getElements(Constants.ELEMENT_COVERAGE);

		for (int i = 0; i < nl.getLength(); i++) {
			coverages.add(new Coverage(nl.item(i)));
		}
	}

	private void initRelatedObjects() throws RIFCSException {
		NodeList nl = super.getElements(Constants.ELEMENT_RELATED_OBJECT);

		for (int i = 0; i < nl.getLength(); i++) {
			relatedObjects.add(new RelatedObject(nl.item(i)));
		}
	}

	private void initSubjects() throws RIFCSException {
		NodeList nl = super.getElements(Constants.ELEMENT_SUBJECT);

		for (int i = 0; i < nl.getLength(); i++) {
			subjects.add(new Subject(nl.item(i)));
		}
	}

	private void initDescriptions() throws RIFCSException {
		NodeList nl = super.getElements(Constants.ELEMENT_DESCRIPTION);

		for (int i = 0; i < nl.getLength(); i++) {
			descriptions.add(new Description(nl.item(i)));
		}
	}

	private void initRelatedInfo() throws RIFCSException {
		NodeList nl = super.getElements(Constants.ELEMENT_RELATED_INFO);

		for (int i = 0; i < nl.getLength(); i++) {
			ris.add(new RelatedInfo(nl.item(i)));
		}
	}

	private void initCitationInfo() throws RIFCSException {
		NodeList nl = super.getElements(Constants.ELEMENT_CITATIONINFO);

		for (int i = 0; i < nl.getLength(); i++) {
			cis.add(new CitationInfo(nl.item(i)));
		}
	}

}