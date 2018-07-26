<?php

class BitcoinTrade
{
  private const API_URL = 'https://api.bitcointrade.com.br/v1';

  protected $apiKey = null;

  public function __construct()
  {
    $arguments = func_get_args();
    $this->apiKey = $arguments[0];
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#8e6f6b73-b2f8-c03a-9d60-a0159f2c6ce0
  public function ticker($currency = 'BTC')
  {
    $apiPath = "/public/{$currency}/ticker";

    return $this->initCurl($apiPath);
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#dc3695f5-6129-e35c-153d-c629aee8fd48
  public function orders($currency = 'BTC')
  {
    $apiPath = "/public/{$currency}/orders";

    return $this->initCurl($apiPath);
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#9fe41816-3d20-e53e-9273-643c95279dc4
  public function trades(
    $currency = 'BTC',
    $hours = 1,
    $page_size = 100,
    $current_page = 1
  ) {
    $timeZone = new DateTimeZone('Brazil/East');

    $start_time = new DateTime('now');
    $start_time->format(DateTime::ATOM);
    $start_time->setTimezone($timeZone);
    $start_time->modify('-'.$hours.' hour');
    $start_time = date_format($start_time, DateTime::ATOM);

    $end_time = new DateTime('now');
    $end_time->format(DateTime::ATOM);
    $end_time->setTimezone($timeZone);
    $end_time = date_format($end_time, DateTime::ATOM);

    $apiPath = "/public/{$currency}/trades?start_time={$start_time}&end_time={$end_time}&page_size={$page_size}&current_page={$current_page}";

    return $this->initCurl($apiPath);
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#7aa82620-f7a2-7688-3081-bbb95afc3be3
  public function orderbook($currency = 'BTC')
  {
    $apiPath = "/market?currency={$currency}";
    $apiKeyRequired = true;

    return $this->initCurl($apiPath, $apiKeyRequired);
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#9a20d5e9-056b-7427-5f22-35f571f60411
  public function summary($currency = 'BTC')
  {
    $apiPath = "/market/summary?currency={$currency}";
    $apiKeyRequired = true;

    return $this->initCurl($apiPath, $apiKeyRequired);
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#989dcc17-e4fa-1262-fa35-589d47dd6b43
  public function userOrders(
    $currency = "BTC",
    $status = "executed_completely",
    $hours = 24,
    $type = "buy",
    $page_size = 100,
    $current_page = 1
  ) {
    $timeZone = new DateTimeZone('Brazil/East');

    $start_time = new DateTime('now');
    $start_time->format(DateTime::ATOM);
    $start_time->setTimezone($timeZone);
    $start_time->modify('-'.$hours.' hour');
    $start_time = date_format($start_time, DateTime::ATOM);

    $end_time = new DateTime('now');
    $end_time->format(DateTime::ATOM);
    $end_time->setTimezone($timeZone);
    $end_time = date_format($end_time, DateTime::ATOM);

    $apiPath = "/market/user_orders/list?status={$status}&start_date={$start_time}&end_date={$end_time}&currency={$currency}&type={$type}&page_size={$page_size}&current_page={$current_page}";
    $apiKeyRequired = true;

    return $this->initCurl($apiPath, $apiKeyRequired);
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#8d1745de-d21e-1478-9dfc-dd6f2a381cd1
  public function cancelOrder($id = '')
  {
    $fields = compact('id');

    $apiPath = "/market/user_orders/";

    $apiKeyRequired = true;

    return $this->initCurl($apiPath, $apiKeyRequired, $fields, 'DELETE');
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#c3fbdb41-fdd6-108c-753d-5efcfeff7a7e
  public function estimatedPrice($currency = "BTC", $amount = 0, $type ="buy")
  {
    $apiPath = "/market/estimated_price?amount={$amount}&currency={$currency}&type={$type}";
    $apiKeyRequired = true;

    return $this->initCurl($apiPath, $apiKeyRequired);
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#5ef0088b-40ef-4668-2ac4-59e0b94e91f7
  public function balance()
  {
    $apiPath = '/wallets/balance';
    $apiKeyRequired = true;

    return $this->initCurl($apiPath, $apiKeyRequired);
  }

  // API Documentation: https://apidocs.bitcointrade.com.br/#caf0a4c9-8485-4b14-d162-2a38cc8440a9
  public function createOrder(
    $currency = "BTC",
    $amount = 0,
    $type ="buy",
    $subtype="limited",
    $unit_price = 0
  ) {
    $fields = compact('currency', 'amount', 'type', 'subtype', 'unit_price');

    $apiPath = '/market/create_order';
    $apiKeyRequired = true;

    return $this->initCurl($apiPath, $apiKeyRequired, $fields, 'POST');
  }

  private function initCurl($path = '', $apiKeyRequired = false, $fields = [], $method = 'GET')
  {
    $curl = curl_init();

    $fields = json_encode($fields);

    $header = [
      'Content-Type: application/json'
    ];

    if($apiKeyRequired){
      array_unshift($header, "Authorization: ApiToken {$this->apiKey}");
    }

    $options = [
      CURLOPT_URL => self::API_URL . $path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_POSTFIELDS => $fields,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => $header
    ];

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);

    $err = curl_error($curl);

    curl_close($curl);

    return $err
      ? "cURL Error #: {$err}"
      : json_decode($response);
  }
}
