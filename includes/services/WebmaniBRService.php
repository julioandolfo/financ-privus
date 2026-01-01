<?php
namespace Includes\Services;

class WebmaniBRService
{
    private $config;
    private $baseUrl;
    
    public function __construct($config)
    {
        $this->config = $config;
        $this->baseUrl = $config['ambiente'] == 'producao' 
            ? 'https://webmaniabr.com/api/1/' 
            : 'https://webmaniabr.com/api/1/';  // WebmaniaBR usa mesma URL
    }
    
    /**
     * Testa conexão com WebmaniaBR
     */
    public function testarConexao()
    {
        try {
            $response = $this->makeRequest('GET', 'nfe/saldo/');
            
            return [
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso!',
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Emitir NF-e
     */
    public function emitirNFe($dadosNota)
    {
        return $this->makeRequest('POST', 'nfe/emissao/', $dadosNota);
    }
    
    /**
     * Consultar status da NF-e
     */
    public function consultarNFe($chaveNFe)
    {
        return $this->makeRequest('GET', "nfe/consulta/{$chaveNFe}/");
    }
    
    /**
     * Cancelar NF-e
     */
    public function cancelarNFe($chaveNFe, $motivo)
    {
        return $this->makeRequest('PUT', "nfe/cancelar/", [
            'chave' => $chaveNFe,
            'motivo' => $motivo
        ]);
    }
    
    /**
     * Download do XML da NF-e
     */
    public function downloadXML($chaveNFe)
    {
        return $this->makeRequest('GET', "nfe/xml/{$chaveNFe}/");
    }
    
    /**
     * Download do DANFE (PDF)
     */
    public function downloadDANFE($chaveNFe)
    {
        return $this->makeRequest('GET', "nfe/danfe/{$chaveNFe}/");
    }
    
    /**
     * Faz requisição para API WebmaniaBR
     */
    private function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'X-Consumer-Key: ' . $this->config['consumer_key'],
            'X-Consumer-Secret: ' . $this->config['consumer_secret'],
            'X-Access-Token: ' . $this->config['access_token'],
            'X-Access-Token-Secret: ' . $this->config['access_token_secret']
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('Erro cURL: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new \Exception($result['error'] ?? 'Erro na requisição à WebmaniaBR');
        }
        
        return $result;
    }
}
