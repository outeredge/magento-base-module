<?php

class Edge_Base_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{
    public function addItemAfter($type, $name, $after, $params=null, $if=null, $cond=null)
    {
        if ($type==='skin_css' && empty($params)) {
            $params = 'media="all"';
        }

        $this->_data['after'][$after][$type.'/'.$name] = array(
            'type'   => $type,
            'name'   => $name,
            'params' => $params,
            'if'     => $if,
            'cond'   => $cond,
        );
        return $this;
    }

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
                $lines[$itemIf]['other'][$itemName] = sprintf('<link href="%s"%s rel="alternate" type="application/rss+xml" />',
                    $href, $params
                );
                break;
            case 'link_rel':
                $lines[$itemIf]['other'][$itemName] = sprintf('<link%s href="%s" />', $params, $href);
                break;
            case 'external_js':
                $lines[$itemIf]['other'][$itemName] = sprintf('<script type="text/javascript" src="%s"></script>', $href);
                break;
            case 'script':
                $lines[$itemIf]['other'][$itemName] = sprintf('<script type="text/javascript">%s</script>', $href);
                break;
        }
    }

    /**
     * Get HEAD HTML with CSS/JS/RSS definitions
     * (actually it also renders other elements, TODO: fix it up or rename this method)
     *
     * @return string
     */
    public function getCssJsHtml()
    {
        // separate items by types
        $lines  = array();
        foreach ($this->_data['items'] as $item) {
            if (!is_null($item['cond']) && !$this->getData($item['cond']) || !isset($item['name'])) {
                continue;
            }
            $if     = !empty($item['if']) ? $item['if'] : '';
            $params = !empty($item['params']) ? $item['params'] : '';
            switch ($item['type']) {
                case 'js':        // js/*.js
                case 'skin_js':   // skin/*/*.js
                case 'js_css':    // js/*.css
                case 'skin_css':  // skin/*/*.css
                    $lines[$if][$item['type']][$params][$item['name']] = $item['name'];
                    break;
                default:
                    $this->_separateOtherHtmlHeadElements($lines, $if, $item['type'], $params, $item['name'], $item);
                    break;
            }
        }


        // Process After Items
        // Inserts items into the head in correct order based off xml
        if (!empty($this->_data['after'])) {
            foreach ($this->_data['after'] as $after => $items) {
                foreach ($items as $item) {
                    if (!is_null($item['cond']) && !$this->getData($item['cond']) || !isset($item['name'])) {
                        continue;
                    }
                    $if     = !empty($item['if']) ? $item['if'] : '';
                    $params = !empty($item['params']) ? $item['params'] : '';

                    foreach ($lines[$if] as $type => $data) {
                        $dataArray = array();

                        if ($type === 'other') {
                            $dataArray = $data;

                            switch ($item['type']) {
                                case 'js_css':
                                case 'skin_css':
                                    $itemHtml = $this->_prepareStaticAndSkinElements('<link rel="stylesheet" type="text/css" href="%s"%s />',
                                        $item['type'] === 'js_css' ? array('' => array($item['name'] => $item['name'])) : array(),
                                        $item['type'] === 'skin_css' ? array('' => array($item['name'] => $item['name'])) : array()
                                    );
                                    break;
                                case 'js':
                                case 'skin_js':
                                    $itemHtml = $this->_prepareStaticAndSkinElements('<script type="text/javascript" src="%s"%s></script>',
                                        $item['type'] === 'js' ? array('' => array($item['name'] => $item['name'])) : array(),
                                        $item['type'] === 'skin_js' ? array('' => array($item['name'] => $item['name'])) : array()
                                    );
                                    break;
                                case 'external_js':
                                    $itemHtml = sprintf('<script type="text/javascript" src="%s"></script>', $item['name']);
                                    break;
                                case 'script':
                                    $itemHtml = sprintf('<script type="text/javascript">%s</script>', $item['name']);
                                    break;
                            }
                        } else {
                            $dataArray = empty($data[$params]) ? array() : $data[$params];
                            $itemHtml = $item['name'];
                        }

                        if (isset($dataArray[$after])) {
                            $array = array();
                            foreach ($dataArray as $key => $value) {
                                $array[$key] = $value;
                                if ($key === $after) {
                                    $array[$item['name']] = $itemHtml;
                                }
                            }

                            if ($type === 'other') {
                                $lines[$if][$type] = $array;
                            } else {
                                $lines[$if][$type][$params] = $array;
                            }
                        }
                    }
                }
            }
        }

        // prepare HTML
        $shouldMergeJs = Mage::getStoreConfigFlag('dev/js/merge_files');
        $shouldMergeCss = Mage::getStoreConfigFlag('dev/css/merge_css_files');
        $html   = '';
        foreach ($lines as $if => $items) {
            if (empty($items)) {
                continue;
            }
            if (!empty($if)) {
                // open !IE conditional using raw value
                if (strpos($if, "><!-->") !== false) {
                    $html .= $if . "\n";
                } else {
                    $html .= '<!--[if '.$if.']>' . "\n";
                }
            }

            // static and skin css
            $html .= $this->_prepareStaticAndSkinElements('<link rel="stylesheet" type="text/css" href="%s"%s />'."\n",
                empty($items['js_css']) ? array() : $items['js_css'],
                empty($items['skin_css']) ? array() : $items['skin_css'],
                $shouldMergeCss ? array(Mage::getDesign(), 'getMergedCssUrl') : null
            );

            // static and skin javascripts
            $html .= $this->_prepareStaticAndSkinElements('<script type="text/javascript" src="%s"%s></script>' . "\n",
                empty($items['js']) ? array() : $items['js'],
                empty($items['skin_js']) ? array() : $items['skin_js'],
                $shouldMergeJs ? array(Mage::getDesign(), 'getMergedJsUrl') : null
            );

            // other stuff
            if (!empty($items['other'])) {
                $html .= $this->_prepareOtherHtmlHeadElements($items['other']) . "\n";
            }

            if (!empty($if)) {
                // close !IE conditional comments correctly
                if (strpos($if, "><!-->") !== false) {
                    $html .= '<!--<![endif]-->' . "\n";
                } else {
                    $html .= '<![endif]-->' . "\n";
                }
            }
        }
        return $html;
    }
}