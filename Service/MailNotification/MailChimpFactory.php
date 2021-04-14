<?php

declare(strict_types=1);

namespace App\Factories\MailNotification;

use Newsletter;
use Illuminate\Http\Request;
use App\Factories\MailNotification\Interfaces\MailNotificationInterface;

/**
 * Class MailChimpFactory
 * @package App\Factories\MailNotification
 */
class MailChimpFactory implements MailNotificationInterface
{
    /*** @var Newsletter */
    private $mailChimpClient;

    /*** @var Request */
    private $request;

    private $listId;

    /**
     * MailChimpFactory constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->listId = env('MAILCHIMP_LIST_ID');
        $this->request = $request;
        $this->mailChimpClient = Newsletter::getApi();
    }

    /**
     * @return mixed
     */
    public function subscribe()
    {
        $result = $this->sendMail();
        $this->request->merge(['already_exist' => !isset($result['id'])]);
        return $result;
    }

    /**
     * @return mixed
     */
    private function sendMail()
    {
        $data = [
            'email_address' => $this->request->contactForm['email'],
            'status' => 'subscribed',
            'merge_fields' => $this->prepareProducts()
        ];
        $myHashId = $this->checkExistingEmail();
        if ($myHashId) {
            return $this->mailChimpClient->put("lists/{$this->listId}/members/{$myHashId}", $data);
        }
        return $this->mailChimpClient->post("lists/{$this->listId}/members/", $data);
    }

    /**
     * @return array
     */
    private function prepareProducts()
    {
        $iter = 1;
        $productsData = $this->getDefaultParameters();
        $filteredProducts = $this->request->filteredProducts;
        foreach ($filteredProducts as $filteredProduct) {
            foreach ($filteredProduct['products'] as $product) {
                $productsData['PRODUCT' . $iter] = $product['name'];
                $productsData['PLINK' . $iter] = $product['link'] ?? '';
                $productsData['PDESC' . $iter] = $product['description'];
                $productsData['PIMG' . $iter] = getProductImage($product['name']);
                $productsData['PUPC' . $iter] = $this->getUpc($product);
                $iter++;
            }
        }
        if (isset($this->request->french)) {
            $iter = 1;
            $filteredProducts = $this->request->french;
            foreach ($filteredProducts as $filteredProduct) {
                foreach ($filteredProduct['products'] as $product) {
                    $productsData['PLINK' . $iter] = $product['french_link'] ?? '';
                    $productsData['PRODUCT' . $iter] = $product['name'];
                    $productsData['PDESC' . $iter] = $product['description'];
                    $iter++;
                }
            }
        }
        return $productsData;
    }

    /**
     * @param $product
     * @return string
     */
    private function getUpc($product)
    {
        return env('APP_URL') . 'img/ups/' . $this->getLanguageCode() . '/'
            . strtolower(str_replace(' ', '_', $product['name'])) . '.png';
    }

    /**
     * @return mixed|string
     */
    private function getLanguageCode()
    {
        return $this->request->languageCode ?? 'nl';
    }

    /**
     * @return string
     */
    private function getLanguageKey()
    {

        $languageKey = 'link';
        if ($this->getLanguageCode() == 'FR') {
            $languageKey = 'french_link';
        }
        return $languageKey;
    }

    /**
     * @return array
     */
    private function getDefaultParameters()
    {
        return [
            'EMAIL' => $this->request->contactForm['email'],
            'FNAME' => $this->request->contactForm['name'],
            'LANG' => strtolower($this->getLanguageCode()),
            'PHONE' => $this->request->contactForm['phone'],
            'CNAME' => $this->request->contactForm['company'],
            'CODE' => $this->request->contactForm['post'],
        ];
    }

    /**
     * @return mixed|string
     */
    private function checkExistingEmail()
    {
        $myHashId = '';
        $countList = $this->getList();
        $listMembers = $this->getList("?count={$countList['total_items']}");
        foreach ($listMembers['members'] as $member) {
            if ($member['email_address'] === $this->request->contactForm['email']) {
                $myHashId = $member['id'];
                break;
            }
        }
        return $myHashId;
    }

    /**
     * @param string $params
     * @return mixed
     */
    private function getList($params = '')
    {
        return $this->mailChimpClient->get("lists/{$this->listId}/members{$params}");
    }
}
