<?php
/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 23/06/2017
 * Time: 14:57
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProjectRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return QueryBuilder to create the query for the list of project whom user is an admin
     */
    public function findAvailableProjectByAdminId(User $user)
    {
        return $this->createQueryBuilder('project')
            ->join('project.userProjectAssociations','upa')
            ->where('upa.user = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('project.standardLabel','ASC');
    }

    /**
     * @param $lang string the language iso code
     * @param $project int the ID of the project
     * @return array
     * @throws DBALException
     */
    public function findClassesAndPropertiesByProjectIdApi($lang, $project)
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $sql = "SELECT result::text FROM api.get_owl_classes_and_properties_for_profiles(:lang, 0, :project) as result;";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'lang' => $lang,
            'project' => $project
        ));

        return $stmt->fetchAll();
    }

    /**
     * @param $lang string the language iso code
     * @param $project int the ID of the project
     * @return string XML (OWL format)
     * @throws DBALException
     */
    public function findNamespacesByProjectIdApi($lang, $project)
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $sql = "SELECT result::text FROM api.get_owl_wisski_from_project(:lang, :project) as result;";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'lang' => $lang,
            'project' => $project
        ));

        return $stmt->fetchAll();
    }

    public function findOntoMeUriFromOfficialUri($officialUri){
        // Find the OntoME URI corresponding to the given official URI of a class or property
        // Ex: https://sdhss.org/ontology/core/C32 -> namespaceUri = https://sdhss.org/ontology/core/ et identifierInUri = C32
        $namespaceUri = substr($officialUri, 0, strrpos($officialUri, '/') + 1); // Find the first part of the URI until the last '/' included
        $identifierInUri = substr($officialUri, strrpos($officialUri, '/') + 1); // Find the last part of the URI after the last '/'
        
        $conn = $this->getEntityManager()
            ->getConnection();

        // Request the database to find the namespace corresponding to the namespaceUri and get its id (if it exists and is not a top level namespace)
        $sql = "SELECT pk_namespace FROM che.namespace WHERE namespace_uri = :namespaceUri AND is_top_level_namespace = FALSE;";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'namespaceUri' => $namespaceUri
        ));
        $result = $stmt->fetchAll();

        if (empty($result)) {
            return null;
        }

        $idNamespaces = array_column($result, 'pk_namespace');

        // Search for the class having the identifierInUri
        $sql = "SELECT DISTINCT cl.pk_class
                FROM che.class cl
                LEFT JOIN che.class_version cv ON cl.pk_class = cv.fk_class
                WHERE cl.identifier_in_uri = :identifierInUri
                AND cv.fk_namespace_for_version IN (".implode(',', $idNamespaces).")";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'identifierInUri' => $identifierInUri
        ));

        $result = $stmt->fetchAll();

        // If there is something, return the (first) result, otherwise continue with properties
        if (!empty($result)) {
            return 'https://ontome.net/ontology/c' . array_column($result, 'pk_class')[0];
        }

        // If no class is found, search among properties
        $sql = "SELECT DISTINCT prop.pk_property
                FROM che.property prop
                LEFT JOIN che.property_version pv ON prop.pk_property = pv.fk_property
                WHERE prop.identifier_in_uri = :identifierInUri
                AND pv.fk_namespace_for_version IN (".implode(',', $idNamespaces).")";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'identifierInUri' => $identifierInUri
        ));

        $result = $stmt->fetchAll();

        if (!empty($result)) {
            return 'https://ontome.net/ontology/p' . array_column($result, 'pk_property')[0];
        }

        // If no class nor property is found, find the project url
         $sql = "SELECT DISTINCT ns.fk_project_for_top_level_namespace
                FROM che.namespace ns
                WHERE ns.namespace_uri = :namespaceUri
                AND ns.pk_namespace IN (".implode(',', $idNamespaces).")";;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'namespaceUri' => $namespaceUri
        ));

        $result = $stmt->fetchAll();

        if (!empty($result)) {
            return 'https://ontome.net/project/' . array_column($result, 'fk_project_for_top_level_namespace')[0];
        }

        return null;
    }
}