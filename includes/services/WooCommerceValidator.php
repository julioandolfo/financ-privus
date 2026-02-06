<?php
namespace Includes\Services;

/**
 * Valida e sanitiza dados do WooCommerce antes de importar
 */
class WooCommerceValidator
{
    /**
     * Valida dados de produto
     */
    public function validarProduto($prodWoo)
    {
        $erros = [];
        
        // SKU obrigatório
        if (empty($prodWoo['sku'])) {
            $erros[] = "SKU obrigatório";
        }
        
        // Nome obrigatório e mínimo 3 caracteres
        if (empty($prodWoo['name'])) {
            $erros[] = "Nome obrigatório";
        } elseif (strlen($prodWoo['name']) < 3) {
            $erros[] = "Nome muito curto (mínimo 3 caracteres)";
        }
        
        // Preço não pode ser negativo
        if (isset($prodWoo['price']) && $prodWoo['price'] < 0) {
            $erros[] = "Preço não pode ser negativo";
        }
        
        // Estoque não pode ser negativo
        if (isset($prodWoo['stock_quantity']) && $prodWoo['stock_quantity'] < 0) {
            $erros[] = "Estoque não pode ser negativo";
        }
        
        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }
    
    /**
     * Sanitiza dados de produto
     */
    public function sanitizarProduto($prodWoo)
    {
        return [
            'nome' => $this->sanitizarTexto($prodWoo['name'] ?? ''),
            'descricao' => $this->limparHTML($prodWoo['description'] ?? ''),
            'descricao_curta' => $this->limparHTML($prodWoo['short_description'] ?? ''),
            'sku' => $this->sanitizarSKU($prodWoo['sku'] ?? ''),
            'preco_venda' => $this->sanitizarPreco($prodWoo['price'] ?? 0),
            'preco_custo' => $this->sanitizarPreco($prodWoo['regular_price'] ?? 0),
            'estoque' => (int) ($prodWoo['stock_quantity'] ?? 0),
            'codigo_barras' => $this->sanitizarTexto($prodWoo['ean'] ?? ''),
            'ativo' => ($prodWoo['status'] ?? 'publish') === 'publish' ? 1 : 0
        ];
    }
    
    /**
     * Valida dados de pedido
     */
    public function validarPedido($pedWoo)
    {
        $erros = [];
        
        // Número do pedido obrigatório
        if (empty($pedWoo['number'])) {
            $erros[] = "Número do pedido obrigatório";
        }
        
        // Total do pedido obrigatório
        if (!isset($pedWoo['total']) || $pedWoo['total'] < 0) {
            $erros[] = "Total do pedido inválido";
        }
        
        // Cliente precisa ter pelo menos nome
        if (empty($pedWoo['billing']['first_name']) && empty($pedWoo['billing']['company'])) {
            $erros[] = "Cliente sem nome ou razão social";
        }
        
        // Data de criação obrigatória
        if (empty($pedWoo['date_created'])) {
            $erros[] = "Data de criação obrigatória";
        }
        
        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }
    
    /**
     * Sanitiza dados de pedido
     */
    public function sanitizarPedido($pedWoo)
    {
        return [
            'numero_pedido' => $this->sanitizarTexto($pedWoo['number'] ?? ''),
            'valor_total' => $this->sanitizarPreco($pedWoo['total'] ?? 0),
            'data_pedido' => $this->sanitizarData($pedWoo['date_created'] ?? date('Y-m-d H:i:s')),
            'status' => $this->sanitizarTexto($pedWoo['status'] ?? 'pending'),
            'payment_method' => $this->sanitizarTexto($pedWoo['payment_method'] ?? ''),
            'payment_method_title' => $this->sanitizarTexto($pedWoo['payment_method_title'] ?? ''),
            'customer_note' => $this->sanitizarTexto($pedWoo['customer_note'] ?? ''),
            'frete' => $this->sanitizarPreco($pedWoo['shipping_total'] ?? 0),
            'desconto' => $this->sanitizarPreco($pedWoo['discount_total'] ?? 0)
        ];
    }
    
    /**
     * Sanitiza texto genérico
     */
    private function sanitizarTexto($texto)
    {
        return trim(strip_tags($texto));
    }
    
    /**
     * Limpa HTML mantendo tags seguras
     */
    private function limparHTML($html)
    {
        // Remove tags perigosas mas mantém formatação básica
        $permitidas = '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6>';
        $limpo = strip_tags($html, $permitidas);
        
        // Remove atributos perigosos
        $limpo = preg_replace('/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i', '<$1$2>', $limpo);
        
        return trim($limpo);
    }
    
    /**
     * Sanitiza SKU
     */
    private function sanitizarSKU($sku)
    {
        // Remove caracteres especiais, mantém apenas letras, números, hífen e underline
        $sku = preg_replace('/[^a-zA-Z0-9\-_]/', '', $sku);
        return strtoupper(trim($sku));
    }
    
    /**
     * Sanitiza preço/valor decimal
     */
    private function sanitizarPreco($preco)
    {
        $preco = str_replace(',', '.', $preco);
        $preco = (float) $preco;
        return round($preco, 2);
    }
    
    /**
     * Sanitiza data
     */
    private function sanitizarData($data)
    {
        try {
            $timestamp = strtotime($data);
            if ($timestamp === false) {
                return date('Y-m-d H:i:s');
            }
            return date('Y-m-d H:i:s', $timestamp);
        } catch (\Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }
    
    /**
     * Valida e sanitiza cliente
     */
    public function sanitizarCliente($billing)
    {
        return [
            'nome' => $this->sanitizarTexto(
                ($billing['company'] ?? '') ?: 
                ($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')
            ),
            'email' => filter_var($billing['email'] ?? '', FILTER_SANITIZE_EMAIL),
            'telefone' => $this->sanitizarTelefone($billing['phone'] ?? ''),
            'cpf_cnpj' => $this->sanitizarCpfCnpj($billing['cpf'] ?? $billing['cnpj'] ?? ''),
            'endereco' => $this->sanitizarTexto($billing['address_1'] ?? ''),
            'complemento' => $this->sanitizarTexto($billing['address_2'] ?? ''),
            'bairro' => $this->sanitizarTexto($billing['neighborhood'] ?? ''),
            'cidade' => $this->sanitizarTexto($billing['city'] ?? ''),
            'estado' => $this->sanitizarTexto($billing['state'] ?? ''),
            'cep' => $this->sanitizarCep($billing['postcode'] ?? '')
        ];
    }
    
    /**
     * Sanitiza telefone
     */
    private function sanitizarTelefone($telefone)
    {
        return preg_replace('/[^0-9]/', '', $telefone);
    }
    
    /**
     * Sanitiza CPF/CNPJ
     */
    private function sanitizarCpfCnpj($cpfCnpj)
    {
        return preg_replace('/[^0-9]/', '', $cpfCnpj);
    }
    
    /**
     * Sanitiza CEP
     */
    private function sanitizarCep($cep)
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }
}
