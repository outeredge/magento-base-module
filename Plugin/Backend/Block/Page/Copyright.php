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

        $a = $doc->createElement('a');
        $a->setAttribute('href', 'http://outeredgeuk.com/');
        $a->setAttribute('target', '_blank');
        $a->setAttribute('title', __('outer/edge'));
        $a->appendChild($img);

        $doc->appendChild($doc->createElement('br'));
        $doc->appendChild($a);

        return $doc->saveHTML();
    }
}
