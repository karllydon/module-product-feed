<?php

namespace VaxLtd\ProductFeed\Model\Format;




class Csv extends AbstractFormat
{

    /**
     * 
     * @param array $rows
     * @return bool|resource
     */
    public function formatFile($rows)
    {
        $headers = [];
        $outputRows = [];
        try {
            foreach ($rows as $product) {
                if (array_keys($product) != $headers) {
                    foreach (array_keys($product) as $key) {
                        if (!in_array($key, $headers)) {
                            $headers[] = $key;
                        }
                    }
                }
            }
            foreach ($rows as $product) {
                foreach ($headers as $header) {
                    if (!in_array($header, array_keys($product))) {
                        $product[$header] = "";
                    }
                }
                ksort($product);
                $outputRows[] = $this->checkSerialized(array_values($product));
            }
            sort($headers);
            $fp = fopen('php://temp', 'r+b');
            fputcsv($fp, $headers);
            foreach ($outputRows as $row) {
                fputcsv($fp, $row);
            }
            rewind($fp);
            return $fp;
        } catch (\Exception $e) {
            $this->logger->error("Could not format prodfeed as csv: " . $e->getMessage());
            $this->logger->error("Headers: " . print_r($headers, true));
            return false;
        }

    }

    /**
     * To ensure no nested arrays
     * 
     * @var array $row
     * @return array 
     */
    private function checkSerialized($row)
    {
        $out = [];
        foreach ($row as $element) {
            $out[] = is_array($element) ? $this->json->serialize($element) : $element;
        }
        return $out;
    }



}