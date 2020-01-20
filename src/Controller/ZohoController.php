<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ZohoController extends AbstractController {

    /**
     * @Route("/zoho", name="zoho")
     */
    public function index(Request $request) {
        $file = $request->files->get("content");
        $requestDetails = $request->request->get("id");
        $content = file_get_contents($file->getPathName());
        $requestParams = json_decode($requestDetails, true);
        $requestParams["offerDocumentTemplateId"] = $requestParams["id"];
        unset($requestParams["id"]);
        $requestParams["fileContent"] = $content;
        $data = json_encode($requestParams);
        $this->callOfferService($data);
        die;
    }

    private function callOfferService($requestParams) {
        $data = $this->prepData($requestParams);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_URL, "http://192.168.232.31:8071/offer-document-template/document");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'appId: 124',
            'systemId: 222',
            'Accept: application/json',
            'Content-Type: application/json'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_COOKIE, '');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    public function prepData($data) {
        if (is_array($data)) {
            $multipart = false;

            foreach ($data as $item) {
                if (is_string($item) && strncmp($item, "@", 1) == 0 && is_file(substr($item, 1))) {
                    $multipart = true;
                    break;
                }
            }

            return ($multipart) ? $data : http_build_query($data, '', '&');
        } else {
            return $data;
        }
    }

}
