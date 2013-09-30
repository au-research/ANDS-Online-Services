## ANDS Registry

**THIS REPOSITORY IS NO LONGER CURRENT - SEE BELOW FOR ANDS REGISTRY SOFTWARE & OTHER INFORMATION**


*     [ANDS Registry Core](https://github.com/au-research/ANDS-Registry-Core)
*     [ANDS Registry Contrib/Addons](https://github.com/au-research/ANDS-Registry-Contrib)
*     [ANDS Harvester Service](https://github.com/au-research/ANDS-Harvester)
*     [Full ANDS Software Listing](https://github.com/au-research/)

## What are we changing?

We are moving towards a multiple repository model, with each substantial application component hosted in its own individual repo. This will result in the following structure:

`au-research/ANDS-Registry-Core` - the core PHP codebase which includes a metadata registry, front-end portal and access management system

`au-research/ANDS-Registry-Contrib` - non-core addons including CMS editor, widget libraries, identifier management front-end and other self-contained community-sourced contributions.

`au-research/ANDS-Harvester` - a Java-based Tomcat web application used to schedule and harvest metadata from remote providers (over HTTP and OAI-PMH).

`au-research/ANDS-PIDS-Service` - a Java-based Tomcat web application which provides an API layer implemented around the CNRI Handle service.

`au-research/ANDS-RIFCS-API` - a Java library which provides a wrapper around the DOM methods required to manipulate and produce RIFCS documents.


## What, why?
The existing codebase has become quite large and difficult to maintain, particularly for non-ANDS staff who want to investigate or re-use parts of the code. Part of our vision for the software is to provide both a lightweight registry implementation (which would be easily deployable and customisable) as well as enabling the community to contribute and collaborate on functionality. By separating the core and non-core software components, we can make sure that the core registry software is kept to base functionality and other community-focussed components can be more accessible to external developers.

 

## How will this affect me?

These changes will be rolled out between R10.2 and R10.3 (i.e. are due to take place during October 2013). Follow our Github organisation "au-research" to monitor the progress and be alerted of the changed to repository names.


#### For Developers

Only core functionality for the registry, portal and roles applications will be maintained in the Registry Core. The ANDS team will maintain these sections of the codebase, however, community contributors with a particular feature which they feel should be included as part of the stock system are welcome to make a pull request.

"Apps" contained in the `ANDS-Registry-Contrib` repository are mostly self-contained applications, but do depend on theANDS-Registry-Core (and are installed by extracting the repository into the applications/apps/ directory of the core installation). Two primary branches will be maintained: a community branch (to which pull-requests from non-ANDS staff can be made) and a master branch (which contains the feature set currently supported by the ANDS production registry).


#### For Deployers

To deploy the ANDS registry stack, you will need to now checkout a copy of the ANDS-Registry-Core (and, if appropriate, follow any installation/upgrade instructions for your particular version).

In order to install the non-core applications, checkout the ANDS-Registry-Contrib directory into your applications/apps/ directory inside where you installed the Core. Many apps are disabled by default (by a mod_enabled() check in their initializer), which means that they must be manually enabled by uncommenting them in your global_config.php.

 

## Future considerations
We recognise that this change will add some small overhead in first deploying the software, but should also vastly improve the accessibility to developers and first-time implementers. Whilst the initial separation of sourcecode is relatively simple, issues such as menu structuring, version dependencies and mechanisms to "plug in" to the core software from outside the core remain to be addressed, subject to community demand.

We are also investigating mechanisms that would support automatic installation and deployment as well as detailed installation and developer guides to help facilitate adoption and collaboration within the community.


## License Terms
Unless otherwise specified, all ANDS Online Services software is Copyright 2009 The Australian National University and licensed under the Apache License version 2.0 ([http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0)).

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
