<?php

namespace Sabinks\FreshbooksClientPhp;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FreshbooksClientPhp{
    private $client;
    private $headers;
    private $url;
    private $token;
    private $businessId;
    protected $config;

    public function __construct($token = null, $businessId = null, array $config = []){
        $this->config = $config;
        $this->token = ($token ? $token : $config['access_token']);
        if (empty($this->token)) {
            throw new Exception("Please provide freshbooks's token", 400);
        }
        $this->businessId = ($businessId ? $businessId : $config['businessId']);
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }

    public function customers(){
        $response = Http::withHeaders($this->headers)
            ->get('https://api.freshbooks.com/accounting/account/' . $this->businessId .'/users/clients',[]);
        
        return $this->getResponse($response);         
    }

    public function createCustomer($input)
    {
        $res = Http::withHeaders($this->headers)
                    ->post('https://api.freshbooks.com/accounting/account/' . $this->businessId .'/users/clients',[
                        'client' => [
                            "fname" => $input['first_name'],
                            "lname" => $input['last_name'],
                            "email" => $input['email'],
                            "organization" => $input['full_name'],
                            "home_phone" =>  $input['phone'],
                            "userid" => $input['user_id'],
                            "p_street" => $input['address1'],
                            "p_street2" => $input['address2'],
                            "p_city" => $input['city'],
                            "p_country" => $input['country_code'],
                            "p_province" => $input['state'],
                            "p_code" => $input['zone_code'],
                            "currency_code" => $input['currency_code'],
                        ]
                    ]);

        return $res->json()['response']['result']['client'];            
    }

    public function createProduct($input)
    {
        $response = Http::withHeaders($this->headers)
            ->post('https://api.freshbooks.com/accounting/account/' . $this->businessId .'/items/items',[
            'item' => [
                'name' => $input['description'],
                'qty' => 1,
                'description' => $input['description'] ?? 'Untitled Job',
                "sku" => $input['id'],
                'unit_cost' => [
                    'amount' => $input['amount'],
                ]
            ]
        ]);

        return $response;       
    }

    public function checkProductExist($input)
    {
        $response = Http::withHeaders($this->headers)
            ->get('https://api.freshbooks.com/accounting/account/' 
                . $this->businessId .'/items/items?search%5Bsku%5D='. $input['id']);
        
        return $response;    
    }

    public function invoiceCreate($input =[])
    {
        $invoice = [     
            "email" =>  $input['customerEmail'],  //client email
            "customerid" =>  $input['customerId'],        //client id
            "create_date" =>  Carbon::now()->toDateString(), 
            "lines" =>  [
                [ 
                    'name' => $input['product']['product']['name'],
                    'qty' => 1,
                    'description' => $input['product']['product']['description'],
                    "sku" => $input['product']['product']['id'],
                    "vis_state" => $input['product']['product']['vis_state'],
                    'unit_cost' => [
                        'amount' => $input['product']['product']['unit_cost']['amount'],
                        'code' => $input['product']['product']['unit_cost']['code']
                    ]
                ],
            ]
        ];
        if($input['invoiceNumber']){
            $invoice['invoice_number'] =  $input['invoiceNumber'];
        }
   
        $response = Http::withHeaders($this->headers)
            ->post('https://api.freshbooks.com/accounting/account/' . $this->businessId .'/invoices/invoices',[
            "invoice" =>  $invoice
        ]);     

        return $response;

    }

    public function invoiceSend($input =[])
    {
        $user_name = Auth::user()->name;    //vo user name
        $response = Http::withHeaders($this->headers)
                ->put('https://api.freshbooks.com/accounting/account/' . $this->businessId .'/invoices/invoices/'. 
                $input['invoiceId'] , [
            "invoice" =>  [
                "email_recipients" =>  [
                    $input['to']
                ],
                "invoice_customized_email" =>  [
                    "subject" =>  $input['subject'] ? $input['subject'] : 'Invoice #' . $input['invoiceId'] . ' from ' . $user_name,
                    "body" => $input['message'] ? $input['message'] : 'Custom Message'
                ],
                    "action_email" =>  true
                ]
        ]);

        return $this->getResponse($response); 
    }

    public function deleteInvoice($input)
    {
        $response = Http::withHeaders($this->headers)
                ->put('https://api.freshbooks.com/accounting/account/' . $this->businessId .'/invoices/invoices/'. 
                    $input['invoice_id'] , [
                        "invoice" => [
                            "vis_state" => 1
                        ]
        ]);

        return $response->json();
    }

    public function getShareLink($invoice_id)
    {
        $response = Http::withHeaders($this->headers)
            ->get('https://api.freshbooks.com/accounting/account/' . $this->businessId .
            '/invoices/invoices/'. $invoice_id .'/share_link?share_method=share_link');

        return $this->getResponse($response);
    }

    public function getSharePDF($invoice_id)
    {   
        $response = Http::withHeaders(Arr::add($this->headers,'Accept', 'application/pdf'))
            ->get('https://api.freshbooks.com/accounting/account/' . $this->businessId .
            '/invoices/invoices/'. $invoice_id .'/pdf');

        return $this->getResponse($response);
    }

    public function getInvoicesList()
    {
        $response = Http::withHeaders($this->headers)
            ->get('https://api.freshbooks.com/accounting/account/' . $this->businessId .
            '/invoices/invoices');

        return $response;
    }

    public function itemDelete($item_id)
    {
        $response = Http::withHeaders($this->headers)
            ->get('https://api.freshbooks.com/accounting/account/' . $this->businessId .
            '/invoices/invoices');

        return $this->getResponse($response);
    }

    public function downloadInvoice($invoice_id)
    {
        $response = Http::withHeaders(Arr::add($this->headers,'Accept', 'application/pdf'))
            ->get('https://api.freshbooks.com/accounting/account/' . $this->businessId .
            '/invoices/invoices/'. $invoice_id .'/pdf');
        if($response->ok()){
            Storage::put('Invoice #' . $invoice_id. '.pdf', $response);

            return Storage::path('Invoice #' . $invoice_id. '.pdf');
        }else{

            return $response->json()['response']['errors'];
        }
    }
    
    public function getResponse($response){
        if($response->ok()){

            return $response->json()['response']['result'];
        }else{

            return $response->json()['response']['errors'];
        }
    }
}
