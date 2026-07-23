<?php
/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 21/11/2017
 * Time: 11:22
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{


    /**
     * @Route("/api/classes/project/{project}/json", name="classes_project_json", requirements={"project"="^[0-9]+$"}, methods={"GET"})
     * @param Project $project
     * @return JsonResponse a Json formatted list representation of OntoClasses related to a Project
     */
    public function getClassesByProject(Project $project)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $classes = $em->getRepository(OntoClass::class)
                ->findClassesByProjectId($project);

        } catch (NotFoundHttpException $e) {
            return new JsonResponse(null, 404, 'content-type:application/problem+json');
        }

        if (empty($classes[0]['json'])) {
            return new JsonResponse(null, 204, array());
        }

        //return new JsonResponse(null,404, array('content-type'=>'application/problem+json'));
        return new JsonResponse($classes[0]['json'], 200, array(), true);
    }

    /**
     * @Route("/api/properties/project/{project}/json", name="properties_project_json", requirements={"project"="^[0-9]+$"}, methods={"GET"})
     * @param Project $project
     * @return JsonResponse a Json formatted list representation of Property related to a Project
     */
    public function getPropertiesByProject(Project $project)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $properties = $em->getRepository(Property::class)
                ->findPropertiesByProjectId($project);

        } catch (NotFoundHttpException $e) {
            return new JsonResponse(null, 404, 'content-type:application/problem+json');
        }

        if (empty($properties[0]['json'])) {
            return new JsonResponse(null, 204, array());
        }

        return new JsonResponse($properties[0]['json'], 200, array(), true);
    }

    /**
     * @Route("/api/profiles.json", name="api_profiles_json", methods={"GET"})
     * @param Request $request
     * @return JsonResponse a Json formatted list representation of profiles
     */
    public function getProfiles(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $selectingProject = intval($request->get('selected-by-project', 0));
            $owningProject = intval($request->get('owned-by-project', 0));

            $em = $this->getDoctrine()->getManager();
            $profiles = $em->getRepository(Profile::class)
                ->findProfilesApi($lang, $selectingProject, $owningProject);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'Error';
            $response = array(
                'status' => $status,
                'message' => $message
            );
            return new JsonResponse($response, 500, array('content-type:application/problem+json'));
        }

        if (empty($profiles[0]['json'])) {
            return new JsonResponse('[]', 200, array(), true);//envoi d'un tableau JSON vide si pas de résultat
        }

        return new JsonResponse($profiles[0]['json'], 200, array(), true);
    }

    /**
     * @Route("/api/classes-profile.json", name="api_classes_profile_json", methods={"GET"})
     * @param Request $request
     * @return JsonResponse a Json formatted list representation of classes with profile
     */
    public function getClassesWithProfile(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $availableInProfile = intval($request->get('available-in-profile', 0));
            $selectedByProject = intval($request->get('selected-by-project', 0));

            $em = $this->getDoctrine()->getManager();
            $classes = $em->getRepository(OntoClass::class)
                ->findClassesWithProfileApi($lang, $availableInProfile, $selectedByProject);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'Error';
            $response = array(
                'status' => $status,
                'message' => $message
            );
            return new JsonResponse($response, 500, array('content-type:application/problem+json'));
        }

        if (empty($classes[0]['json'])) {
            return new JsonResponse('[]', 200, array(), true);//envoi d'un tableau JSON vide si pas de résultat
        }

        return new JsonResponse($classes[0]['json'], 200, array(), true);
    }

    /**
     * @Route("/api/properties-profile.json", name="api_properties_profile_json", methods={"GET"})
     * @param Request $request
     * @return JsonResponse a Json formatted list representation of properties with profile
     */
    public function getPropertiesWithProfile(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $availableInProfile = intval($request->get('available-in-profile', 0));
            $selectedByProject = intval($request->get('selected-by-project', 0));

            $em = $this->getDoctrine()->getManager();
            $properties = $em->getRepository(Property::class)
                ->findPropertiesWithProfileApi($lang, $availableInProfile, $selectedByProject);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'Error';
            $response = array(
                'status' => $status,
                'message' => $message
            );
            return new JsonResponse($response, 500, array('content-type:application/problem+json'));
        }

        if (empty($properties[0]['json'])) {
            return new JsonResponse('[]', 200, array(), true);//envoi d'un tableau JSON vide si pas de résultat
        }

        return new JsonResponse($properties[0]['json'], 200, array(), true);
    }

    /**
     * @Route("/api/shacl-profile.ttl", name="api_shacl_profile", methods={"GET"})
     */
    public function getShaclWithProfile(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $profileId = intval($request->get('profile-id', 0));

            $em = $this->getDoctrine()->getManager();
            $output = $em->getRepository(Profile::class)
                ->findShaclWithProfile($lang, $profileId);

        } catch (\Exception $e) {
            $message = "# Error: (PHP" . phpversion() . ")" . $e->getMessage(); // Commentaire en Turtle
            return new Response($message, 500, ['Content-Type' => 'text/turtle']);
        }

        if (empty($output)) {
            return new Response("", 200, ['Content-Type' => 'text/turtle']); // Fichier Turtle vide
        }

        return new Response($output, 200, ['Content-Type' => 'text/turtle']);
    }


    /**
     * @Route("/api/namespaces-rdf-owl.rdf", name="api_classes_and_properties_by_namespace_xml", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getClassesAndPropertiesByNamespace(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $namespaceId = intval($request->get('namespace', 0));
            $em = $this->getDoctrine()->getManager();
            $xml = $em->getRepository(OntoNamespace::class)
                ->findClassesAndPropertiesByNamespaceIdApi($lang, $namespaceId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: ' . $e->getMessage() . '"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');
            return $response;
        }

        $response = new Response($xml[0]['result']);
        $response->headers->set('Content-Type', 'application/rdf+xml');
        return $response;
    }

    /**
     * @Route("/api/owl-wisski.rdf", name="api_owl_wisski_by_namespace", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getOwlWisskiByNamespace(Request $request)
    {
        // Let's see what environment we're in. If dev, we do not persist files
        $persistent = true;
        $currentEnv = $this->getParameter('kernel.environment');
        if ($currentEnv === 'dev') {
            $persistent = false;
        }

        try {
            $lang = $request->get('lang', 'en');
            $namespaceId = intval($request->get('namespace', 0));

            // Check if we need to reload the file (so with a query SQL)
            $reload = boolval($request->get('reload', false));

            // Build the path to the expected file (web/documents/files-owl/namespace-<id>.owl)
            $owlFilePath = 'documents/files-owl/namespace-' . $namespaceId . '.owl';

            // If we don't want to persist, delete the file if it exists
            if (!$persistent && file_exists($owlFilePath)) {
                unlink($owlFilePath);
            }

            // If the file does not exist or if reloading is forced
            if (!file_exists($owlFilePath) || $reload) {
                // In this case, we generate the XML with the SQL query.
                $em = $this->getDoctrine()->getManager();
                $xml = $em->getRepository(OntoNamespace::class)
                    ->findClassesAndPropertiesByNamespaceIdApiWisski($lang, $namespaceId);

                // The file is saved if the namespace is not ongoing.
                // unless $persistent is false, we do not save
                if ($persistent) {
                    $isOngoing = $em->getRepository(OntoNamespace::class)->find($namespaceId)->getIsOngoing();
                    if (!$isOngoing) {
                        $xmlContent = simplexml_load_string($xml[0]['result']);
                        if ($xmlContent !== false) {
                            // Save the owl file (or overwrite completely in case of reload)
                            $xmlContent->asXML($owlFilePath);
                        }
                    }
                }
            } else {
                // Otherwise, the file exists and we return the content of the owl file
                $xmlContent = simplexml_load_file($owlFilePath);
                $xml = [];
                $xml[0]['result'] = $xmlContent !== false ? $xmlContent->asXML() : '';
            }
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: ' . $e->getMessage() . '"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');
            return $response;
        }

        $response = new Response($xml[0]['result']);
        $response->headers->set('Content-Type', 'application/rdf+xml');
        return $response;
    }

    /**
     * @Route("/api/project-rdf-owl.rdf", name="api_classes_and_properties_by_project_xml", methods={"GET"})
     * @param Request $request
     * @return Response XML formatted response of classes and properties related to this project
     */
    public function getClassesAndPropertiesByProject(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $projectId = intval($request->get('project', 0));
            $em = $this->getDoctrine()->getManager();
            $xml = $em->getRepository(Project::class)
                ->findClassesAndPropertiesByProjectIdApi($lang, $projectId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: ' . $e->getMessage() . '"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');
            return $response;
        }

        $response = new Response($xml[0]['result']);
        $response->headers->set('Content-Type', 'application/rdf+xml');
        return $response;
    }

    /**
     * @Route("/api/profile-rdf-owl.rdf", name="api_classes_and_properties_by_profile_xml", methods={"GET"})
     * @param Request $request
     * @return Response XML formatted response of classes and properties related to this profile
     */
    public function getClassesAndPropertiesByProfile(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $profileId = intval($request->get('profile', 0));
            $em = $this->getDoctrine()->getManager();
            $xml = $em->getRepository(Profile::class)
                ->findClassesAndPropertiesByProfileIdApi($lang, $profileId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: ' . $e->getMessage() . '"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');
            return $response;
        }

        $response = new Response($xml[0]['result']);
        $response->headers->set('Content-Type', 'application/rdf+xml');
        return $response;
    }

    /**
     * @Route("/api/namespaces-rdfs.rdf", name="api_classes_and_properties_by_namespace_xml_rdfs", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getClassesAndPropertiesByNamespaceRdfs(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $namespaceId = intval($request->get('namespace', 0));
            $withInverseProperties = intval($request->get('withInverseProperties', 0));
            $withSpecificUri = intval($request->get('withSpecificUri', 0));
            $em = $this->getDoctrine()->getManager();
            $xml = $em->getRepository(OntoNamespace::class)
                ->findClassesAndPropertiesByNamespaceIdApiRdfs($lang, $namespaceId, $withInverseProperties, $withSpecificUri);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: ' . $e->getMessage() . '"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');
            return $response;
        }
        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        $dom->loadXML($xml[0]['result']);
        $dom->formatOutput = TRUE;
        $response = new Response($dom->saveXML());
        $response->headers->set('Content-Type', 'application/rdf+xml');
        return $response;
    }
    
    /**
     * @Route("/api/profile-rdfs.rdf", name="api_classes_and_properties_by_profile_xml_rdfs", methods={"GET"})
     * @param Request $request
     * @return Response XML formatted response of classes and properties related to this profile
     */
    public function getClassesAndPropertiesByProfileRdfs(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $profileId = intval($request->get('profile', 0));
            $em = $this->getDoctrine()->getManager();
            $xml = $em->getRepository(Profile::class)
                ->findClassesAndPropertiesByProfileIdApiRdfs($lang, $profileId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: ' . $e->getMessage() . '"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');
            return $response;
        }

        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        $dom->loadXML($xml[0]['result']);
        $dom->formatOutput = TRUE;
        $response = new Response($dom->saveXML());
        $response->headers->set('Content-Type', 'application/rdf+xml');
        return $response;
    }

    /**
     * @Route("/api/owl-wisski-project.rdf", name="api_owl_wisski_by_project", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getOwlWisskiByProject(Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');
            $namespaceId = intval($request->get('project', 0));
            $em = $this->getDoctrine()->getManager();
            $xml = $em->getRepository(Project::class)
                ->findNamespacesByProjectIdApi($lang, $namespaceId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: ' . $e->getMessage() . '"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');
            return $response;
        }

        $response = new Response($xml[0]['result']);
        $response->headers->set('Content-Type', 'application/rdf+xml');
        return $response;
    }

    /**
     * @Route("/api/owl-container-wisski.rdf", name="api_owl_wisski_by_container", methods={"GET"})
     * @param Request $request
     * @return Response a XML formatted response of namespaces related to this container, in OWL format (WissKI)
     */
    public function getOwlWisskiByContainer(Request $request)
    {
        try {
            // Langue par défaut: en, sinon celle passée en paramètre
            $lang = $request->get('lang', 'en');

            // Container ID passé en paramètre, sinon 0 (ce qui ne correspond à aucun container et donc renverra une erreur ou un résultat vide)
            $containerId = intval($request->get('container', 0));

            // Récupérer le container
            $em = $this->getDoctrine()->getManager();
            $xml = $em->getRepository(Container::class)->findNamespacesByContainerIdApi($lang, $containerId);

        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: ' . $e->getMessage() . '"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');
            return $response;
        }

        if (empty($xml) || !isset($xml[0]['result'])) {
            $xmlError = '<?xml version="1.0" encoding="UTF-8" ?><error code="404" message="Container data not found"/>';
            return new Response($xmlError, 404, ['Content-Type' => 'application/rdf+xml']);
        }

        $response = new Response($xml[0]['result']);
        $response->headers->set('Content-Type', 'application/rdf+xml');
        return $response;
    }

    /**
     * @Route("/api/container{container}.rdf", name="api_container", requirements={"container"="^([0-9]+)|(containerId){1}$"}, methods={"GET"})
     * @param Request $request
     * @return Response a XML formatted response of namespaces and pathbuilders related to this container
     */
    public function getApiContainer(Request $request)
    {
        try {
            // Container ID passé en paramètre, sinon 0 (ce qui ne correspond à aucun container et donc renverra une erreur ou un résultat vide)
            $containerId = intval($request->get('container', 0));

            // Récupérer le container
            $em = $this->getDoctrine()->getManager();
            $xml = $em->getRepository(Container::class)->findContainerApi($containerId);

        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: ' . $e->getMessage() . '"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');
            return $response;
        }

        $response = new Response($xml[0]['result']);
        $response->headers->set('Content-Type', 'application/rdf+xml');
        return $response;
    }

    /**
     * @Route("/api/classes-type-descendants/label/{label}/json", name="classes_type_descendants_json", requirements={"label"="^[a-zA-Z0-9 -]+$"}, methods={"GET"})
     * @param String $label the class label to find
     * @return JsonResponse a Json formatted list representation of OntoClasses related to a Project
     */
    public function getE55ChildrenClassesByLabel($label)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $classes = $em->getRepository(OntoClass::class)
                ->findE55ChildClassesFromLabel($label);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'Error';
            $response = array(
                'status' => $status,
                'message' => $message
            );
            return new JsonResponse($response, 500, array('content-type:application/problem+json'));
        }

        if (empty($classes[0]['json'])) {
            return new JsonResponse(null, 204, array());
        }

        return new JsonResponse($classes[0]['json'], 200, array(), true);
    }

    /**
     * @Route("/api/get-ontome-uri", name="api_get_ontome_uri", methods={"GET"})
     * @param Request $request the request containing the officialUri parameter
     * @return JsonResponse a Json formatted response containing the OntoME URI corresponding to the given official URI
     * This API endpoint allows clients to retrieve the OntoME URI corresponding to a given official URI of a class or property
     */
    public function getOntoMeUriFromOfficialUri(Request $request)
    {
        $officialUri = rawurldecode($request->query->get('officialUri'));

        if (!$officialUri) {
            return new JsonResponse(['error' => 'Missing officialUri parameter'], 400, array('content-type:application/problem+json'));
        }

        $em = $this->getDoctrine()->getManager();
        $ontomeUri = $em->getRepository(Project::class)
            ->findOntoMeUriFromOfficialUri($officialUri);

        if (!$ontomeUri) {
            return new JsonResponse(['error' => 'OntoME URI not found'], 404, array('content-type:application/problem+json'));
        }

        return new JsonResponse(['ontome_uri' => $ontomeUri], 200, array('content-type:application/json'));
    }
}