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

    /**
     * @param $lang string the language iso code
     * @param $project int the ID of the project
     * @return string XML (OWL format)
     * @throws DBALException
     */
    public function findApiNamespacesDLByProjectId($lang, $project)
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $resultNamespacesIds = $this->getEntityManager()
            ->getRepository('AppBundle:OntoNamespace')
            ->createQueryBuilder('n')
            ->join('n.projectAssociations', 'pa')
            ->where('pa.project = :projectId')
            ->andWhere('pa.systemType = 38')
            ->setParameter('projectId', $project)
            ->select('n.id')
            ->getQuery()
            ->getResult();


        $namespacesIds = array_map(function($item) {
            return $item['id'];
        }, $resultNamespacesIds);

        // Si aucun namespace n'est trouvé, retourner un résultat vide
        if (empty($namespacesIds)) {
            return array();
        }

        // Convertir le tableau PHP en format PostgreSQL array: {1,2,3}
        $namespacesIdsArray = '{' . implode(',', $namespacesIds) . '}';

        $sql = "SELECT result::text FROM api.get_xml_owl_classes_and_properties_for_namespaces(:lang, :namespacesIds::INTEGER[]) as result;";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'lang' => $lang,
            'namespacesIds' => $namespacesIdsArray
        ));

        return $stmt->fetchAll();
    }
}