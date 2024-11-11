<?php

namespace VaxLtd\ProductFeed\Model\Format;

use Magento\Framework\Exception\LocalizedException;
use VaxLtd\ProductFeed\Logger\Logger;

class Xml extends AbstractFormat
{
    protected $escapeSpecialChars = false;
    protected $disableEscapingFields = [];

    /**
     * @var \XMLWriter
     */
    protected $writer;

    protected $logger;

    public function __construct(Logger $logger)
    {
        if (!class_exists('\XMLWriter')) {
            throw new LocalizedException(__('The XMLWriter class could not be found. This means your PHP installation is missing XMLWriter features. You cannot export XML/XSL types without XMLWriter. Please get in touch with your hoster or server administrator to add XMLWriter features.'));
        }
        $this->writer = new \XMLWriter();
        $this->writer->openMemory();
        $this->writer->setIndent(2);
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->startElement('objects');
        $this->logger = $logger;

    }


    public function formatFile($rows)
    {
        try {
            $useInternalXmlErrors = libxml_use_internal_errors(true);
            libxml_clear_errors();
            $this->setEscapeSpecialChars(true);
            $this->setDisableEscapingFields([]);
            $this->fromArray($rows);
            $output = $this->getDocument();
            if (libxml_get_last_error() !== FALSE) {
                $this->throwXmlException(__("Something is wrong with the internally processed XML markup"));
            }
            $fp = fopen('php://temp', 'r+b');
            fputs($fp, $output);
            rewind($fp);
            libxml_use_internal_errors($useInternalXmlErrors);

            return $fp;
        } catch (\Exception $e) {
            $this->logger->error("Could not format prodfeed as xml: " . $e->getMessage());
            return false;
        }
    }

    public function setEscapeSpecialChars($escapeSpecialChars)
    {
        $this->escapeSpecialChars = $escapeSpecialChars;
    }

    public function setDisableEscapingFields($disableEscapingFields)
    {
        $this->disableEscapingFields = $disableEscapingFields;
    }

    public function setElement($elementName, $elementText)
    {
        $elementName = trim($elementName);
        if (isset($elementName[0]) && is_numeric($elementName[0])) {
            $elementName = '_' . $elementName;
        }
        $this->writer->startElement($elementName);
        $this->writer->text((string) $elementText);
        $this->writer->endElement();
    }

    public function fromArray($array, $parentKey = '')
    {
        if (is_array($array)) {
            foreach ($array as $key => $element) {
                if (is_array($element)) {
                    $key = $this->handleSpecialParentKeys($key, $parentKey);
                    $this->writer->startElement($key);
                    $this->fromArray($element, $key);
                    $this->writer->endElement();
                } elseif (is_string($key)) {
                    $this->setElement($key, $this->stripInvalidXml($key, $element));
                }
            }
        }
    }

    public function getDocument()
    {
        $this->writer->endElement();
        $this->writer->endDocument();
        return $this->writer->outputMemory();
    }

    public function handleSpecialParentKeys($key, $parentKey)
    {
        if (is_numeric($key) && $parentKey == '') {
            $key = 'object';
        }
    
        $iteratingKeys = [
            'items',
            'custom_options',
            'product_attributes',
            'product_options'
        ];
    
        if (is_numeric($key) && $parentKey !== '') {
            if (in_array($parentKey, $iteratingKeys) || isset($iteratingKeys[$parentKey])) {
                if (isset($iteratingKeys[$parentKey])) {
                    $key = $iteratingKeys[$parentKey];
                } else {
                    $key = substr($parentKey, 0, -1);
                }
            }
            // Ensure a valid string key - thanks to Thomas Hägi
            if (is_numeric($key)) {
                // Create pseudo-singular key from parent key if possible
                $len = strlen($parentKey);
                if ($parentKey && $parentKey[$len - 1] == 's') {
                    $key = substr($parentKey, 0, $len - 1);
                } else {
                    $key = 'object';
                }
            }
        }
        return $key;
    }

    protected function stripInvalidXml($key, $string)
    {
        $strippedValue = preg_replace(
            '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u',
            '',
            (string) $string
        );
        /*if ($this->escapeSpecialChars) {
            if (!in_array($key, $this->disableEscapingFields)) {
                #$strippedValue = htmlspecialchars($strippedValue);
            }
        }*/
        return $strippedValue;
    }


    protected function throwXmlException($message)
    {
        $message .= "\n";
        foreach (libxml_get_errors() as $error) {
            // XML error codes: http://www.xmlsoft.org/html/libxml-xmlerror.html
            $message .= "\tLine " . $error->line . " (Error Code: " . $error->code . "): " . $error->message;
            if (strpos($error->message, "\n") === FALSE) {
                $message .= "\n";
            }
        }
        libxml_clear_errors();
        throw new LocalizedException(__($message));
    }
}

