<?php
namespace App\Repository;

use App\Entity\Container;
use Doctrine\DBAL\DBALException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContainerRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Container::class);
    }

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
    /**
     * @param $container int the ID of the container
     * @return string XML (OWL format)
     * @throws DBALException
     */
    public function findContainerApi($container)
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        $sql = "SELECT result::text FROM api.get_xml_api_container(:container) as result;";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array(
            'container' => $container
        ));

        return $stmt->fetchAll();
    }
}