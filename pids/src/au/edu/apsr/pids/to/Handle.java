/**
 * Date Modified: $Date: 2010-06-30 15:19:58 +1000 (Wed, 30 Jun 2010) $
 * Version: $Revision: 447 $
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
package au.edu.apsr.pids.to;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import net.handle.hdllib.AbstractMessage;
import net.handle.hdllib.AbstractResponse;
import net.handle.hdllib.AddValueRequest;
import net.handle.hdllib.AdminRecord;
import net.handle.hdllib.Common;
import net.handle.hdllib.CreateHandleRequest;
import net.handle.hdllib.RemoveValueRequest;
import net.handle.hdllib.Encoder;
import net.handle.hdllib.HandleException;
import net.handle.hdllib.HandleResolver;
import net.handle.hdllib.HandleValue;
import net.handle.hdllib.ModifyValueRequest;
import net.handle.hdllib.PublicKeyAuthenticationInfo;
import net.handle.hdllib.ResolutionRequest;
import net.handle.hdllib.ResolutionResponse;
import net.handle.hdllib.Resolver;
import net.handle.hdllib.Util;

import au.edu.apsr.pids.dao.DAOException;
import au.edu.apsr.pids.dao.HandleDAO;
import au.edu.apsr.pids.servlet.MintServlet;
import au.edu.apsr.pids.util.HandleConfig;
import au.edu.apsr.pids.util.Constants;
import au.edu.apsr.pids.util.HandleSupport;
import au.edu.apsr.pids.util.ProcessingException;
import au.edu.apsr.pids.util.ServletSupport;

import org.apache.log4j.Logger;

/**
 * Class representing a Handle object
 * 
 * @author Scott Yeadon, ANU 
 */
public class Handle
{
    private static final Logger log = Logger.getLogger(Handle.class);

    private String handle;
    private HandleConfig handleConfig = null;
    private Resolver resolver = new Resolver();
    private HashMap<String,String> hm = new HashMap<String,String>();
    
    /**
     * Private constructor of a Handle object. Handles are created via other
     * method calls rather than constructed directly.
     */
    private Handle()
    {
        this.handleConfig = HandleConfig.getHandleConfig();
        
        // support workaround for HANDLE_NOT_FOUND_EXCEPTION
        hm.put(Constants.STD_TYPE_URL_STRING, Constants.STD_TYPE_URL_STRING);
        hm.put(Constants.XT_TYPE_DESC_STRING, Constants.XT_TYPE_DESC_STRING);
    }

    
    /** 
     * Create a Handle object object using the provided Identifier and
     * handle values
     * 
     * @return Handle
     *           the Handle object
     * @param identifier
     *           the agent Identifier object
     * @param hv
     *           an array of HandleValue objects to be added to the handle
     * @throws DAOException
     * @throws HandleException
     */
    public static Handle create(Identifier identifier,
                                HandleValue[] hv) throws DAOException, HandleException
    {
        Handle handleObject = new Handle();
        
        HandleConfig hc = HandleConfig.getHandleConfig();
        handleObject.setHandle(hc.getPrefix() + '/' + Handle.getNextSuffix());
        
        String idHash = null;
        AdminRecord admin = handleObject.createAdminRecord(Constants.NA_HANDLE_PREFIX + hc.getPrefix(), Constants.ADMIN_GROUP_IDX);
        
        HandleValue values[] = new HandleValue[hv.length + 2];

        // load the passed values into the value array
        int i;
        
        for (i = 0; i < hv.length; i++)
        {
            values[i] = hv[i];
        }
        
        // add the admin values
        values[i] = new HandleValue();
        values[i].setIndex(Constants.ADMIN_IDX);
        values[i].setType(Common.STD_TYPE_HSADMIN);
        values[i].setData(Encoder.encodeAdminRecord(admin));
        values[i].setTTL(Constants.DEFAULT_TTL);
        
        values[i+1] = new HandleValue();
        values[i+1].setIndex(Constants.AGENT_IDX);
        values[i+1].setType(Constants.XT_AGENTID);
        values[i+1].setData(Util.encodeString(identifier.getHandle()));
        values[i+1].setTTL(Constants.DEFAULT_TTL);
        
        AbstractResponse response = handleObject.createHandle(values);

        if (response.responseCode == AbstractMessage.RC_SUCCESS)
        {
            log.info("Successfully created handle: " + handleObject.getHandle());
            return handleObject;
        }
        else
        {
            log.info("Failed to create handle: " + response);
            return null;
        }
    }

    
    /** 
     * Create an agent admin Handle object object using the provided identifier
     * string and authentication domain string
     * 
     * @return Handle
     *           the Handle object
     * @param identifier
     *           the agent identifier string
     * @param authDomain
     *           the agent authentication domain string
     * @throws DAOException
     * @throws HandleException
     */
    public static Handle createAdmin(String identifier,
                                     String authDomain) throws DAOException, HandleException
    {
        Handle handleObject = new Handle();

        HandleConfig hc = HandleConfig.getHandleConfig();

        handleObject.setHandle(hc.getPrefix() + '/' + Handle.getNextSuffix());

        AdminRecord admin = handleObject.createAdminRecord(Constants.NA_HANDLE_PREFIX + hc.getPrefix(), Constants.ADMIN_GROUP_IDX);

        HandleValue values[] = new HandleValue[3];

        values[0] = new HandleValue();
        values[0].setIndex(Constants.AGENT_DESC_IDX);
        values[0].setType(Constants.XT_TYPE_DESC);
        values[0].setAnyoneCanRead(false);
        values[0].setData(Util.encodeString(identifier + Identifier.separator + authDomain));
        values[0].setTTL(Constants.DEFAULT_TTL);
        
        values[1] = new HandleValue();
        values[1].setIndex(Constants.ADMIN_IDX);
        values[1].setType(Common.STD_TYPE_HSADMIN);
        values[1].setData(Encoder.encodeAdminRecord(admin));
        values[1].setTTL(Constants.DEFAULT_TTL);
        
        values[2] = new HandleValue();
        values[2].setIndex(Constants.AGENT_IDX);
        values[2].setType(Constants.XT_AGENTID);
        values[2].setData(Util.encodeString(handleObject.getHandle()));
        values[2].setTTL(Constants.DEFAULT_TTL);

        AbstractResponse response = handleObject.createHandle(values);

        if (response.responseCode == AbstractMessage.RC_SUCCESS)
        {
            log.info("Successfully created admin handle: " + handleObject.getHandle());
            return handleObject;
        }
        else
        {
            log.info("Failed to create admin handle: " + response);
            return null;
        }
    }

    
    /**
     * Create the NA admin record for a new handle. The NA admin is provided
     * all permissions bar ADD_NA and DELETE_NA 
     * 
     * @return AdminRecord
     *           an AdminRecord object representing the NA handle admin
     * @param handle
     *           the NA admin handle in byte form
     * @param idx
     *           the handle index the of the NA handle's HS_VLIST entry
     */    
     public AdminRecord createAdminRecord(String handle, int idx)
     {
         return new AdminRecord(Util.encodeString(handle),
                               idx,
                               AdminRecord.PRM_ADD_HANDLE,
                               AdminRecord.PRM_DELETE_HANDLE,
                               AdminRecord.PRM_NO_ADD_NA,
                               AdminRecord.PRM_NO_DELETE_NA,
                               AdminRecord.PRM_READ_VALUE,
                               AdminRecord.PRM_MODIFY_VALUE,
                               AdminRecord.PRM_REMOVE_VALUE,
                               AdminRecord.PRM_ADD_VALUE,
                               AdminRecord.PRM_MODIFY_ADMIN,
                               AdminRecord.PRM_REMOVE_ADMIN,
                               AdminRecord.PRM_ADD_ADMIN,
                               AdminRecord.PRM_LIST_HANDLES);
    }
    
    
    /**
     * Add one or more values to the handle 
     * 
     * @return boolean
     *           <code>true</code> if the value was added else <code>false</code>
     * @param value
     *           an array of HandleValue objects
     * @throws HandleException
     */
    public boolean addValue(HandleValue[] value) throws DAOException, HandleException
    {
        if (value.length < 1)
        {
            log.error("Empty value, unable to add value");
            return false;
        }
        
        for (int i = 0; i < value.length; i++)
        {
            if (value[i].getTypeAsString().equals("URL"))
            {
                if (!HandleSupport.isValidURL(value[i].getDataAsString()))
                {
                    log.error("Invalid value for URL type: " + value[i].getDataAsString());
                    return false;
                }                
            }
        }
        
        byte idHandle[] = Util.encodeString(Constants.NA_HANDLE_PREFIX + handleConfig.getPrefix());
        
        PublicKeyAuthenticationInfo pubKeyAuthInfo = new PublicKeyAuthenticationInfo(idHandle,
              Constants.SEC_KEY_IDX,
              handleConfig.getPrivateKey());
        
        // if index has not been set, set it
        if (value[0].getIndex() == -1)
        {
            // get the next available index, not overly scalable but unlikely to hit
            // issues unless huge number of indexes for single handle
            int nextIndex = 0;
            Integer[] indexes = getSortedIndexes();
            boolean foundIndex = false;
            while (!foundIndex)
            {
                nextIndex++;
                
                while (HandleSupport.isIndexReserved(nextIndex))
                {
                    nextIndex++;
                }
                
                int i;            
                for (i=0; i<indexes.length; i++)
                {
                    if (nextIndex == indexes[i].intValue())
                    {
                        break;
                    }
                }
                
                if (i==indexes.length)
                {
                    foundIndex = true;
                }
            }
            value[0].setIndex(nextIndex);
        }

        // do the add request
        AddValueRequest req = new AddValueRequest(Util.encodeString(this.getHandle()), value, pubKeyAuthInfo);
        
        AbstractResponse response = resolver.getResolver().processRequest(req);
        
        if (response.responseCode == AbstractMessage.RC_SUCCESS)
        {
            return true;
        }
        else
        {
            log.error("Error adding handle value to handle " + getHandle() + ": " + AbstractMessage.getResponseCodeMessage(response.responseCode));
            return false;
        }
    }


    /**
     * Delete a value from the handle 
     * 
     * @return boolean
     *           <code>true</code> if the value was deleted else <code>false</code>
     * @param index
     *           the index of the value to delete
     * @throws HandleException
     */
    public boolean deleteValue(int index) throws HandleException
    {
        byte idHandle[] = Util.encodeString(Constants.NA_HANDLE_PREFIX + handleConfig.getPrefix());
        
        PublicKeyAuthenticationInfo pubKeyAuthInfo = new PublicKeyAuthenticationInfo(idHandle,
              Constants.SEC_KEY_IDX,
              handleConfig.getPrivateKey());
                
        RemoveValueRequest req = new RemoveValueRequest(Util.encodeString(this.getHandle()), index, pubKeyAuthInfo);
        
        AbstractResponse response = resolver.getResolver().processRequest(req);
        
        if (response.responseCode == AbstractMessage.RC_SUCCESS)
        {
            return true;
        }
        else
        {
            log.error("Error deleting handle value from handle " + getHandle() + ": " + AbstractMessage.getResponseCodeMessage(response.responseCode));
            return false;
        }
    }


    /**
     * Modify a handle value 
     * 
     * @return boolean
     *           <code>true</code> if the value was deleted else <code>false</code>
     * @param index
     *           the index of the value to modify
     * @param value
     *           a string value to replace the existing value
     * @throws HandleException
     */
    public boolean modifyValue(int index,
                               String value) throws HandleException
    {
        boolean modified = false;
        
        HandleValue[] hv = getValues(index);
        if (hv.length == 0)
        {
            return modified;
        }
        
        if (hv[0].getTypeAsString().equals("URL"))
        {
            if (!HandleSupport.isValidURL(value))
            {
                log.error("Invalid value for URL type: " + value);
                return modified;
            }
        }
        hv[0].setData(Util.encodeString(value));
        hv[0].setTTL(Constants.DEFAULT_TTL);
        
        byte idHandle[] = Util.encodeString(Constants.NA_HANDLE_PREFIX + handleConfig.getPrefix());
        
        PublicKeyAuthenticationInfo pubKeyAuthInfo = new PublicKeyAuthenticationInfo(idHandle,
              Constants.SEC_KEY_IDX,
              handleConfig.getPrivateKey());
                
        ModifyValueRequest req = new ModifyValueRequest(Util.encodeString(this.getHandle()), hv, pubKeyAuthInfo);
        
        AbstractResponse response = resolver.getResolver().processRequest(req);
        
        if (response.responseCode == AbstractMessage.RC_SUCCESS)
        {
            modified = true;
        }
        else
        {
            log.error("Error modifying handle value for " + getHandle() + ": " + AbstractMessage.getResponseCodeMessage(response.responseCode));
            modified = false;
        }
        
        return modified;
    }

    
    /**
     * Obtain the values of the handle 
     * 
     * @return HandleValue[]
     *           An array of HandleValue objects
     * @throws HandleException
     */
    public HandleValue[] getValues() throws HandleException
    {
        return resolver.resolveHandle(this.getHandle());
    }


    /**
     * Obtain the handle value at the provided index 
     * 
     * @return HandleValue[]
     *           An array comprising a single HandleValue object
     * @param index
     *          The index of the value to return
     * @throws HandleException
     */
    public HandleValue[] getValues(int index) throws HandleException
    {
        int[] indexes = {index};
        return getValues(indexes);
    }


    /**
     * Obtain the handle values at the provided indexes 
     * 
     * @return HandleValue[]
     *           An array of HandleValue objects
     * @param indexes
     *          An array of indexes whose values are to be returned
     * @throws HandleException
     */
    public HandleValue[] getValues(int[] indexes) throws HandleException
    {
        return resolver.resolveHandle(this.getHandle(), null, indexes, false);
    }
    
    
    /**
     * Obtain the handle values of the provided types 
     * 
     * @return HandleValue[]
     *           An array of HandleValue objects
     * @param types
     *          An array of types whose values are to be returned
     * @throws HandleException
     */
    public HandleValue[] getValues(String[] types) throws HandleException
    {
        //return resolver.resolveHandle(this.getHandle(), types, null, false);
        HandleValue[] hvs = resolver.resolveHandle(this.getHandle());
        return resolveAllowedValues(hvs);
    }

    
    /**
     * Set the handle string 
     * 
     * @param handle
     *          A handle string
     */
    public void setHandle(String handle)
    {
       this.handle = handle;
    }


    /**
     * Get the handle string of this Handle object 
     * 
     * @return String
     *          the handle string
     */
     public String getHandle()
     {
         return this.handle;
     }
     
     
     /**
      * Get the next handle suffix value
      * 
      * @return long
      *          the next available suffix value
      * @throws DAOException
      */
     // TODO: Support other suffix formats by creating a Suffix interface
     // Current implementation will be long but should be able to add later
     // on different suffix types without major code changes.
     public static long getNextSuffix() throws DAOException
     {
         HandleDAO hdao = new HandleDAO();
         return hdao.getNextSuffix();
     }


     /**
      * Get a list of handles matching a data value
      * 
     * @param data
     *          A string contained within hamdles handles are to be returned
     * @param type
     *          The handle value type (or null if all types)
     * @param pubReadOnly
     *          Only include publicly readable values
      * @return List<Handle>
      *          A list of handles with data matching the string. If type 
      *          is provided only matches within that type will be returned
      * @throws DAOException
      */
     public static List<Handle> getHandlesByData(String data,
                                                 String type,
                                                 boolean pubReadOnly) throws HandleException, DAOException
     {
         HandleDAO hdao = new HandleDAO();
         List<String> l = hdao.getHandlesByData(data, type, pubReadOnly);
         List<Handle> handleObjects = new ArrayList<Handle>();
         
         try
         {
             for (Iterator<String> i = l.iterator(); i.hasNext();)
             {
                 handleObjects.add(Handle.find(i.next()));
             }
         }
         catch (HandleException he)
         {
             log.info("Handle exception caught", he);
         }
         
         return handleObjects;
     }

     
     /**
     * Obtain the Handle object associated with the provided handle string  
     * 
     * @return Handle
     *           The Handle object if found else <code>null</code>
     * @param handleString
     *          A handle string
     * @throws HandleException
     */
     public static Handle find(String handleString) throws HandleException
     {
         Handle handle = null;
         
         try
         {
             Resolver resolver = new Resolver();
             HandleValue[] hv = resolver.resolveHandle(handleString);
             if (hv.length > 0)
             {
                 handle = new Handle();
                 handle.setHandle(handleString);
             }
         }
         catch (HandleException he)
         {
             log.info("Handle exception caught", he);
         }
         
         return handle;
     }
          

     /**
      * Obtain a List of Handle objects belonging to the provided Identifier
      *  
      * 
      * @return List&lt;Handle&gt;
      *           A list of Handle Objects
      * @param identifier
      *          The Identifier object representing the agent whose handles
      *          are to be returned
      * @throws HandleException
      * @throws DAOException
      */
     public static List<String> getHandleStrings(Identifier identifier,
                                                 String startHandle) throws HandleException, DAOException
     {
         Handle handle = null;
         HandleDAO hdao = new HandleDAO();
         return hdao.getHandles(identifier, startHandle);
     }

     
     /**
      * Obtain a List of Handle objects belonging to the provided Identifier
      *  
      * 
      * @return List&lt;Handle&gt;
      *           A list of Handle Objects
      * @param identifier
      *          The Identifier object representing the agent whose handles
      *          are to be returned
      * @param token
      *          A resumption token for the listing
      * @throws HandleException
      * @throws DAOException
      */
/*     public static List<Handle> getHandles(Identifier identifier,
                                           String token) throws HandleException, DAOException
     {
         Handle handle = null;
         HandleDAO hdao = new HandleDAO();
         List<String> handles = hdao.getHandles(identifier, token);
         List<Handle> handleObjects = new ArrayList<Handle>();
         
         try
         {
             for (Iterator<String> i = handles.iterator(); i.hasNext();)
             {
                 handleObjects.add(Handle.find(i.next()));
             }
         }
         catch (HandleException he)
         {
             log.info("Handle exception caught", he);
         }
         
         return handleObjects;
     }*/
     
     
     /**
      * Create a new handle record on the handle server 
      * 
      * @return AbstractResponse
      *           the handle server response to the create request
      * @param HandleValue[]
      *          An array of HandleValue objects
      * @throws HandleException
      */
     private AbstractResponse createHandle(HandleValue[] hv) throws HandleException
     {
         byte idHandle[] = Util.encodeString(Constants.NA_HANDLE_PREFIX + handleConfig.getPrefix());
         
         PublicKeyAuthenticationInfo pubKeyAuthInfo = new PublicKeyAuthenticationInfo(idHandle,
               Constants.SEC_KEY_IDX,
               handleConfig.getPrivateKey());

         CreateHandleRequest req = new CreateHandleRequest(Util.encodeString(this.getHandle()), hv, pubKeyAuthInfo);
         HandleResolver resolver = new HandleResolver();
         resolver.traceMessages = true;
         return resolver.processRequest(req);
     }
     
     
     /**
      * Indicate whether an agent is the handle admin for this handle 
      * 
      * @return boolean
      *           <code>true</code> if the provided Identifier is the 
      *           administrator of this handle else <code>false</code>
      * @param identifier
      *           An Identifier object representing an agent
      * @throws HandleException
      */
     public boolean isAdmin(Identifier identifier) throws HandleException
     {
         boolean isAdmin = false;

         // Get the record owner. May have to change this to types or reserved
         // indexes if have multiple admin agents
         int idx[] = {Constants.AGENT_IDX};
         HandleValue[] agentHandle = resolver.resolveHandle(getHandle(), null, idx, false);
         
         if (agentHandle.length > 0)
         {
             byte idHandle[] = Util.encodeString(Constants.NA_HANDLE_PREFIX + handleConfig.getPrefix());
                          
             PublicKeyAuthenticationInfo pubKeyAuthInfo = new PublicKeyAuthenticationInfo(idHandle,
                     Constants.SEC_KEY_IDX,
                     handleConfig.getPrivateKey());

             int idxDesc[] = {Constants.AGENT_DESC_IDX};
             ResolutionRequest rReq = new ResolutionRequest(agentHandle[0].getData(), null, idxDesc, pubKeyAuthInfo);

             rReq.ignoreRestrictedValues = false;
             AbstractResponse response = resolver.getResolver().processRequest(rReq);
             
             if (response instanceof ResolutionResponse)
             {
                 HandleValue[] ahv = ((ResolutionResponse)response).getHandleValues();
                 log.info("length=" + ahv.length);
    
                 if (ahv.length > 0)
                 {
                     if (ahv[0].getDataAsString().equals(identifier.getAdminKey()))
                     {
                         isAdmin = true;
                     }
                 }
             }
             else
             {
                 log.info("Unexpected response; type=" + response.getClass().getName() + ", msg=" + response.toString());
             }
         }
         return isAdmin;
     }

     
     /**
      * Indicate whether a handle value can be deleted. A handle value
      * must be one of the allowed types in order for it to be deleted 
      * 
      * @return boolean
      *           <code>true</code> if the value can be deleted
      *           else <code>false</code>
      * @param index
      *           the index of the value
      * @throws HandleException
      */
     public boolean deleteAllowed(int index) throws HandleException
     {
         String[] types = {Constants.STD_TYPE_URL_STRING, Constants.XT_TYPE_DESC_STRING};
         
         HandleValue[] hv = resolver.resolveHandle(getHandle(), types);
         
         boolean allowed = false;
         
         for (int i=0; i < hv.length; i++)
         {
             if (hv[i].getIndex() == index)
             {
                 allowed = true;
                 break;
             }
         }
         
         return allowed;
     }


     /**
      * Indicate whether a handle value can be modified. A handle value
      * must be one of the allowed types in order for it to be modified 
      * 
      * @return boolean
      *           <code>true</code> if the value can be modified
      *           else <code>false</code>
      * @param index
      *           the index of the value
      * @throws HandleException
      */
     public boolean modifyAllowed(int index) throws HandleException
     {
         // currently same rules apply for modify as for delete 
         return deleteAllowed(index);
     }
     
     
     /**
      * Obtain the index the provided value is located at 
      * 
      * @return int
      *           the index the provided value is located at
      * @param value
      *           the value whose index is to be returned
      * @throws HandleException
      */
     public int getValueIndex(String value) throws HandleException
     {
         int index = -1;
         
//         String[] types = {Constants.STD_TYPE_URL_STRING, Constants.XT_TYPE_DESC_STRING};
         
//         HandleValue[] hv = resolver.resolveHandle(getHandle(), types);
         HandleValue[] hv = resolveAllowedValues(resolver.resolveHandle(getHandle()));
         
         for (int i=0; i < hv.length; i++)
         {
             if (hv[i].getDataAsString().equals(value))
             {
                 index = hv[i].getIndex();
                 break;
             }
         }
         
         return index;
     }
     
     
     // due to bug/ambiguity in resolveHandle with types, need to do this
     private HandleValue[] resolveAllowedValues(HandleValue[] hvs)
     {         
         ArrayList<HandleValue> al = new ArrayList<HandleValue>();
         for (int i=0; i<hvs.length; i++)
         {
             if (hm.get(hvs[i].getTypeAsString()) != null)
             {
                 al.add(hvs[i]);
             }
         }
         
         return al.toArray(new HandleValue[al.size()]); 
     }
     
     
     /**
      * Determine whether an index is in use 
      * 
      * @return boolean
      *           <code>true</code> if the index is available otherwise
      *           <code>false</code>
      * @param index
      *           the index to check
      * @throws HandleException
      */
     public boolean isEmptyIndex(int index) throws HandleException
     {
         HandleValue[] hvs = resolver.resolveHandle(this.getHandle());

         for (int i=0; i < hvs.length; i++)
         {
             if (hvs[i].getIndex() == index)
             {
                 return false;
             }
         }

         return true;
     }

     
     /**
      * Obtain an array of sorted indexes for a handle
      * 
      * @return Array&lt;Integer&gt;
      *           An array of sorted integers
      * @throws DAOException
      */
     public Integer[] getSortedIndexes() throws DAOException
     {
         HandleDAO hdao = new HandleDAO();
         return hdao.getSortedIndexes(this);
     }
}