<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Crawl extends Command
{

  private $rootUrl;      // Trustech domain
  private $exhibitorUrl; // Trustech exhibitor page
  private $rootContent;

  private $companyPageUrls; // Array of URL strings

  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'crawl';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Crawl the Trustech exhibitor list';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    // Ignore any DOM errors from the crawl
    libxml_use_internal_errors(true);

    $this->rootUrl      = env('TRUSTECH_URL');
    $this->exhibitorUrl = env('TRUSTECH_URL') . "/Catalogue2/Exhibitors-list";

    $this->info('Crawing the Trustech exhibitor list');
    $this->info( $this->exhibitorUrl );

    $this->rootContent = $this->getUrlContent( $this->exhibitorUrl );
    $this->getCompanyPageUrls();
  }

  private function getUrlContent($url)
  {
    global $output;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
  }

  private function getCompanyPageUrls()
  {
    $doc = new \DOMDocument();
    $doc->loadHTML( $this->rootContent );
    $XPath = new \DOMXPath($doc);

    $this->companyPageUrls = $this->parseToArray($XPath, '//*[@class="catal-ex-item-title"]//a/@href');
  }

  public function parseToArray($xpath, $xpathquery)
  {
    $elements = $xpath->query($xpathquery);
    if (!is_null($elements)) {
      $resultarray=array();
      foreach ($elements as $element) {
        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
          $resultarray[] = $node->nodeValue;
        }
      }
      return $resultarray;
    }
  }


}
