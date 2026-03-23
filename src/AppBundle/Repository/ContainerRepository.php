<?php
namespace AppBundle\Repository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class ContainerRepository extends EntityRepository
{
    /**
     * @param $lang string the language iso code
     * @param $container int the ID of the container
     * @return string XML (OWL format)
     * @throws DBALException
     */
    public function findNamespacesByContainerIdApi($lang, $container)
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $sql = "SELECT result::text FROM api.get_owl_wisski_from_container(:lang, :container) as result;";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'lang' => $lang,
            'container' => $container
        ));

        return $stmt->fetchAll();
    }
}