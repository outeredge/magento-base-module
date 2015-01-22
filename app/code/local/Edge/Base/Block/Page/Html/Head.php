<?php

class Edge_Base_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{
    public function addExternalJs($name, $params = "")
    {
        $this->addItem('external_js', $name, $params);
        return $this;
    }

    public function addScript($name, $params = "")
    {
        $this->addItem('script', $name, $params);
        return $this;
    }

    protected function _separateOtherHtmlHeadElements(&$lines, $itemIf, $itemType, $itemParams, $itemName, $itemThe)
    {
        $params = $itemParams ? ' ' . $itemParams : '';
        $href   = $itemName;
        switch ($itemType) {
            case 'rss':
                $lines[$itemIf]['other'][] = sprintf('<link href="%s"%s rel="alternate" type="application/rss+xml" />',
                    $href, $params
                );
                break;
            case 'link_rel':
                $lines[$itemIf]['other'][] = sprintf('<link%s href="%s" />', $params, $href);
                break;
            case 'external_js':
                $lines[$itemIf]['other'][] = sprintf('<script src="%s"></script>', $href);
                break;
            case 'script':
                $lines[$itemIf]['other'][] = sprintf('<script>%s</script>', $href);
                break;
        }
    }
}