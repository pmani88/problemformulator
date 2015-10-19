<?php
/* Basic CakePHP helper to output plaintext content and prompt to save as file.
 * Based on CSVHelper:
 * http://bakery.cakephp.org/articles/view/csv-helper-php5
 */
class PlaintextHelper extends AppHelper
{
        var $filename;
       
        public function render($output = '', $filename = null)
        {
                if (!empty($filename) && is_string($filename))
                {
                        $this->setFilename($filename);
                }
                header ('Expires: Mon, 1 Apr 1974 05:00:00 GMT');
                header ('Last-Modified: ' . gmdate('D,d M YH:i:s') . ' GMT');
                header ('Pragma: no-cache');
                header('Content-disposition:attachment;filename='.$this->filename);
                header('Content-type: text/html');
               
                return $this->output($output);
        }
       
        /**
         * Sets the output filename. Automatically appends .txt if necessary.
         *
         * @param string $filename Filename to save as
         * @access public
         */
        public function setFilename($filename)
        {
                if (!empty($filename))
                {
                        if (strtolower(substr($filename, -4)) != '.txt')
                        {
                                $filename .= '.txt';
                        }
                        $this->filename = $filename;
                }
        }
}
?>