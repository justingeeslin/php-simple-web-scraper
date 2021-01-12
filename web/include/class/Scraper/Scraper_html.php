<?php
namespace PHPSimpleWebScraper\Scraper;
use PHPSimpleWebScraper\Browser\Browser;

/**
 * Displays a response as HTML.
 */
class Scraper_html extends Scraper_Base {

    protected $_sType = 'html';

    public function do() {

        // Check cache
        $_sFileBaseName  = $this->_getRequestCacheName() . ".{$this->_sType}";
        $_sFilePath      = $this->_sCacheDirPathToday . '/' . $_sFileBaseName;

        // Use cache
        if ( file_exists( $_sFilePath ) ) {
            $_iModifiedTime  = ( integer ) filemtime( $_sFilePath );
            if ( $_iModifiedTime + ( ( integer ) $this->_aBaseArguments[ 'cache_lifespan' ] ) > time() ) {
                readfile( $_sFilePath );
                return;
            }
        }

        $_sContent = $this->_getContent();
        echo $_sContent;
        flush();
        file_put_contents( $_sFilePath, $_sContent );   // caching

    }
        protected function _getContent() {
            $_oBrowser  = new Browser(
                $this->_aBaseArguments[ 'binary_path' ],
                $this->_aBaseArguments[ 'user_agent' ],
                $this->_aBaseArguments[ 'headers' ],
                $this->_aClientArguments
            );
            $_oBrowser->setRequestArguments( $this->_aRequestArguments );
            $_oResponse = $_oBrowser->get( $this->_aBaseArguments[ 'url' ] );
            $_sContent  = $_oResponse->getContent();
            if ( $_oResponse->getStatus()&& $_sContent ) {

                if (isset($_REQUEST['bannerimage'])) {
                    // echo "image only mode!";
                    // Modify content here..
                    error_reporting(E_ERROR | E_PARSE);
                    $doc = new \DOMDocument();
                    $doc->loadHTML($_sContent);

                    /* Create a new XPath object */
                    $xpath = new \DomXPath($doc);
                    
                    $nodes = $xpath->query("/html/body/div/div/div[3]/div/div[1]/div[1]/div");
                     
                    $output = "";
                    foreach ($nodes as $i => $node) {  
                        // $node->removeAttribute('class');                  
                        // $output = $doc->saveHTML($node);
                        $output .= $node->getAttribute('style');
                        $output = str_replace('background-image:url(', '', $output);
                        $output = str_replace(')', '', $output);
                        // Replace the image html
                        // $output = str_replace('<div height="[object Object]" style="background-image:url(', '', $output);
                        // $output = str_replace(')\'" class="sc-fzoxnE iOpsHV"></div></div></div>', '', $output);
                    }
                    // echo $output;
                    return $output;
                }

                
                return $_sContent;
            }
            // Error
            return "<h2>Failed to Get Response</h2>"
                . "<pre>"
                . htmlspecialchars( print_r( $_oResponse, true ) )
                . "</pre>";

        }

}