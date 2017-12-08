<?php

namespace OuterEdge\Base\Plugin\Backend\Block\Page;

use Magento\Backend\Block\Page\Copyright as PageCopyright;
use DOMDocument;

class Copyright
{
    /**
     * Add outer/edge logo to footer in backend
     *
     * @param PageCopyright $subject
     * @return string
     */
    public function afterFetchView(PageCopyright $subject, $html)
    {
        $doc = new DOMDocument();
        $doc->loadHtml($html);

        $img = $doc->createElement('img');
        $img->setAttribute('src', $subject->getViewFileUrl('OuterEdge_Base::images/logo-outeredge.png'));
        $img->setAttribute('title', 'Supported by outer/edge');
        
        $a = $doc->createElement('a');
        $a->setAttribute('href', 'https://outeredgeuk.com/');
        $a->setAttribute('target', '_blank');
        $a->setAttribute('title', __('outer/edge'));
        $a->appendChild($img);
        
        $br = $doc->insertBefore($doc->createElement('br'), $doc->childNodes->item(0));
        $doc->insertBefore($a, $br);

        return $doc->saveHTML();
    }
}
