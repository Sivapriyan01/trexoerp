<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Purchase;
use App\Models\DailyExpense;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Setting;
use Carbon\Carbon;

class TallyService
{
    /**
     * Generate XML for Sales Vouchers
     */
    public function generateSalesXml($bills)
    {
        $xml = $this->getHeader();

        foreach ($bills as $bill) {
            $date = $bill->bill_date->format('Ymd');
            $partyName = htmlspecialchars($bill->customer_name ?: 'Cash', ENT_XML1);
            $salesLedger = htmlspecialchars(Setting::get('tally_sales_ledger', 'Sales Account'), ENT_XML1);
            $cgstLedger = htmlspecialchars(Setting::get('tally_cgst_ledger', 'Output CGST'), ENT_XML1);
            $sgstLedger = htmlspecialchars(Setting::get('tally_sgst_ledger', 'Output SGST'), ENT_XML1);
            $roundOffLedger = htmlspecialchars(Setting::get('tally_roundoff_ledger', 'Round Off'), ENT_XML1);

            $xml .= "        <TALLYMESSAGE xmlns:UDF=\"TallyUDF\">\n";
            $xml .= "          <VOUCHER VCHTYPE=\"Sales\" ACTION=\"Create\" OBJVIEW=\"Invoice\">\n";
            $xml .= "            <DATE>{$date}</DATE>\n";
            $xml .= "            <VOUCHERTYPENAME>Sales</VOUCHERTYPENAME>\n";
            $xml .= "            <VOUCHERNUMBER>{$bill->invoice_no}</VOUCHERNUMBER>\n";
            $xml .= "            <PARTYLEDGERNAME>{$partyName}</PARTYLEDGERNAME>\n";
            $xml .= "            <PERSISTEDVIEW>Invoice</PERSISTEDVIEW>\n";

            // Party Entry (Debit)
            $xml .= $this->getLedgerEntry($partyName, 'Yes', -$bill->grand_total);

            // Sales Entry (Credit)
            $taxableValue = $bill->subtotal - $bill->discount_amount;
            $xml .= $this->getLedgerEntry($salesLedger, 'No', $taxableValue);

            // GST Entries
            if ($bill->gst_amount > 0) {
                $halfGst = round($bill->gst_amount / 2, 2);
                $xml .= $this->getLedgerEntry($cgstLedger, 'No', $halfGst);
                $xml .= $this->getLedgerEntry($sgstLedger, 'No', $halfGst);
            }

            // Round Off
            if ($bill->round_off != 0) {
                $xml .= $this->getLedgerEntry($roundOffLedger, 'No', -$bill->round_off);
            }
            
            // Inventory Items
            if ($bill->relationLoaded('items') && $bill->items->isNotEmpty()) {
                foreach ($bill->items as $item) {
                    $qty = $item->quantity ?: 1;
                    $rate = $item->mrp ?: ($item->total / $qty);
                    $amount = $item->total ?: 0;
                    $name = $item->product_name ?? 'Item';
                    $xml .= $this->getInventoryEntry($name, $qty, $rate, $amount);
                }
            }

            $xml .= "          </VOUCHER>\n";
            $xml .= "        </TALLYMESSAGE>\n";
        }

        $xml .= $this->getFooter();
        return $xml;
    }

    /**
     * Generate XML for Purchase Vouchers
     */
    public function generatePurchaseXml($purchases)
    {
        $xml = $this->getHeader();

        foreach ($purchases as $purchase) {
            $date = $purchase->invoice_date->format('Ymd');
            $partyName = htmlspecialchars($purchase->vendor->name ?? 'Unknown Supplier', ENT_XML1);
            $purchaseLedger = htmlspecialchars(Setting::get('tally_purchase_ledger', 'Purchase Account'), ENT_XML1);
            $cgstLedger = htmlspecialchars(Setting::get('tally_input_cgst_ledger', 'Input CGST'), ENT_XML1);
            $sgstLedger = htmlspecialchars(Setting::get('tally_input_sgst_ledger', 'Input SGST'), ENT_XML1);

            $xml .= "        <TALLYMESSAGE xmlns:UDF=\"TallyUDF\">\n";
            $xml .= "          <VOUCHER VCHTYPE=\"Purchase\" ACTION=\"Create\" OBJVIEW=\"Invoice\">\n";
            $xml .= "            <DATE>{$date}</DATE>\n";
            $xml .= "            <VOUCHERTYPENAME>Purchase</VOUCHERTYPENAME>\n";
            $xml .= "            <VOUCHERNUMBER>{$purchase->invoice_ref}</VOUCHERNUMBER>\n";
            $xml .= "            <PARTYLEDGERNAME>{$partyName}</PARTYLEDGERNAME>\n";
            $xml .= "            <PERSISTEDVIEW>Invoice</PERSISTEDVIEW>\n";

            // Party Entry (Credit)
            $xml .= $this->getLedgerEntry($partyName, 'No', $purchase->total_amount);

            // Purchase Entry (Debit)
            $taxableValue = $purchase->total_amount - $purchase->gst_amount;
            $xml .= $this->getLedgerEntry($purchaseLedger, 'Yes', -$taxableValue);

            // GST Entries
            if ($purchase->gst_amount > 0) {
                $halfGst = round($purchase->gst_amount / 2, 2);
                $xml .= $this->getLedgerEntry($cgstLedger, 'Yes', -$halfGst);
                $xml .= $this->getLedgerEntry($sgstLedger, 'Yes', -$halfGst);
            }
            
            // Inventory Items
            if ($purchase->relationLoaded('items') && $purchase->items->isNotEmpty()) {
                foreach ($purchase->items as $item) {
                    $qty = $item->quantity ?: 1;
                    $rate = $item->buy_price ?: ($item->total_amount / $qty);
                    $amount = $item->total_amount ?: 0;
                    $name = $item->product_name ?? 'Item';
                    $xml .= $this->getInventoryEntry($name, $qty, $rate, $amount);
                }
            }

            $xml .= "          </VOUCHER>\n";
            $xml .= "        </TALLYMESSAGE>\n";
        }

        $xml .= $this->getFooter();
        return $xml;
    }

    /**
     * Generate XML for Expense Vouchers (Payment)
     */
    public function generateExpenseXml($expenses)
    {
        $xml = $this->getHeader();

        foreach ($expenses as $expense) {
            $date = $expense->expense_date->format('Ymd');
            $cashBankLedger = htmlspecialchars($expense->payment_mode === 'Cash' ? 'Cash' : 'Bank Account', ENT_XML1);
            $expenseLedger = htmlspecialchars($expense->category ?: 'General Expense', ENT_XML1);

            $xml .= "        <TALLYMESSAGE xmlns:UDF=\"TallyUDF\">\n";
            $xml .= "          <VOUCHER VCHTYPE=\"Payment\" ACTION=\"Create\">\n";
            $xml .= "            <DATE>{$date}</DATE>\n";
            $xml .= "            <VOUCHERTYPENAME>Payment</VOUCHERTYPENAME>\n";
            $xml .= "            <PARTYLEDGERNAME>{$expenseLedger}</PARTYLEDGERNAME>\n";

            // Expense (Debit)
            $xml .= $this->getLedgerEntry($expenseLedger, 'Yes', -$expense->amount);

            // Cash/Bank (Credit)
            $xml .= $this->getLedgerEntry($cashBankLedger, 'No', $expense->amount);

            $xml .= "          </VOUCHER>\n";
            $xml .= "        </TALLYMESSAGE>\n";
        }

        $xml .= $this->getFooter();
        return $xml;
    }

    /**
     * Generate XML for Ledgers (Masters)
     */
    public function generateLedgerXml($customers, $suppliers)
    {
        $xml = $this->getHeader('Masters');

        foreach ($customers as $customer) {
            $name = htmlspecialchars($customer->name, ENT_XML1);
            $xml .= "        <TALLYMESSAGE xmlns:UDF=\"TallyUDF\">\n";
            $xml .= "          <LEDGER NAME=\"{$name}\" ACTION=\"Create\">\n";
            $xml .= "            <NAME.LIST><NAME>{$name}</NAME></NAME.LIST>\n";
            $xml .= "            <PARENT>Sundry Debtors</PARENT>\n";
            if ($customer->gstin) {
                $xml .= "            <PARTYGSTIN>{$customer->gstin}</PARTYGSTIN>\n";
                $xml .= "            <GSTREGISTRATIONTYPE>Regular</GSTREGISTRATIONTYPE>\n";
            }
            $xml .= "          </LEDGER>\n";
            $xml .= "        </TALLYMESSAGE>\n";
        }

        foreach ($suppliers as $supplier) {
            $name = htmlspecialchars($supplier->name, ENT_XML1);
            $xml .= "        <TALLYMESSAGE xmlns:UDF=\"TallyUDF\">\n";
            $xml .= "          <LEDGER NAME=\"{$name}\" ACTION=\"Create\">\n";
            $xml .= "            <NAME.LIST><NAME>{$name}</NAME></NAME.LIST>\n";
            $xml .= "            <PARENT>Sundry Creditors</PARENT>\n";
            if ($supplier->gstin) {
                $xml .= "            <PARTYGSTIN>{$supplier->gstin}</PARTYGSTIN>\n";
                $xml .= "            <GSTREGISTRATIONTYPE>Regular</GSTREGISTRATIONTYPE>\n";
            }
            $xml .= "          </LEDGER>\n";
            $xml .= "        </TALLYMESSAGE>\n";
        }

        $xml .= $this->getFooter();
        return $xml;
    }

    /**
     * Generate XML for Products (Stock Items)
     */
    public function generateProductsXml($products)
    {
        $xml = $this->getHeader('Masters');

        foreach ($products as $product) {
            $name = htmlspecialchars($product->product_name, ENT_XML1);
            $hsn = htmlspecialchars($product->hsn ?? '', ENT_XML1);
            $unit = htmlspecialchars($product->unit ?? 'Nos', ENT_XML1);
            $price = $product->mrp ?? 0;
            
            $xml .= "        <TALLYMESSAGE xmlns:UDF=\"TallyUDF\">\n";
            $xml .= "          <STOCKITEM NAME=\"{$name}\" ACTION=\"Create\">\n";
            $xml .= "            <NAME.LIST><NAME>{$name}</NAME></NAME.LIST>\n";
            $xml .= "            <BASEUNITS>{$unit}</BASEUNITS>\n";
            if ($hsn) {
                $xml .= "            <HSNCODE>{$hsn}</HSNCODE>\n";
            }
            $xml .= "            <STANDARDPRICE>{$price}</STANDARDPRICE>\n";
            $xml .= "            <COSTINGMETHOD>Avg. Cost</COSTINGMETHOD>\n";
            $xml .= "            <VALUATIONMETHOD>Avg. Price</VALUATIONMETHOD>\n";
            $xml .= "          </STOCKITEM>\n";
            $xml .= "        </TALLYMESSAGE>\n";
        }

        $xml .= $this->getFooter();
        return $xml;
    }

    private function getHeader($type = 'Vouchers')
    {
        return "<?xml version=\"1.0\"?>\n<ENVELOPE>\n  <HEADER>\n    <TALLYREQUEST>Import Data</TALLYREQUEST>\n  </HEADER>\n  <BODY>\n    <IMPORTDATA>\n      <REQUESTDESC>\n        <REPORTNAME>{$type}</REPORTNAME>\n      </REQUESTDESC>\n      <REQUESTDATA>\n";
    }

    private function getFooter()
    {
        return "      </REQUESTDATA>\n    </IMPORTDATA>\n  </BODY>\n</ENVELOPE>";
    }

    private function getLedgerEntry($name, $isPositive, $amount)
    {
        $name = htmlspecialchars($name, ENT_XML1);
        return "            <ALLLEDGERENTRIES.LIST>\n" .
            "              <LEDGERNAME>{$name}</LEDGERNAME>\n" .
            "              <ISDEEMEDPOSITIVE>{$isPositive}</ISDEEMEDPOSITIVE>\n" .
            "              <AMOUNT>{$amount}</AMOUNT>\n" .
            "            </ALLLEDGERENTRIES.LIST>\n";
    }

    private function getInventoryEntry($itemName, $qty, $rate, $amount)
    {
        $itemName = htmlspecialchars($itemName, ENT_XML1);
        return "            <ALLINVENTORYENTRIES.LIST>\n" .
            "              <STOCKITEMNAME>{$itemName}</STOCKITEMNAME>\n" .
            "              <BILLEDQTY>{$qty}</BILLEDQTY>\n" .
            "              <RATE>{$rate}</RATE>\n" .
            "              <AMOUNT>{$amount}</AMOUNT>\n" .
            "            </ALLINVENTORYENTRIES.LIST>\n";
    }

    /**
     * Sanitize and convert XML content (handles UTF-16, BOMs, and illegal XML control characters)
     */
    private function sanitizeXmlContent($xmlContent)
    {
        if (empty($xmlContent)) {
            return '';
        }

        // Robust UTF-16 Detection and Conversion
        $bom = substr($xmlContent, 0, 2);
        if ($bom === "\xFF\xFE") {
            $xmlContent = mb_convert_encoding(substr($xmlContent, 2), 'UTF-8', 'UTF-16LE');
        } elseif ($bom === "\xFE\xFF") {
            $xmlContent = mb_convert_encoding(substr($xmlContent, 2), 'UTF-8', 'UTF-16BE');
        } elseif (stripos(substr($xmlContent, 0, 100), 'encoding="UTF-16"') !== false) {
            $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'UTF-16');
        } else {
            // If iconv/mbstring isn't needed, just ensure it's valid UTF-8
            if (!mb_check_encoding($xmlContent, 'UTF-8')) {
                $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'auto');
            }
        }

        // Remove UTF-8 BOM if present
        if (str_starts_with($xmlContent, "\xEF\xBB\xBF")) {
            $xmlContent = substr($xmlContent, 3);
        }

        // Scrub illegal XML control characters (raw bytes) using str_replace
        $controls = [];
        for ($i = 0; $i <= 31; $i++) {
            if ($i !== 9 && $i !== 10 && $i !== 13)
                $controls[] = chr($i);
        }
        $controls[] = chr(127);
        $xmlContent = str_replace($controls, '', $xmlContent);

        // Strip invalid XML character references like &#4; &#1; etc. that Tally embeds.
        $xmlContent = preg_replace_callback('/&#(\d+);/', function ($m) {
            $code = (int) $m[1];
            if ($code === 9 || $code === 10 || $code === 13 || $code >= 32) {
                return $m[0]; // valid, keep it
            }
            return ''; // invalid control char ref, strip it
        }, $xmlContent);

        // Strip invalid hex char refs like &#x4; &#x1C; etc.
        $xmlContent = preg_replace_callback('/&#x([0-9a-fA-F]+);/', function ($m) {
            $code = hexdec($m[1]);
            if ($code === 9 || $code === 10 || $code === 13 || $code >= 32) {
                return $m[0];
            }
            return '';
        }, $xmlContent);

        // Fix unescaped ampersands which are VERY common in Tally XML (e.g. "M/s A & B")
        $xmlContent = preg_replace('/&(?!(?:amp|quot|apos|lt|gt|#x?\w+);)/', '&amp;', $xmlContent);

        return trim($xmlContent);
    }

    public function parseLedgersXml($xmlContent)
    {
        $xmlContent = $this->sanitizeXmlContent($xmlContent);
        if (empty($xmlContent)) {
            return [];
        }

        // Branch for Excel XML export
        if (str_contains($xmlContent, 'progid="Excel.Sheet"')) {
            return $this->parseExcelXmlLedgers($xmlContent);
        }

        // Suppress errors and load XML
        libxml_use_internal_errors(true);
        $xml = @simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_PARSEHUGE);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $errorMsg = 'XML Parse Error: ';
            $count = 0;
            foreach ($errors as $error) {
                $errorMsg .= trim($error->message) . " (Line: {$error->line}); ";
                if (++$count >= 5)
                    break; // Only show first 5 errors
            }
            throw new \Exception($errorMsg . " | Length: " . strlen($xmlContent));
        }

        $ledgers = [];

        // Build group mapping to resolve custom nested parents
        $groupMap = [];
        $groupNodes = $xml->xpath("//*[local-name()='GROUP']");
        if ($groupNodes) {
            foreach ($groupNodes as $node) {
                $groupName = '';
                foreach ($node->attributes() as $attrName => $attrVal) {
                    if (strtolower($attrName) === 'name') {
                        $groupName = trim((string) $attrVal);
                        break;
                    }
                }
                if (!$groupName) {
                    $nameNodes = $node->xpath(".//*[local-name()='NAME']");
                    if ($nameNodes) {
                        $groupName = trim((string) $nameNodes[0]);
                    }
                }

                $parentGroup = '';
                $parentNodes = $node->xpath(".//*[local-name()='PARENT']");
                if ($parentNodes) {
                    $parentGroup = trim((string) $parentNodes[0]);
                }

                if ($groupName && $parentGroup) {
                    $groupMap[strtolower($groupName)] = strtolower($parentGroup);
                }
            }
        }

        $resolveRootParent = function ($parentName) use ($groupMap) {
            $current = strtolower($parentName);
            $visited = [];
            while (!empty($current) && !isset($visited[$current])) {
                $visited[$current] = true;
                if (str_contains($current, 'debtors') || $current === 'sundry debtors') {
                    return 'Sundry Debtors';
                }
                if (str_contains($current, 'creditors') || $current === 'sundry creditors') {
                    return 'Sundry Creditors';
                }
                if (isset($groupMap[$current])) {
                    $current = $groupMap[$current];
                } else {
                    break;
                }
            }
            return $parentName;
        };

        // ── STRATEGY 1: Ledger Masters Export ────────────────────────────────
        // Supports flexible XML attribute names and nested elements
        $masterNodes = $xml->xpath("//*[local-name()='LEDGER']");
        if ($masterNodes) {
            foreach ($masterNodes as $node) {
                $name = '';
                foreach ($node->attributes() as $attrName => $attrVal) {
                    if (strtolower($attrName) === 'name') {
                        $name = trim((string) $attrVal);
                        break;
                    }
                }
                if (!$name) {
                    $nameNodes = $node->xpath(".//*[local-name()='NAME']");
                    if ($nameNodes)
                        $name = trim((string) $nameNodes[0]);
                }
                if (!$name) {
                    $nameNodes = $node->xpath(".//*[local-name()='NAME.LIST']//*[local-name()='NAME']");
                    if ($nameNodes)
                        $name = trim((string) $nameNodes[0]);
                }

                if (!$name)
                    continue;

                $parentNodes = $node->xpath(".//*[local-name()='PARENT']");
                $parent = $parentNodes ? trim((string) $parentNodes[0]) : '';
                if ($parent) {
                    $parent = $resolveRootParent($parent);
                }

                $gstinNodes = $node->xpath(".//*[local-name()='PARTYGSTIN' or local-name()='GSTIN']");
                $gstin = $gstinNodes ? trim((string) $gstinNodes[0]) : '';

                $phone = '';
                foreach (['LEDPHONE', 'PRILEDMOBILE', 'MOBILENUMBER', 'PHONE', 'MOBILE'] as $tag) {
                    $nodes = $node->xpath(".//*[local-name()='{$tag}']");
                    if ($nodes) {
                        $phone = trim((string) $nodes[0]);
                        break;
                    }
                }

                $email = '';
                $emailNodes = $node->xpath(".//*[local-name()='LEDEMAIL' or local-name()='EMAIL']");
                if ($emailNodes)
                    $email = trim((string) $emailNodes[0]);

                $address = '';
                $addrNodes = $node->xpath(".//*[local-name()='ADDRESS']");
                if ($addrNodes) {
                    $parts = array_filter(array_map(fn($a) => trim((string) $a), $addrNodes));
                    $address = implode(', ', $parts);
                }

                $stateNodes = $node->xpath(".//*[local-name()='STATE' or local-name()='STATENAME']");
                $state = $stateNodes ? trim((string) $stateNodes[0]) : '';

                $pincodeNodes = $node->xpath(".//*[local-name()='PINCODE' or local-name()='POSTALCODE']");
                $pincode = $pincodeNodes ? trim((string) $pincodeNodes[0]) : '';

                $cityNodes = $node->xpath(".//*[local-name()='CITY']");
                $city = $cityNodes ? trim((string) $cityNodes[0]) : '';

                if ($name && $parent) {
                    $ledgers[] = [
                        'name' => $name,
                        'parent' => $parent,
                        'gstin' => $gstin,
                        'phone' => $phone,
                        'email' => $email,
                        'address' => $address,
                        'city' => $city,
                        'state' => $state,
                        'pincode' => $pincode,
                    ];
                }
            }
        }

        // ── STRATEGY 2: Transactions/Vouchers Export ──────────────────────────
        if (empty($ledgers)) {
            $voucherNodes = $xml->xpath("//*[local-name()='VOUCHER']");
            if ($voucherNodes) {
                $seen = [];
                foreach ($voucherNodes as $voucher) {
                    $vchTypeNodes = $voucher->xpath(".//*[local-name()='VOUCHERTYPENAME']");
                    $vchType = $vchTypeNodes ? strtolower(trim((string) $vchTypeNodes[0])) : '';

                    $partyNodes = $voucher->xpath(".//*[local-name()='PARTYLEDGERNAME']");
                    $partyName = $partyNodes ? trim((string) $partyNodes[0]) : '';

                    if (!$partyName || isset($seen[$partyName]))
                        continue;
                    $seen[$partyName] = true;

                    if (str_contains($vchType, 'sales') || str_contains($vchType, 'receipt')) {
                        $parent = 'Sundry Debtors';
                    } elseif (str_contains($vchType, 'purchase') || str_contains($vchType, 'payment')) {
                        $parent = 'Sundry Creditors';
                    } else {
                        $parent = 'Sundry Debtors';
                    }

                    $gstinNodes = $voucher->xpath(".//*[local-name()='PARTYGSTIN' or local-name()='GSTIN']");
                    $gstin = $gstinNodes ? trim((string) $gstinNodes[0]) : '';

                    $address = '';
                    $addrNodes = $voucher->xpath(".//*[local-name()='ADDRESS' or local-name()='BASICBUYERADDRESS']");
                    if ($addrNodes) {
                        $parts = array_filter(array_map(fn($a) => trim((string) $a), $addrNodes));
                        $address = implode(', ', $parts);
                    }

                    $phone = '';
                    foreach (['BASICBUYERPHONE', 'LEDGERPHONE', 'PHONE', 'MOBILE'] as $tag) {
                        $nodes = $voucher->xpath(".//*[local-name()='{$tag}']");
                        if ($nodes) {
                            $phone = trim((string) $nodes[0]);
                            break;
                        }
                    }

                    $email = '';
                    $emailNodes = $voucher->xpath(".//*[local-name()='LEDEMAIL' or local-name()='EMAIL']");
                    if ($emailNodes)
                        $email = trim((string) $emailNodes[0]);

                    $stateNodes = $voucher->xpath(".//*[local-name()='STATE' or local-name()='BASICBUYERSTATE' or local-name()='STATENAME']");
                    $state = $stateNodes ? trim((string) $stateNodes[0]) : '';

                    $pincodeNodes = $voucher->xpath(".//*[local-name()='PINCODE' or local-name()='BASICBUYERPINCODE' or local-name()='POSTALCODE']");
                    $pincode = $pincodeNodes ? trim((string) $pincodeNodes[0]) : '';

                    $cityNodes = $voucher->xpath(".//*[local-name()='CITY']");
                    $city = $cityNodes ? trim((string) $cityNodes[0]) : '';

                    $ledgers[] = [
                        'name' => $partyName,
                        'parent' => $parent,
                        'gstin' => $gstin,
                        'phone' => $phone,
                        'email' => $email,
                        'address' => $address,
                        'city' => $city,
                        'state' => $state,
                        'pincode' => $pincode,
                    ];
                }
            }
        }

        libxml_clear_errors();

        // Deduplicate by name and merge fields so no data is lost
        return array_values(array_reduce($ledgers, function ($carry, $item) {
            $name = $item['name'];
            if (!isset($carry[$name])) {
                $carry[$name] = $item;
            } else {
                foreach (['gstin', 'phone', 'email', 'address', 'city', 'state', 'pincode'] as $f) {
                    if (empty($carry[$name][$f]) && !empty($item[$f])) {
                        $carry[$name][$f] = $item[$f];
                    }
                }
            }
            return $carry;
        }, []));
    }

    public function parseStockItemsXml($xmlContent)
    {
        $xmlContent = $this->sanitizeXmlContent($xmlContent);
        if (empty($xmlContent)) return [];

        libxml_use_internal_errors(true);
        $xml = @simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_PARSEHUGE);
        if ($xml === false) return [];

        $products = [];
        $itemNodes = $xml->xpath("//*[local-name()='STOCKITEM']");
        
        if ($itemNodes) {
            foreach ($itemNodes as $node) {
                $name = '';
                foreach ($node->attributes() as $attrName => $attrVal) {
                    if (strtolower($attrName) === 'name') {
                        $name = trim((string)$attrVal);
                        break;
                    }
                }
                if (!$name) {
                    $nameNodes = $node->xpath(".//*[local-name()='NAME']");
                    if ($nameNodes) $name = trim((string)$nameNodes[0]);
                }

                if (!$name) continue;

                $parentNodes = $node->xpath(".//*[local-name()='PARENT']");
                $parent = $parentNodes ? trim((string)$parentNodes[0]) : '';

                $baseUnitsNodes = $node->xpath(".//*[local-name()='BASEUNITS']");
                $unit = $baseUnitsNodes ? trim((string)$baseUnitsNodes[0]) : '';

                $costingNodes = $node->xpath(".//*[local-name()='COSTINGMETHOD']");
                $costing = $costingNodes ? trim((string)$costingNodes[0]) : '';
                
                $hsnNodes = $node->xpath(".//*[local-name()='HSNCODE' or local-name()='HSN']");
                $hsn = $hsnNodes ? trim((string)$hsnNodes[0]) : '';

                $products[] = [
                    'name' => $name,
                    'parent' => $parent,
                    'unit' => $unit,
                    'costing' => $costing,
                    'hsn' => $hsn,
                ];
            }
        }

        libxml_clear_errors();
        return $products;
    }

    private function parseExcelXmlLedgers($xmlContent)
    {
        libxml_use_internal_errors(true);
        $xmlContent = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlContent);
        $xml = @simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_PARSEHUGE);
        
        if ($xml === false) {
            return [];
        }

        $ledgers = [];
        $rows = $xml->xpath("//*[local-name()='Row']");
        
        if (!$rows) return [];

        $headers = [];
        $headerRowIndex = 0;

        foreach ($rows as $index => $row) {
            $cells = $row->xpath(".//*[local-name()='Cell']");
            if (!$cells) continue;
            
            $rowData = [];
            foreach ($cells as $cell) {
                $dataNode = $cell->xpath(".//*[local-name()='Data']");
                $rowData[] = $dataNode ? trim((string)$dataNode[0]) : '';
            }

            $hasName = false;
            $hasParent = false;
            foreach ($rowData as $i => $val) {
                $lowerVal = strtolower($val);
                if (str_contains($lowerVal, 'name of ledger') || $lowerVal === 'name' || $lowerVal === 'ledger name') {
                    $headers['name'] = $i;
                    $hasName = true;
                } elseif (str_contains($lowerVal, 'under') || $lowerVal === 'parent' || $lowerVal === 'group') {
                    $headers['parent'] = $i;
                    $hasParent = true;
                } elseif (str_contains($lowerVal, 'gstin') || str_contains($lowerVal, 'uin')) {
                    $headers['gstin'] = $i;
                } elseif (str_contains($lowerVal, 'phone') || str_contains($lowerVal, 'mobile')) {
                    $headers['phone'] = $i;
                } elseif (str_contains($lowerVal, 'email')) {
                    $headers['email'] = $i;
                } elseif (str_contains($lowerVal, 'address')) {
                    $headers['address'] = $i;
                } elseif (str_contains($lowerVal, 'state')) {
                    $headers['state'] = $i;
                } elseif (str_contains($lowerVal, 'pin') || str_contains($lowerVal, 'postal')) {
                    $headers['pincode'] = $i;
                } elseif (str_contains($lowerVal, 'city')) {
                    $headers['city'] = $i;
                }
            }

            if ($hasName && $hasParent) {
                $headerRowIndex = $index;
                break;
            }
        }

        if (empty($headers) && count($rows) > 0) {
            $headers = ['name' => 0, 'parent' => 1];
        }

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $cells = $row->xpath(".//*[local-name()='Cell']");
            
            $rowData = [];
            $colIndex = 0;
            foreach ($cells as $cell) {
                $attrs = $cell->attributes('urn:schemas-microsoft-com:office:spreadsheet');
                if (isset($attrs['Index'])) {
                    $colIndex = (int)$attrs['Index'] - 1;
                }
                
                $dataNode = $cell->xpath(".//*[local-name()='Data']");
                $val = $dataNode ? trim((string)$dataNode[0]) : '';
                $rowData[$colIndex] = $val;
                $colIndex++;
            }

            $name = isset($headers['name'], $rowData[$headers['name']]) ? $rowData[$headers['name']] : '';
            $parent = isset($headers['parent'], $rowData[$headers['parent']]) ? $rowData[$headers['parent']] : '';

            if (!$name || !$parent) continue;

            $resolveRootParent = function($parentName) {
                $current = strtolower($parentName);
                if (str_contains($current, 'debtor') || $current === 'sundry debtors') return 'Sundry Debtors';
                if (str_contains($current, 'creditor') || $current === 'sundry creditors') return 'Sundry Creditors';
                return $parentName;
            };

            $parent = $resolveRootParent($parent);

            $ledgers[] = [
                'name'    => $name,
                'parent'  => $parent,
                'gstin'   => isset($headers['gstin'], $rowData[$headers['gstin']]) ? $rowData[$headers['gstin']] : '',
                'phone'   => isset($headers['phone'], $rowData[$headers['phone']]) ? $rowData[$headers['phone']] : '',
                'email'   => isset($headers['email'], $rowData[$headers['email']]) ? $rowData[$headers['email']] : '',
                'address' => isset($headers['address'], $rowData[$headers['address']]) ? $rowData[$headers['address']] : '',
                'city'    => isset($headers['city'], $rowData[$headers['city']]) ? $rowData[$headers['city']] : '',
                'state'   => isset($headers['state'], $rowData[$headers['state']]) ? $rowData[$headers['state']] : '',
                'pincode' => isset($headers['pincode'], $rowData[$headers['pincode']]) ? $rowData[$headers['pincode']] : '',
            ];
        }

        libxml_clear_errors();
        return $ledgers;
    }

    public function parseStandardExcelLedgers(array $rows)
    {
        if (empty($rows)) return [];

        $ledgers = [];
        $headers = [];
        $headerRowIndex = -1;

        foreach ($rows as $index => $rowData) {
            $hasName = false;
            $hasParent = false;
            foreach ($rowData as $i => $val) {
                if ($val === null) continue;
                // Clean Tally raw export markers like $Name, $_PartyGSTIN
                $cleanVal = str_replace(['$', '_'], '', $val);
                $lowerVal = strtolower(trim((string)$cleanVal));
                
                if (str_contains($lowerVal, 'name of ledger') || $lowerVal === 'name' || $lowerVal === 'ledger name' || $lowerVal === 'particulars') {
                    $headers['name'] = $i;
                    $hasName = true;
                } elseif (str_contains($lowerVal, 'under') || $lowerVal === 'parent' || $lowerVal === 'group') {
                    $headers['parent'] = $i;
                    $hasParent = true;
                } elseif (str_contains($lowerVal, 'partygstin') || $lowerVal === 'gstin' || str_contains($lowerVal, 'uin')) {
                    $headers['gstin'] = $i;
                } elseif (str_contains($lowerVal, 'ledgermobile') || $lowerVal === 'phone' || str_contains($lowerVal, 'mobile')) {
                    $headers['phone'] = $i;
                } elseif ($lowerVal === 'email' || str_contains($lowerVal, 'email')) {
                    $headers['email'] = $i;
                } elseif (str_contains($lowerVal, 'address1') || $lowerVal === 'address' || str_contains($lowerVal, 'address')) {
                    $headers['address'] = $i;
                } elseif (str_contains($lowerVal, 'priorstatename') || $lowerVal === 'state' || str_contains($lowerVal, 'state')) {
                    $headers['state'] = $i;
                } elseif (str_contains($lowerVal, 'pin') || str_contains($lowerVal, 'postal') || $lowerVal === 'pincode') {
                    $headers['pincode'] = $i;
                } elseif ($lowerVal === 'city' || str_contains($lowerVal, 'city')) {
                    $headers['city'] = $i;
                }
            }

            if ($hasName && $hasParent) {
                $headerRowIndex = $index;
                break;
            } elseif ($hasName) { 
                $headerRowIndex = $index;
            }
        }

        if (empty($headers)) {
            $headers = ['name' => 0, 'parent' => 1];
            $headerRowIndex = -1;
        }

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $rowData = $rows[$i];
            
            $name = isset($headers['name'], $rowData[$headers['name']]) ? trim((string)$rowData[$headers['name']]) : '';
            $parent = isset($headers['parent'], $rowData[$headers['parent']]) ? trim((string)$rowData[$headers['parent']]) : '';

            if (!$name) continue;
            if (!$parent) $parent = 'Sundry Debtors';

            $resolveRootParent = function($parentName) {
                $current = strtolower($parentName);
                if (str_contains($current, 'debtor') || $current === 'sundry debtors' || $current === 'customers') return 'Sundry Debtors';
                if (str_contains($current, 'creditor') || $current === 'sundry creditors' || $current === 'suppliers') return 'Sundry Creditors';
                return $parentName;
            };

            $parent = $resolveRootParent($parent);

            $ledgers[] = [
                'name'    => $name,
                'parent'  => $parent,
                'gstin'   => isset($headers['gstin'], $rowData[$headers['gstin']]) ? trim((string)$rowData[$headers['gstin']]) : '',
                'phone'   => isset($headers['phone'], $rowData[$headers['phone']]) ? trim((string)$rowData[$headers['phone']]) : '',
                'email'   => isset($headers['email'], $rowData[$headers['email']]) ? trim((string)$rowData[$headers['email']]) : '',
                'address' => isset($headers['address'], $rowData[$headers['address']]) ? trim((string)$rowData[$headers['address']]) : '',
                'city'    => isset($headers['city'], $rowData[$headers['city']]) ? trim((string)$rowData[$headers['city']]) : '',
                'state'   => isset($headers['state'], $rowData[$headers['state']]) ? trim((string)$rowData[$headers['state']]) : '',
                'pincode' => isset($headers['pincode'], $rowData[$headers['pincode']]) ? trim((string)$rowData[$headers['pincode']]) : '',
            ];
        }

        return $ledgers;
    }

    public function parseStandardExcelStockItems(array $rows)
    {
        if (empty($rows)) return [];

        $products = [];
        $headers = [];
        $headerRowIndex = -1;

        foreach ($rows as $index => $rowData) {
            $hasName = false;
            foreach ($rowData as $i => $val) {
                if ($val === null) continue;
                $cleanVal = str_replace(['$', '_'], '', $val);
                $lowerVal = strtolower(trim((string)$cleanVal));
                
                if (str_contains($lowerVal, 'name of item') || $lowerVal === 'name' || $lowerVal === 'item name' || str_contains($lowerVal, 'product name')) {
                    $headers['name'] = $i;
                    $hasName = true;
                } elseif (str_contains($lowerVal, 'under') || $lowerVal === 'parent' || $lowerVal === 'group') {
                    $headers['parent'] = $i;
                } elseif (str_contains($lowerVal, 'baseunits') || $lowerVal === 'unit') {
                    $headers['unit'] = $i;
                } elseif (str_contains($lowerVal, 'costingmethod') || str_contains($lowerVal, 'costing')) {
                    $headers['costing'] = $i;
                } elseif (str_contains($lowerVal, 'hsncode') || str_contains($lowerVal, 'hsn')) {
                    $headers['hsn'] = $i;
                } elseif (str_contains($lowerVal, 'closingqty') || str_contains($lowerVal, 'closingbalance') || str_contains($lowerVal, 'clos') || $lowerVal === 'stock') {
                    if (!isset($headers['stock'])) $headers['stock'] = $i; // Use first occurrence
                } elseif (str_contains($lowerVal, 'lastsaleprice') || str_contains($lowerVal, 'closingrate') || str_contains($lowerVal, 'standardrate') || $lowerVal === 'mrp') {
                    $headers['mrp'] = $i;
                } elseif (str_contains($lowerVal, 'lastpurcprice') || str_contains($lowerVal, 'standardcost') || str_contains($lowerVal, 'dealer price')) {
                    $headers['dealer_price'] = $i;
                }
            }

            if ($hasName) { 
                $headerRowIndex = $index;
                break;
            }
        }

        // To ensure we don't accidentally parse a Daybook or Purchase Register as products,
        // we must require at least one other product-specific header besides just 'name'
        if (empty($headers) || !isset($headers['name']) || (!isset($headers['unit']) && !isset($headers['costing']) && !isset($headers['hsn']))) {
            return [];
        }

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $rowData = $rows[$i];
            
            $name = isset($headers['name'], $rowData[$headers['name']]) ? trim((string)$rowData[$headers['name']]) : '';
            if (!$name) continue;

            $stockStr = isset($headers['stock'], $rowData[$headers['stock']]) ? trim((string)$rowData[$headers['stock']]) : '0';
            // Extract numeric value from something like "100 NOS"
            preg_match('/^-?\d+(?:\.\d+)?/', $stockStr, $matches);
            $stock = !empty($matches) ? (float)$matches[0] : 0;

            $mrp = isset($headers['mrp'], $rowData[$headers['mrp']]) ? (float)trim((string)$rowData[$headers['mrp']]) : 0;
            $dealer_price = isset($headers['dealer_price'], $rowData[$headers['dealer_price']]) ? (float)trim((string)$rowData[$headers['dealer_price']]) : 0;

            $products[] = [
                'name'         => $name,
                'parent'       => isset($headers['parent'], $rowData[$headers['parent']]) ? trim((string)$rowData[$headers['parent']]) : '',
                'unit'         => isset($headers['unit'], $rowData[$headers['unit']]) ? trim((string)$rowData[$headers['unit']]) : '',
                'costing'      => isset($headers['costing'], $rowData[$headers['costing']]) ? trim((string)$rowData[$headers['costing']]) : '',
                'hsn'          => isset($headers['hsn'], $rowData[$headers['hsn']]) ? trim((string)$rowData[$headers['hsn']]) : '',
                'stock'        => $stock,
                'mrp'          => $mrp,
                'dealer_price' => $dealer_price,
            ];
        }

        return $products;
    }

    public function parseStandardExcelPurchases(array $rows)
    {
        if (empty($rows)) return [];

        $purchases = [];
        $headers = [];
        $headerRowIndex = -1;

        foreach ($rows as $index => $rowData) {
            $hasDate = false;
            foreach ($rowData as $i => $val) {
                if ($val === null) continue;
                $cleanVal = str_replace(['$', '_'], '', $val);
                $lowerVal = strtolower(trim((string)$cleanVal));
                
                if ($lowerVal === 'date' || str_contains($lowerVal, 'voucher date')) {
                    $headers['date'] = $i;
                    $hasDate = true;
                } elseif ($lowerVal === 'particulars' || $lowerVal === 'party name' || $lowerVal === 'supplier') {
                    $headers['party'] = $i;
                } elseif ($lowerVal === 'vch type' || $lowerVal === 'voucher type') {
                    $headers['type'] = $i;
                } elseif (str_contains($lowerVal, 'vch no') || str_contains($lowerVal, 'voucher no') || $lowerVal === 'ref no') {
                    $headers['vch_no'] = $i;
                } elseif (str_contains($lowerVal, 'debit')) {
                    $headers['debit'] = $i;
                } elseif (str_contains($lowerVal, 'credit')) {
                    $headers['credit'] = $i;
                } elseif (str_contains($lowerVal, 'gross') || str_contains($lowerVal, 'amount') || $lowerVal === 'value' || $lowerVal === 'invoice value' || str_contains($lowerVal, 'total amount')) {
                    $headers['amount'] = $i;
                }
            }

            if ($hasDate && isset($headers['party'])) { 
                $headerRowIndex = $index;
                break;
            }
        }

        if (empty($headers) || !isset($headers['date']) || !isset($headers['party'])) {
            return [];
        }

        $currentPurchase = null;

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $rowData = $rows[$i];
            
            $dateStr = isset($headers['date'], $rowData[$headers['date']]) ? trim((string)$rowData[$headers['date']]) : '';
            $party = isset($headers['party'], $rowData[$headers['party']]) ? trim((string)$rowData[$headers['party']]) : '';
            
            // If there's no date, this row is likely an item or ledger line belonging to the last voucher
            if (!$dateStr) {
                if ($currentPurchase) {
                    $itemName = $party;
                    if (!$itemName && isset($rowData[0])) $itemName = trim((string)$rowData[0]);
                    if (!$itemName && isset($rowData[1])) $itemName = trim((string)$rowData[1]);
                    
                    if ($itemName) {
                        $lowerName = strtolower($itemName);
                        // Skip Tally structural/reference rows and taxes
                        if (in_array($lowerName, ['new ref', 'agst ref', 'advance', 'on account', 'round off']) || str_ends_with($lowerName, 'gst')) {
                            continue;
                        }

                        // Try to extract quantity and rate to determine if it's an inventory item
                        // Tally usually puts Qty around col 3 or 4, Rate around 4 or 5
                        $qtyRaw = '';
                        $rateRaw = '';
                        $amountRaw = '';
                        
                        $foundNumbers = [];
                        
                        // Scan columns from index 2 onwards to find Qty and Rate
                        for ($c = 2; $c < count($rowData); $c++) {
                            $val = trim((string)($rowData[$c] ?? ''));
                            if (empty($val)) continue;
                            
                            // If it contains NOS, PCS, KGS, etc. like '1 NOS'
                            if (preg_match('/^([0-9.]+)\s+([A-Za-z]+)$/', $val, $m)) {
                                $qtyRaw = $m[1];
                                $foundNumbers[] = $m[1];
                            } 
                            // If it's a rate like '1200.00/NOS'
                            elseif (preg_match('/^([0-9.]+)\/[A-Za-z]+$/', $val, $m)) {
                                $rateRaw = $m[1];
                                $foundNumbers[] = $m[1];
                            } 
                            // If it's just a number, it could be the amount or a plain quantity
                            elseif (is_numeric(str_replace(',', '', $val))) {
                                $foundNumbers[] = str_replace(',', '', $val);
                            }
                        }

                        // If regex missed them (due to Excel cell formatting hiding the 'NOS'), fallback to sequence
                        if (!$qtyRaw && !$rateRaw && count($foundNumbers) >= 2) {
                            $qtyRaw = $foundNumbers[0];
                            $rateRaw = $foundNumbers[1];
                            $amountRaw = isset($foundNumbers[2]) ? $foundNumbers[2] : ($qtyRaw * $rateRaw);
                        } elseif (count($foundNumbers) > 0) {
                            // The last number is usually the amount
                            $amountRaw = end($foundNumbers);
                        }

                        // Safeguard against numeric field overflows (e.g. from parsing reference numbers as qty)
                        if ((float)$qtyRaw > 99999999 || (float)$amountRaw > 9999999999) {
                            continue;
                        }

                        if ($qtyRaw || $rateRaw || count($foundNumbers) >= 2) {
                            $currentPurchase['items'][] = [
                                'name' => $itemName,
                                'qty' => (float)$qtyRaw ?: 1,
                                'price' => (float)$rateRaw ?: (float)$amountRaw,
                                'amount' => (float)$amountRaw
                            ];
                            // Update the reference in the array
                            $purchases[count($purchases) - 1] = $currentPurchase;
                        }
                    }
                }
                continue;
            }

            // Skip common Tally totals rows (sometimes "Grand Total" is in the Date column)
            if (str_contains(strtolower($dateStr), 'total') || str_contains(strtolower($party), 'total') || strtolower($party) === 'opening balance') {
                $currentPurchase = null;
                continue;
            }

            // A valid voucher header MUST have a party name
            if (empty($party)) {
                $currentPurchase = null;
                continue;
            }

            $vchType = isset($headers['type'], $rowData[$headers['type']]) ? trim((string)$rowData[$headers['type']]) : 'Purchase';
            $lowerType = strtolower($vchType);

            // Capture Payment Vouchers
            if (str_contains($lowerType, 'payment')) {
                if (isset($payments) === false) $payments = []; // Initialize if needed
                
                $amountStr = isset($headers['amount'], $rowData[$headers['amount']]) ? trim((string)$rowData[$headers['amount']]) : '';
                $amountRaw = str_replace(',', '', preg_replace('/[a-zA-Z\s]/', '', $amountStr));
                $amount = (float)$amountRaw;

                if ($amount > 0) {
                    $payments[] = [
                        'date' => $dateStr,
                        'party' => $party,
                        'vch_no' => $vchNo ?? '',
                        'amount' => $amount
                    ];
                }
                $currentPurchase = null;
                continue;
            }

            // Only process purchases (skip payments/journals if mixed in daybook)
            if (!str_contains($lowerType, 'purchase')) {
                $currentPurchase = null; // Reset
                continue;
            }

            $vchNo = isset($headers['vch_no'], $rowData[$headers['vch_no']]) ? trim((string)$rowData[$headers['vch_no']]) : 'PUR-' . uniqid();
            
            // Extract amount from either Debit, Credit, or generic Amount column
            $amountRaw1 = isset($headers['amount'], $rowData[$headers['amount']]) ? trim((string)$rowData[$headers['amount']]) : '0';
            $amountRaw2 = isset($headers['debit'], $rowData[$headers['debit']]) ? trim((string)$rowData[$headers['debit']]) : '0';
            $amountRaw3 = isset($headers['credit'], $rowData[$headers['credit']]) ? trim((string)$rowData[$headers['credit']]) : '0';
            
            $val1 = (float) preg_replace('/[^0-9.]/', '', $amountRaw1);
            $val2 = (float) preg_replace('/[^0-9.]/', '', $amountRaw2);
            $val3 = (float) preg_replace('/[^0-9.]/', '', $amountRaw3);
            
            $amount = max($val1, $val2, $val3);

            // FALLBACK: If Amount is still 0, Tally often puts the Gross Total in the very last column
            // Scan from Vch No column to the right to find the largest numeric value
            if ($amount == 0 && isset($headers['vch_no'])) {
                for ($colIndex = $headers['vch_no'] + 1; $colIndex < count($rowData); $colIndex++) {
                    $cellVal = isset($rowData[$colIndex]) ? trim((string)$rowData[$colIndex]) : '';
                    if (!empty($cellVal)) {
                        $cellNum = (float) preg_replace('/[^0-9.]/', '', $cellVal);
                        if ($cellNum > $amount) {
                            $amount = $cellNum;
                        }
                    }
                }
            }

            // Parse Date format from Tally (often DD-MM-YYYY)
            try {
                // If Excel numeric date
                if (is_numeric($dateStr)) {
                    $date = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateStr))->format('Y-m-d');
                } else {
                    $date = \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $date = now()->format('Y-m-d');
            }

            $currentPurchase = [
                'date' => $date,
                'party' => $party,
                'vch_no' => $vchNo,
                'amount' => $amount,
                'type' => $vchType,
                'items' => []
            ];
            $purchases[] = $currentPurchase;
        }

        return [
            'purchases' => $purchases,
            'payments' => $payments ?? []
        ];
    }

    public function parseStandardExcelSales(array $rows)
    {
        if (empty($rows)) return [];

        $sales = [];
        $receipts = [];
        $headers = [];
        $headerRowIndex = -1;

        foreach ($rows as $index => $rowData) {
            $hasDate = false;
            foreach ($rowData as $i => $val) {
                if ($val === null) continue;
                $cleanVal = str_replace(['$', '_'], '', $val);
                $lowerVal = strtolower(trim((string)$cleanVal));
                
                if ($lowerVal === 'date' || str_contains($lowerVal, 'voucher date')) {
                    $headers['date'] = $i;
                    $hasDate = true;
                } elseif ($lowerVal === 'particulars' || $lowerVal === 'party name' || $lowerVal === 'customer') {
                    $headers['party'] = $i;
                } elseif ($lowerVal === 'vch type' || $lowerVal === 'voucher type') {
                    $headers['type'] = $i;
                } elseif (str_contains($lowerVal, 'vch no') || str_contains($lowerVal, 'voucher no') || $lowerVal === 'ref no') {
                    $headers['vch_no'] = $i;
                } elseif (str_contains($lowerVal, 'debit')) {
                    $headers['debit'] = $i;
                } elseif (str_contains($lowerVal, 'credit')) {
                    $headers['credit'] = $i;
                } elseif (str_contains($lowerVal, 'gross') || str_contains($lowerVal, 'amount') || $lowerVal === 'value' || $lowerVal === 'invoice value' || str_contains($lowerVal, 'grand total') || str_contains($lowerVal, 'total amount')) {
                    $headers['amount'] = $i;
                }
            }

            if ($hasDate && isset($headers['party'])) { 
                $headerRowIndex = $index;
                break;
            }
        }

        if (empty($headers) || !isset($headers['date']) || !isset($headers['party'])) {
            return [];
        }

        $currentSale = null;

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $rowData = $rows[$i];
            
            $dateStr = isset($headers['date'], $rowData[$headers['date']]) ? trim((string)$rowData[$headers['date']]) : '';
            $party = isset($headers['party'], $rowData[$headers['party']]) ? trim((string)$rowData[$headers['party']]) : '';
            
            // If there's no date, this row is likely an item or ledger line belonging to the last voucher
            if (!$dateStr) {
                if ($currentSale) {
                    $itemName = $party;
                    if (!$itemName && isset($rowData[0])) $itemName = trim((string)$rowData[0]);
                    if (!$itemName && isset($rowData[1])) $itemName = trim((string)$rowData[1]);
                    
                    if ($itemName) {
                        $lowerName = strtolower($itemName);
                        if (in_array($lowerName, ['new ref', 'agst ref', 'advance', 'on account', 'round off']) || str_ends_with($lowerName, 'gst')) {
                            continue;
                        }

                        $qtyRaw = '';
                        $rateRaw = '';
                        $amountRaw = '';
                        
                        $foundNumbers = [];
                        
                        for ($c = 2; $c < count($rowData); $c++) {
                            $val = trim((string)($rowData[$c] ?? ''));
                            if (empty($val)) continue;
                            
                            if (preg_match('/^([0-9.]+)\s+([A-Za-z]+)$/', $val, $m)) {
                                $qtyRaw = $m[1];
                                $foundNumbers[] = $m[1];
                            } 
                            elseif (preg_match('/^([0-9.]+)\/[A-Za-z]+$/', $val, $m)) {
                                $rateRaw = $m[1];
                                $foundNumbers[] = $m[1];
                            } 
                            elseif (is_numeric(str_replace(',', '', $val))) {
                                $foundNumbers[] = str_replace(',', '', $val);
                            }
                        }

                        if (!$qtyRaw && !$rateRaw && count($foundNumbers) >= 2) {
                            $qtyRaw = $foundNumbers[0];
                            $rateRaw = $foundNumbers[1];
                            $amountRaw = isset($foundNumbers[2]) ? $foundNumbers[2] : ($qtyRaw * $rateRaw);
                        } elseif (count($foundNumbers) > 0) {
                            $amountRaw = end($foundNumbers);
                        }

                        if ((float)$qtyRaw > 99999999 || (float)$amountRaw > 9999999999) {
                            continue;
                        }

                        if ($qtyRaw || $rateRaw || count($foundNumbers) >= 2) {
                            $currentSale['items'][] = [
                                'name' => $itemName,
                                'qty' => (float)$qtyRaw ?: 1,
                                'price' => (float)$rateRaw ?: (float)$amountRaw,
                                'amount' => (float)$amountRaw
                            ];
                            $sales[count($sales) - 1] = $currentSale;
                        }
                    }
                }
                continue;
            }

            if (str_contains(strtolower($dateStr), 'total') || str_contains(strtolower($party), 'total') || strtolower($party) === 'opening balance') {
                $currentSale = null;
                continue;
            }

            if (empty($party)) {
                $currentSale = null;
                continue;
            }

            $vchType = isset($headers['type'], $rowData[$headers['type']]) ? trim((string)$rowData[$headers['type']]) : 'Sales';
            $lowerType = strtolower($vchType);

            if (str_contains($lowerType, 'receipt')) {
                $amountStr = isset($headers['amount'], $rowData[$headers['amount']]) ? trim((string)$rowData[$headers['amount']]) : '';
                $amountRaw = str_replace(',', '', preg_replace('/[a-zA-Z\s]/', '', $amountStr));
                $amount = (float)$amountRaw;

                if ($amount > 0) {
                    $receipts[] = [
                        'date' => $dateStr,
                        'party' => $party,
                        'vch_no' => $vchNo ?? '',
                        'amount' => $amount
                    ];
                }
                $currentSale = null;
                continue;
            }

            if (!str_contains($lowerType, 'sales')) {
                $currentSale = null; 
                continue;
            }

            $vchNo = isset($headers['vch_no'], $rowData[$headers['vch_no']]) ? trim((string)$rowData[$headers['vch_no']]) : 'INV-' . uniqid();
            
            $amountRaw1 = isset($headers['amount'], $rowData[$headers['amount']]) ? trim((string)$rowData[$headers['amount']]) : '0';
            $amountRaw2 = isset($headers['debit'], $rowData[$headers['debit']]) ? trim((string)$rowData[$headers['debit']]) : '0';
            $amountRaw3 = isset($headers['credit'], $rowData[$headers['credit']]) ? trim((string)$rowData[$headers['credit']]) : '0';
            
            $val1 = (float) preg_replace('/[^0-9.]/', '', $amountRaw1);
            $val2 = (float) preg_replace('/[^0-9.]/', '', $amountRaw2);
            $val3 = (float) preg_replace('/[^0-9.]/', '', $amountRaw3);
            
            $amount = max($val1, $val2, $val3);

            if ($amount == 0 && isset($headers['vch_no'])) {
                for ($colIndex = $headers['vch_no'] + 1; $colIndex < count($rowData); $colIndex++) {
                    $cellVal = isset($rowData[$colIndex]) ? trim((string)$rowData[$colIndex]) : '';
                    if (!empty($cellVal)) {
                        $cellNum = (float) preg_replace('/[^0-9.]/', '', $cellVal);
                        if ($cellNum > $amount) {
                            $amount = $cellNum;
                        }
                    }
                }
            }

            try {
                if (is_numeric($dateStr)) {
                    $date = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateStr))->format('Y-m-d');
                } else {
                    $date = \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $date = now()->format('Y-m-d');
            }

            $currentSale = [
                'date' => $date,
                'party' => $party,
                'vch_no' => $vchNo,
                'amount' => $amount,
                'type' => $vchType,
                'items' => []
            ];
            $sales[] = $currentSale;
        }

        return [
            'sales' => $sales,
            'receipts' => $receipts
        ];
    }

    public function parseVouchersXml($xmlContent)
    {
        $xmlContent = $this->sanitizeXmlContent($xmlContent);
        if (empty($xmlContent)) {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_PARSEHUGE);
        if (!$xml) {
            return [];
        }

        $voucherNodes = $xml->xpath("//*[local-name()='VOUCHER']");
        $vouchers = [];

        if ($voucherNodes) {
            foreach ($voucherNodes as $node) {
                $vchTypeNodes = $node->xpath(".//*[local-name()='VOUCHERTYPENAME']");
                $vchType = $vchTypeNodes ? trim((string) $vchTypeNodes[0]) : '';
                if (!$vchType) {
                    $vchType = trim((string) ($node['VCHTYPE'] ?? ''));
                }

                $vchNumNodes = $node->xpath(".//*[local-name()='VOUCHERNUMBER']");
                $vchNum = $vchNumNodes ? trim((string) $vchNumNodes[0]) : '';

                // Try case-insensitive DATE child first (to avoid nested DATE nodes)
                $dateNodes = $node->xpath("./*[translate(local-name(), 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') = 'DATE']");
                if (!$dateNodes) {
                    $dateNodes = $node->xpath(".//*[translate(local-name(), 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') = 'DATE']");
                }
                $dateStr = $dateNodes ? trim((string) $dateNodes[0]) : '';

                $date = null;
                if ($dateStr) {
                    // Normalize and strip everything except digits, hyphens, and slashes
                    $cleanDateStr = preg_replace('/[^0-9\-\/]/', '', $dateStr);
                    // Extract only digits to check for 8-digit YYYYMMDD
                    $digits = preg_replace('/[^0-9]/', '', $cleanDateStr);

                    if (strlen($digits) === 8) {
                        try {
                            $date = Carbon::createFromFormat('Ymd', $digits)->format('Y-m-d');
                        } catch (\Exception $e) {
                        }
                    }

                    if (!$date) {
                        try {
                            $date = Carbon::parse($dateStr)->format('Y-m-d');
                        } catch (\Exception $e) {
                        }
                    }
                }

                // Fallback to EFFECTIVEDATE if main DATE is missing or failed to parse
                if (!$date) {
                    $effDateNodes = $node->xpath("./*[translate(local-name(), 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') = 'EFFECTIVEDATE']");
                    if (!$effDateNodes) {
                        $effDateNodes = $node->xpath(".//*[translate(local-name(), 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') = 'EFFECTIVEDATE']");
                    }
                    if ($effDateNodes) {
                        $effStr = trim((string) $effDateNodes[0]);
                        $cleanEffStr = preg_replace('/[^0-9\-\/]/', '', $effStr);
                        $digits = preg_replace('/[^0-9]/', '', $cleanEffStr);

                        if (strlen($digits) === 8) {
                            try {
                                $date = Carbon::createFromFormat('Ymd', $digits)->format('Y-m-d');
                            } catch (\Exception $e) {
                            }
                        }
                        if (!$date) {
                            try {
                                $date = Carbon::parse($effStr)->format('Y-m-d');
                            } catch (\Exception $e) {
                            }
                        }
                    }
                }

                // Final fallback if no date can be parsed from XML
                if (!$date) {
                    $date = now()->format('Y-m-d');
                }

                $partyNodes = $node->xpath(".//*[local-name()='PARTYLEDGERNAME']");
                $partyName = $partyNodes ? trim((string) $partyNodes[0]) : '';

                $ledgerEntries = [];
                $entryNodes = $node->xpath(".//*[local-name()='ALLLEDGERENTRIES.LIST' or local-name()='LEDGERENTRIES.LIST']");
                foreach ($entryNodes as $entry) {
                    $ledgerNameNodes = $entry->xpath(".//*[local-name()='LEDGERNAME']");
                    $ledgerName = $ledgerNameNodes ? trim((string) $ledgerNameNodes[0]) : '';

                    $amountNodes = $entry->xpath(".//*[local-name()='AMOUNT']");
                    $amount = $amountNodes ? (float) trim((string) $amountNodes[0]) : 0.0;

                    if ($ledgerName) {
                        $ledgerEntries[] = [
                            'ledger' => $ledgerName,
                            'amount' => $amount,
                        ];
                    }
                }

                $items = [];
                $invNodes = $node->xpath(".//*[local-name()='ALLINVENTORYENTRIES.LIST' or local-name()='INVENTORYENTRIES.LIST']");
                foreach ($invNodes as $inv) {
                    $itemNameNodes = $inv->xpath(".//*[local-name()='STOCKITEMNAME']");
                    $itemName = $itemNameNodes ? trim((string) $itemNameNodes[0]) : '';

                    $qtyNodes = $inv->xpath(".//*[local-name()='BILLEDQTY' or local-name()='QTY']");
                    $qtyStr = $qtyNodes ? trim((string) $qtyNodes[0]) : '1';
                    $qty = (int) preg_replace('/[^0-9]/', '', $qtyStr) ?: 1;

                    $rateNodes = $inv->xpath(".//*[local-name()='RATE']");
                    $rateStr = $rateNodes ? trim((string) $rateNodes[0]) : '0';
                    $rate = (float) preg_replace('/[^0-9.]/', '', $rateStr) ?: 0.0;

                    $amountNodes = $inv->xpath(".//*[local-name()='AMOUNT']");
                    $amount = $amountNodes ? abs((float) trim((string) $amountNodes[0])) : 0.0;

                    if ($itemName) {
                        $items[] = [
                            'name' => $itemName,
                            'qty' => $qty,
                            'price' => $rate ?: ($amount / $qty),
                            'amount' => $amount,
                        ];
                    }
                }

                if ($vchNum && $vchType) {
                    $vouchers[] = [
                        'invoice_no' => $vchNum,
                        'type' => $vchType,
                        'date' => $date,
                        'party' => $partyName,
                        'ledger_entries' => $ledgerEntries,
                        'items' => $items,
                    ];
                }
            }
        }

        libxml_clear_errors();
        return $vouchers;
    }
}


