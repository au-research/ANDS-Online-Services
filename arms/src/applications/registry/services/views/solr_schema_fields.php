<?php 

/**
 * Show SOLR Schema Fields
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/services/controllers/services
 * @package ands/services
 * 
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
      
<section id="registry-solr-schema">
      
<div class="row">
      <div class="span9" id="registry-solr-schema-left">
            <div class="box">
                  <div class="box-header clearfix">
                        <h1>Query Schema <small>(SOLR schema)</small></h1>
                  </div>
            
                        
                      <br /><h4>
                        slug
                      </h4>
                      <p>
                        <span class="provided">The unique URL "slug" which is appended to the base url of
                        the portal. For example:
                        http://researchdata.ands.org.au/this_is_the_url_slug</span>
                      </p>
                      <br /><h4>
                        fulltext
                      </h4>
                      <p>
                        <span class="provided">A full-text search of the entire record (and ANDS-inferred
                        values)</span> <span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        display_title
                      </h4>
                      <p>
                        <span class="provided">The title of the record (built from it's nameParts</span>
                      </p>
                      <br /><h4>
                        list_title
                      </h4>
                      <p>
                        <span class="provided">The alternate title of the record used when the record is
                        displayed in a list</span>
                      </p>
                      <br /><h4>
                        class
                      </h4>
                      <p>
                        <span class="provided">The class of the record (one of "collection", "party",
                        "activity", "service")</span>
                      </p>
                      <br /><h4>
                        key
                      </h4>
                      <p>
                        <span class="provided">The key of the record, provided in the RIFCS &lt;key&gt;
                        element</span>
                      </p>
                      <br /><h4>
                        data_source_key
                      </h4>
                      <p>
                        <span class="provided">The key of the data source which provided this record</span>
                      </p>
                      <br /><h4>
                        timestamp
                      </h4>
                      <p>
                        <span class="inferred">The W3CDTF timestamp representing when the record was most
                        recently updated</span> <span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        group
                      </h4>
                      <p>
                        <span class="provided">The group attribute of the registry object (usually the name
                        of the institution/provider)</span>
                      </p>
                      <br /><h4>
                        type
                      </h4>
                      <p>
                        <span class="provided">The registryObject type attribute (i.e. "dataset"
                        (collections) or "group" (for parties))</span>
                      </p>
                      <br /><h4>
                        description_value
                      </h4>
                      <p>
                        <span class="provided">The values of description elements in the
                        record.</span><span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        description_type
                      </h4>
                      <p>
                        <span class="provided">The types of description elements in the
                        record.</span><span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        subject_value_unresolved
                      </h4>
                      <p>
                        <span class="provided">The subject value provided to ANDS (usually an anzsrc code
                        in notation form (i.e. "020103")).</span><span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        subject_type
                      </h4>
                      <p>
                        <span class="provided">The type value of the subject (if any) (i.e.
                        "anzsrc-for").</span><span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        subject_value_resolved
                      </h4>
                      <p>
                        <span class="inferred">The resolved value of the subject (if any) (i.e.
                        "MATHEMATICAL SCIENCES").</span><span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        subject_vocab_uri
                      </h4>
                      <p>
                        <span class="inferred">The resolved URI of the subject (if it is matched in a
                        supported vocabulary).</span><span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        identifier_value
                      </h4>
                      <p>
                        <span class="provided">The identifier value provided to ANDS.</span><span class=
                        "multivalued">*</span>
                      </p>
                      <br /><h4>
                        identifier_type
                      </h4>
                      <p>
                        <span class="provided">The type of the identifier provided to
                        ANDS.</span><span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        spatial
                      </h4>
                      <p>
                        <span class="provided">The spatial coverage of this registry object (TODO: rename
                        and change!)</span><span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        related_object_key
                      </h4>
                      <p>
                        <span class="provided">The key of records which this record relates to (note: does
                        NOT include inferred/reverse links)</span> <span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        related_object_class
                      </h4>
                      <p>
                        <span class="provided">The class of records which this record relates to (provided
                        they exist in the registry) (i.e. "collection", "party", etc.).</span> <span class=
                        "multivalued">*</span>
                      </p>
                      <br /><h4>
                        related_object_type
                      </h4>
                      <p>
                        <span class="provided">The type of records which this record relates to (provided
                        they exist in the registry).</span> <span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        related_object_display_title
                      </h4>
                      <p>
                        <span class="provided">The title for records which this record relates to (provided
                        they exist in the registry)</span> <span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        related_object_relation
                      </h4>
                      <p>
                        <span class="provided">The nature of the relation (i.e. "isPartOf")</span>
                        <span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        related_object_list_title
                      </h4>
                      <p>
                        <span class="provided">The nature of the relation (i.e. "isPartOf")</span>
                        <span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        related_object_display_logo
                      </h4>
                      <p>
                        <span class="provided">The nature of the relation (i.e. "isPartOf")</span>
                        <span class="multivalued">*</span>
                      </p>
                      <br /><h4>
                        originating_source
                      </h4>
                      <p>
                        <span class="provided">The originating source for this record</span>
                      </p>
                      <br /><h4>
                        id
                      </h4>
                      <p>
                        <span class="internal">The unique registry id of the record (generated internally
                        during the ingest process)</span>
                      </p>
                      <br /><h4>
                        data_source_id
                      </h4>
                      <p>
                        <span class="internal">The id for the data_source (generated internally when the
                        data source is created)</span>
                      </p>
                      <br /><h4>
                        status
                      </h4>
                      <p>
                        <span class="internal">The status of the record in the registry (only PUBLISHED
                        records are currently indexed)</span>
                      </p>
            </div>
      </div>
</div>
<?php $this->load->view('footer');?>