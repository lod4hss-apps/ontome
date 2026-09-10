<?php

/**
 * Created by PhpStorm.
 * User: Djamel
 * Date: 21/11/2017
 * Time: 11:22.
 */

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ClassRepository;
use App\Repository\ContainerRepository;
use App\Repository\NamespaceRepository;
use App\Repository\ProfileRepository;
use App\Repository\ProjectRepository;
use App\Repository\PropertyRepository;
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
     *
     * @return JsonResponse a Json formatted list representation of OntoClasses related to a Project
     */
    public function getClassesByProject(Project $project, ClassRepository $classRepository): JsonResponse
    {
        try {
            $classes = $classRepository->findClassesByProjectId($project);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, ['content-type:application/problem+json']);
        }

        if (empty($classes[0]['json'])) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
        }

        // return new JsonResponse(null,404, array('content-type'=>'application/problem+json'));
        return new JsonResponse($classes[0]['json'], Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/properties/project/{project}/json", name="properties_project_json", requirements={"project"="^[0-9]+$"}, methods={"GET"})
     *
     * @return JsonResponse a Json formatted list representation of Property related to a Project
     */
    public function getPropertiesByProject(Project $project, PropertyRepository $propertyRepository): JsonResponse
    {
        try {
            $properties = $propertyRepository->findPropertiesByProjectId($project);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, ['content-type:application/problem+json']);
        }

        if (empty($properties[0]['json'])) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
        }

        return new JsonResponse($properties[0]['json'], Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/profiles.json", name="api_profiles_json", methods={"GET"})
     *
     * @return JsonResponse a Json formatted list representation of profiles
     */
    public function getProfiles(Request $request, ProfileRepository $profileRepository): JsonResponse
    {
        try {
            $lang = $request->get('lang', 'en');
            $selectingProject = intval($request->get('selected-by-project', 0));
            $owningProject = intval($request->get('owned-by-project', 0));

            $profiles = $profileRepository->findProfilesApi($lang, $selectingProject, $owningProject);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'Error';
            $response = [
                'status' => $status,
                'message' => $message,
            ];

            return new JsonResponse($response, Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type:application/problem+json']);
        }

        if (empty($profiles[0]['json'])) {
            return new JsonResponse('[]', Response::HTTP_OK, [], true); // envoi d'un tableau JSON vide si pas de résultat
        }

        return new JsonResponse($profiles[0]['json'], Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/classes-profile.json", name="api_classes_profile_json", methods={"GET"})
     *
     * @return JsonResponse a Json formatted list representation of classes with profile
     */
    public function getClassesWithProfile(Request $request, ClassRepository $classRepository): JsonResponse
    {
        try {
            $lang = $request->get('lang', 'en');
            $availableInProfile = intval($request->get('available-in-profile', 0));
            $selectedByProject = intval($request->get('selected-by-project', 0));

            $classes = $classRepository->findClassesWithProfileApi($lang, $availableInProfile, $selectedByProject);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'Error';
            $response = [
                'status' => $status,
                'message' => $message,
            ];

            return new JsonResponse($response, Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type:application/problem+json']);
        }

        if (empty($classes[0]['json'])) {
            return new JsonResponse('[]', Response::HTTP_OK, [], true); // envoi d'un tableau JSON vide si pas de résultat
        }

        return new JsonResponse($classes[0]['json'], Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/properties-profile.json", name="api_properties_profile_json", methods={"GET"})
     *
     * @return JsonResponse a Json formatted list representation of properties with profile
     */
    public function getPropertiesWithProfile(Request $request, PropertyRepository $propertyRepository): JsonResponse
    {
        try {
            $lang = $request->get('lang', 'en');
            $availableInProfile = intval($request->get('available-in-profile', 0));
            $selectedByProject = intval($request->get('selected-by-project', 0));

            $properties = $propertyRepository->findPropertiesWithProfileApi($lang, $availableInProfile, $selectedByProject);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'Error';
            $response = [
                'status' => $status,
                'message' => $message,
            ];

            return new JsonResponse($response, Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type:application/problem+json']);
        }

        if (empty($properties[0]['json'])) {
            return new JsonResponse('[]', Response::HTTP_OK, [], true); // envoi d'un tableau JSON vide si pas de résultat
        }

        return new JsonResponse($properties[0]['json'], Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/shacl-profile.ttl", name="api_shacl_profile", methods={"GET"})
     */
    public function getShaclWithProfile(Request $request, ProfileRepository $profileRepository): Response
    {
        try {
            $lang = $request->get('lang', 'en');
            $profileId = intval($request->get('profile-id', 0));

            $output = $profileRepository->findShaclWithProfile($lang, $profileId);
        } catch (\Exception $e) {
            $message = '# Error: (PHP'.phpversion().')'.$e->getMessage(); // Commentaire en Turtle

            return new Response($message, Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type' => 'text/turtle']);
        }

        if (empty($output)) {
            return new Response('', Response::HTTP_OK, ['Content-Type' => 'text/turtle']); // Fichier Turtle vide
        }

        return new Response($output, Response::HTTP_OK, ['Content-Type' => 'text/turtle']);
    }

    /**
     * @Route("/api/namespaces-rdf-owl.rdf", name="api_classes_and_properties_by_namespace_xml", methods={"GET"})
     */
    public function getClassesAndPropertiesByNamespace(Request $request, NamespaceRepository $namespaceRepository): Response
    {
        try {
            $lang = $request->get('lang', 'en');
            $namespaceId = intval($request->get('namespace', 0));
            $xml = $namespaceRepository->findClassesAndPropertiesByNamespaceIdApi($lang, $namespaceId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: '.$e->getMessage().'"/>';
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
     */
    public function getOwlWisskiByNamespace(Request $request, NamespaceRepository $namespaceRepository): Response
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
            $owlFilePath = 'documents/files-owl/namespace-'.$namespaceId.'.owl';

            // If we don't want to persist, delete the file if it exists
            if (!$persistent && file_exists($owlFilePath)) {
                unlink($owlFilePath);
            }

            // If the file does not exist or if reloading is forced
            if (!file_exists($owlFilePath) || $reload) {
                // In this case, we generate the XML with the SQL query.
                $xml = $namespaceRepository->findClassesAndPropertiesByNamespaceIdApiWisski($lang, $namespaceId);

                // The file is saved if the namespace is not ongoing.
                // unless $persistent is false, we do not save
                if ($persistent) {
                    $isOngoing = $namespaceRepository->find($namespaceId)->getIsOngoing();
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
            $xml .= '<error code="500" message="Error: '.$e->getMessage().'"/>';
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
     *
     * @return Response XML formatted response of classes and properties related to this project
     */
    public function getClassesAndPropertiesByProject(Request $request, ProjectRepository $projectRepository): Response
    {
        try {
            $lang = $request->get('lang', 'en');
            $projectId = intval($request->get('project', 0));
            $xml = $projectRepository->findClassesAndPropertiesByProjectIdApi($lang, $projectId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: '.$e->getMessage().'"/>';
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
     *
     * @return Response XML formatted response of classes and properties related to this profile
     */
    public function getClassesAndPropertiesByProfile(Request $request, ProfileRepository $profileRepository): Response
    {
        try {
            $lang = $request->get('lang', 'en');
            $profileId = intval($request->get('profile', 0));
            $xml = $profileRepository->findClassesAndPropertiesByProfileIdApi($lang, $profileId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: '.$e->getMessage().'"/>';
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
     */
    public function getClassesAndPropertiesByNamespaceRdfs(Request $request, NamespaceRepository $namespaceRepository): Response
    {
        try {
            $lang = $request->get('lang', 'en');
            $namespaceId = intval($request->get('namespace', 0));
            $withInverseProperties = intval($request->get('withInverseProperties', 0));
            $withSpecificUri = intval($request->get('withSpecificUri', 0));
            $xml = $namespaceRepository->findClassesAndPropertiesByNamespaceIdApiRdfs($lang, $namespaceId, $withInverseProperties, $withSpecificUri);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: '.$e->getMessage().'"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');

            return $response;
        }
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml[0]['result']);
        $dom->formatOutput = true;
        $response = new Response($dom->saveXML());
        $response->headers->set('Content-Type', 'application/rdf+xml');

        return $response;
    }

    /**
     * @Route("/api/profile-rdfs.rdf", name="api_classes_and_properties_by_profile_xml_rdfs", methods={"GET"})
     *
     * @return Response XML formatted response of classes and properties related to this profile
     */
    public function getClassesAndPropertiesByProfileRdfs(Request $request, ProfileRepository $profileRepository): Response
    {
        try {
            $lang = $request->get('lang', 'en');
            $profileId = intval($request->get('profile', 0));
            $xml = $profileRepository->findClassesAndPropertiesByProfileIdApiRdfs($lang, $profileId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: '.$e->getMessage().'"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');

            return $response;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml[0]['result']);
        $dom->formatOutput = true;
        $response = new Response($dom->saveXML());
        $response->headers->set('Content-Type', 'application/rdf+xml');

        return $response;
    }

    /**
     * @Route("/api/owl-wisski-project.rdf", name="api_owl_wisski_by_project", methods={"GET"})
     */
    public function getOwlWisskiByProject(Request $request, ProjectRepository $projectRepository): Response
    {
        try {
            $lang = $request->get('lang', 'en');
            $namespaceId = intval($request->get('project', 0));
            $xml = $projectRepository->findNamespacesByProjectIdApi($lang, $namespaceId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: '.$e->getMessage().'"/>';
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
     *
     * @return Response a XML formatted response of namespaces related to this container, in OWL format (WissKI)
     */
    public function getOwlWisskiByContainer(Request $request, ContainerRepository $containerRepository): Response
    {
        try {
            // Langue par défaut: en, sinon celle passée en paramètre
            $lang = $request->get('lang', 'en');

            // Container ID passé en paramètre, sinon 0 (ce qui ne correspond à aucun container et donc renverra une erreur ou un résultat vide)
            $containerId = intval($request->get('container', 0));

            // Récupérer le container
            $xml = $containerRepository->findNamespacesByContainerIdApi($lang, $containerId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: '.$e->getMessage().'"/>';
            $response = new Response($xml);
            $response->headers->set('Content-Type', 'application/rdf+xml');

            return $response;
        }

        if (empty($xml) || !isset($xml[0]['result'])) {
            $xmlError = '<?xml version="1.0" encoding="UTF-8" ?><error code="404" message="Container data not found"/>';

            return new Response($xmlError, Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/rdf+xml']);
        }

        $response = new Response($xml[0]['result']);
        $response->headers->set('Content-Type', 'application/rdf+xml');

        return $response;
    }

    /**
     * @Route("/api/container{container}.rdf", name="api_container", requirements={"container"="^([0-9]+)|(containerId){1}$"}, methods={"GET"})
     *
     * @return Response a XML formatted response of namespaces and pathbuilders related to this container
     */
    public function getApiContainer(Request $request, ContainerRepository $containerRepository): Response
    {
        try {
            // Container ID passé en paramètre, sinon 0 (ce qui ne correspond à aucun container et donc renverra une erreur ou un résultat vide)
            $containerId = intval($request->get('container', 0));

            // Récupérer le container
            $xml = $containerRepository->findContainerApi($containerId);
        } catch (\Exception $e) {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<error code="500" message="Error: '.$e->getMessage().'"/>';
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
     *
     * @param string $label the class label to find
     *
     * @return JsonResponse a Json formatted list representation of OntoClasses related to a Project
     */
    public function getE55ChildrenClassesByLabel(string $label, ClassRepository $classRepository): JsonResponse
    {
        try {
            $classes = $classRepository->findE55ChildClassesFromLabel($label);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'Error';
            $response = [
                'status' => $status,
                'message' => $message,
            ];

            return new JsonResponse($response, Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type:application/problem+json']);
        }

        if (empty($classes[0]['json'])) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
        }

        return new JsonResponse($classes[0]['json'], Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/get-ontome-uri", name="api_get_ontome_uri", methods={"GET"})
     *
     * @param Request $request the request containing the officialUri parameter
     *
     * @return JsonResponse a Json formatted response containing the OntoME URI corresponding to the given official URI
     *                      This API endpoint allows clients to retrieve the OntoME URI corresponding to a given official URI of a class or property
     */
    public function getOntoMeUriFromOfficialUri(Request $request, ProjectRepository $projectRepository): JsonResponse
    {
        $officialUri = rawurldecode($request->query->get('officialUri'));

        if (!$officialUri) {
            return new JsonResponse(['error' => 'Missing officialUri parameter'], Response::HTTP_BAD_REQUEST, ['content-type:application/problem+json']);
        }

        $ontomeUri = $projectRepository->findOntoMeUriFromOfficialUri($officialUri);

        if (!$ontomeUri) {
            return new JsonResponse(['error' => 'OntoME URI not found'], Response::HTTP_NOT_FOUND, ['content-type:application/problem+json']);
        }

        return new JsonResponse(['ontome_uri' => $ontomeUri], Response::HTTP_OK, ['content-type:application/json']);
    }
}
