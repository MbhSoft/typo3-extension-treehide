<?php

declare(strict_types=1);

namespace MbhSoftware\Treehide\Hook;

use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * class BackendControllerHook
 */
class BackendControllerHook
{
    public function addJavaScript(array $configuration, BackendController $backendController): void
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $this->getPageRenderer()->addInlineSetting('Treehide', 'ajaxUrl', (string)$uriBuilder->buildUriFromRoute('ajax_treehide_hidepagesrecursive'));
    }

    protected function getPageRenderer(): PageRenderer
    {
        return GeneralUtility::makeInstance(PageRenderer::class);
    }
}
