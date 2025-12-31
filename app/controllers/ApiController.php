<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

/**
 * Controller para endpoints de API
 */
class ApiController extends Controller
{
    /**
     * Busca dados de CNPJ via API ReceitaWS
     * Endpoint: GET /api/cnpj/{cnpj}
     */
    public function buscarCNPJ(Request $request, Response $response, $cnpj = null)
    {
        // Pega CNPJ da URL (parâmetro da rota)
        if ($cnpj === null) {
            $cnpj = $request->get('cnpj', '');
        }
        
        // Remove formatação
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Valida CNPJ
        if (strlen($cnpj) !== 14) {
            return $response->json([
                'success' => false,
                'error' => 'CNPJ deve conter 14 dígitos'
            ], 400);
        }
        
        try {
            // Faz requisição para API ReceitaWS
            // Endpoint correto da ReceitaWS (necessita do prefixo /cnpj/)
            $url = "https://www.receitaws.com.br/v1/cnpj/{$cnpj}";
            
            // Usa cURL para evitar problemas de CORS
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new \Exception('Erro na requisição: ' . $error);
            }
            
            if ($httpCode !== 200) {
                throw new \Exception('Erro ao consultar CNPJ. Código HTTP: ' . $httpCode);
            }
            
            $data = json_decode($result, true);
            
            if (!$data || isset($data['status']) && $data['status'] === 'ERROR') {
                throw new \Exception($data['message'] ?? 'CNPJ não encontrado ou inválido');
            }
            
            // Formata dados para retornar
            $dadosFormatados = [
                'success' => true,
                'data' => [
                    'razao_social' => $data['nome'] ?? '',
                    'nome_fantasia' => $data['fantasia'] ?? '',
                    'telefone' => $data['telefone'] ?? '',
                    'email' => $data['email'] ?? '',
                    'situacao' => $data['situacao'] ?? '',
                    'logradouro' => $data['logradouro'] ?? '',
                    'numero' => $data['numero'] ?? '',
                    'complemento' => $data['complemento'] ?? '',
                    'bairro' => $data['bairro'] ?? '',
                    'cidade' => $data['municipio'] ?? '',
                    'estado' => $data['uf'] ?? '',
                    'cep' => isset($data['cep']) ? preg_replace('/[^0-9]/', '', $data['cep']) : '',
                    'inscricao_estadual' => $data['inscricao_estadual'] ?? ''
                ]
            ];
            
            return $response->json($dadosFormatados);
            
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
