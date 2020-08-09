<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            if ($modx instanceof modX) {
                if ($snippet = $modx->getObject('modSnippet', array('name' => 'Jevix'))) {
                    if(!$propertySet = $modx->getObject('modPropertySet', array('name' => 'RealMessenger'))) {
                        if($propertySet = $modx->newObject('modPropertySet', [
                            'name' => 'RealMessenger',
                            'description' => 'Filter rules for RealMessenger',
                            'properties' => 'a:5:{s:17:"cfgAllowTagParams";a:7:{s:4:"name";s:17:"cfgAllowTagParams";s:4:"desc";s:17:"cfgAllowTagParams";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:7:"lexicon";s:16:"jevix:properties";s:4:"area";s:0:"";s:5:"value";s:221:"{"pre":{"class":["prettyprint"]},"cut":{"title":["#text"]},"a":["title","href"],"img":{"0":"src","alt":"#text","1":"title","align":["right","left","center"],"width":"#int","height":"#int","hspace":"#int","vspace":"#int"}}";}s:12:"cfgAllowTags";a:7:{s:4:"name";s:12:"cfgAllowTags";s:4:"desc";s:12:"cfgAllowTags";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:7:"lexicon";s:16:"jevix:properties";s:4:"area";s:0:"";s:5:"value";s:115:"a,img,i,b,u,em,strong,li,ol,ul,sup,abbr,pre,acronym,h1,h2,h3,h4,h5,h6,cut,br,code,s,blockquote,table,tr,th,td,video";}s:15:"cfgSetTagChilds";a:7:{s:4:"name";s:15:"cfgSetTagChilds";s:4:"desc";s:15:"cfgSetTagChilds";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:7:"lexicon";s:16:"jevix:properties";s:4:"area";s:0:"";s:5:"value";s:109:"[["ul",["li"],false,true],["ol",["li"],false,true],["table",["tr"],false,true],["tr",["td","th"],false,true]]";}s:17:"cfgSetAutoReplace";a:7:{s:4:"name";s:17:"cfgSetAutoReplace";s:4:"desc";s:17:"cfgSetAutoReplace";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:7:"lexicon";s:16:"jevix:properties";s:4:"area";s:0:"";s:5:"value";s:136:"[["+/-","(c)","(с)","(r)","(C)","(С)","(R)","<code","code>"],["±","©","©","®","©","©","®","<pre class=\"prettyprint\"","pre>"]]";}s:14:"cfgSetTagShort";a:7:{s:4:"name";s:14:"cfgSetTagShort";s:4:"desc";s:14:"cfgSetTagShort";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:7:"lexicon";s:16:"jevix:properties";s:4:"area";s:0:"";s:5:"value";s:10:"br,img,cut";}}',
                            ])) {
                            if($propertySet->save()){
                                $snippet->addPropertySet($propertySet->name);
                            }
                        }
                    }
                }
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            if ($modx instanceof modX) {
                //$modx->removeExtensionPackage('realmessenger');
            }
            break;
    }
}
return true;