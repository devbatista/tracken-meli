<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\Credential;
use App\Models\Transportadora;

class ApiController extends Controller
{
    public $credentials;

    public function __construct()
    {
        $this->credentials = Credential::first();
    }

    public function index(Request $request)
    {
        $data = $request->only('code');

        Credential::where(['id' => 1])->update(['code' => $data['code']]);
        $this->credentials = Credential::select('access_token', 'app_id', 'client_secret', 'redirect_uri', 'code')->first();

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/x-www-form-wrlencoded',
            'Accept' => 'application/json',
        ])->post('https://api.mercadolibre.com/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->credentials->app_id,
            'client_secret' => $this->credentials->client_secret,
            'code' => $this->credentials->code,
            'redirect_uri' => $this->credentials->redirect_uri,
        ]);

        $data = json_decode($response->body());

        if (isset($data->status) && $data->status == 400) {
            return 'Gere o código novamente';
        }

        Credential::where(['id' => 1])->update(['access_token' => 'Bearer ' . $data->access_token]);

        return $data;
    }

    public function me()
    {
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => $this->credentials->access_token
        ])->get('https://api.mercadolibre.com/users/me');

        return json_decode($response->body());
    }

    public function orders()
    {
        $data = $this->me();

        if (isset($data->status) && $data->status == '401') {
            return 'Gere o código novamente';
        }

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => $this->credentials->access_token
        ])->get('https://api.mercadolibre.com/orders/search', [
            'seller' => $data->id
        ]);

        return json_decode($response->body());
    }

    public function billingData()
    {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->credentials->access_token,
            ])
            ->get('https://api.mercadolibre.com/users/me');

        return $response->body();
    }

    public function getOrders($order)
    {
        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->credentials->access_token,
        ])->get('https://api.mercadolibre.com/orders/' . $order);

        $data = json_decode($response->body());

        if (isset($data->error)) {
            return $data;
        }

        $dtCriacao = str_replace('T', ' ', $data->date_created);
        $dtCriacao = explode('.', $dtCriacao);

        $vendedor = $this->getPersonData($data->seller->id);
        $enderecoVendedor = $this->getAddress($vendedor->address->zip_code);
        $enderecoVendedor = json_decode($enderecoVendedor);

        $comprador = $this->getPersonData($data->buyer->id);

        if (isset($comprador->address->address)) {
            $enderecoComprador = $this->getAddress($comprador->address->zip_code);
        } else {
            $enderecoComprador = [
                'endereco' => null,
                'bairro' => null,
                'localidade' => null,
                'uf' => null,
                'cep' => null,
            ];
        }

        $enderecoComprador = (object)$enderecoComprador;

        $cpfCnpjComprador = (isset($comprador->identification->number)) ? $comprador->identification->number : null;

        $retorno = [
            'cnpjCpfTransportadora' => '',
            'codigointerno' => '',
            'idEnvio' => $data->shipping->id,
            'idVenda' => '',
            'dtCriacao' => date('Y-m-d H:i:s', strtotime($dtCriacao[0])),
            'vlPago' => 'R$ ' . number_format($data->total_amount, 2, ',', ''),
            'peso' => '',
            'vlFrete' => '',
            'comentarios' => $data->comment,
            'tipoEndereco' => '',
            'cnpjCpfVendedor' => $vendedor->identification->number,
            'cliente' => $vendedor->first_name . ' ' . $vendedor->last_name,
            'razaoSocial' => '',
            'localretirada' => $vendedor->address->address,
            'numeroRetirada' => '',
            'complementoRetirada' => '',
            'bairroRetirada' => $enderecoVendedor->bairro,
            'cidadeRetirada' => $enderecoVendedor->localidade,
            'estadoRetirada' => $enderecoVendedor->uf,
            'cepVendedor' => $enderecoVendedor->cep,
            'cnpfCpfComprador' => $cpfCnpjComprador,
            'nomeComprador' => $data->buyer->first_name . ' ' . $data->buyer->last_name,
            'enderecoEntrega' => $enderecoComprador->endereco,
            'bairroEntrega' => $enderecoComprador->bairro,
            'cidadeEntrega' => $enderecoComprador->localidade,
            'estadoEntrega' => $enderecoComprador->uf,
            'cepEntrega' => $enderecoComprador->cep,
            'telefoneComprador' => (isset($data->buyer->phone->number)) ? $data->buyer->phone->number : null,
            'codServico' => '',
            'codCliente' => '',
            'regiaoOrigem' => '',
            'regiaoDestino' => '',
            'ieVendedor' => '',
            'chave_nf' => '',
            'ieComprador' => '',
        ];

        // $retorno = json_encode($retorno);

        return $retorno;
    }

    private function getPersonData($id)
    {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => $this->credentials->access_token,
            ])
            ->get('https://api.mercadolibre.com/users/' . $id);

        return json_decode($response);
    }

    private function getAddress($cep)
    {
        $response = Http::withoutVerifying()
            ->get('https://viacep.com.br/ws/' . $cep . '/json');

        return $response;
    }

    public function createTestUser()
    {
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => $this->access_token,
            'Content-Type' => 'application/json'
        ])->post('https://api.mercadolibre.com/users/test_user', [
            "site_id" => "MLB"
        ]);

        return $response->body();
    }

    public function insertTransportador(Request $request, Transportadora $transportadora)
    {
        $retorno = ['error' => '', 'list' => []];
        $data = $request->only('nome', 'cnpj');

        $validator = Validator::make($data, [
            'nome' => 'string|required',
            'cnpj' => 'string|required|cnpj|max:18|unique:transportadoras,cnpj'
        ]);

        if ($validator->fails()) {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        $transportadora->nome = $data['nome'];
        $transportadora->cnpj = $data['cnpj'];
        $transportadora->save();

        $retorno['list'] = $data;
        return $retorno;
    }

    public function getTransportadoras()
    {
        return Transportadora::get();
    }
}
