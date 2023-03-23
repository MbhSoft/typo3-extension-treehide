<?php

declare(strict_types=1);

namespace MbhSoftware\Treehide\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * class HidePagesRecursiveController
 */
class HidePagesRecursiveController
{
    protected DataHandler $dataHandler;

    public function __construct()
    {
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();
        $pageUid = (int)($parsedBody['id'] ?? $queryParams['id'] ?? 0);
        $mode = (int)($parsedBody['mode'] ?? $queryParams['mode'] ?? 0);
        $message = $this->getLanguageService()->sL('LLL:EXT:treehide/Resources/Private/Language/locallang.xlf:treehide.message.error');
        $success = false;
        if ($pageUid !== 0 && $this->getBackendUserAuthentication()->isAdmin()) {
            $fieldName = $GLOBALS['TCA']['pages']['ctrl']['enablecolumns']['disabled'];
            $data['pages'][$pageUid][$fieldName] = $mode;
            $subPages = [];
            $this->getPageTreeInfo($pageUid, 99, $subPages);
            foreach ($subPages as $subPage) {
                $data['pages'][$subPage][$fieldName] = $mode;
            }
            $this->dataHandler->start($data, []);
            $this->dataHandler->process_datamap();
            $message = $this->getLanguageService()->sL('LLL:EXT:treehide/Resources/Private/Language/locallang.xlf:treehide.message.success');
            $success = true;
        }
        return new JsonResponse([
            'success' => $success,
            'title' => $this->getLanguageService()->sL('LLL:EXT:treehide/Resources/Private/Language/locallang.xlf:treehide.title'),
            'message' => $message,
        ]);
    }

    protected function getPageTreeInfo(int $pid, int $levels = 99, array &$CPtable = []): array
    {
        if ($levels > 0) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
            $restrictions = $queryBuilder->getRestrictions()->removeAll();
            $restrictions->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $queryBuilder
                ->select('uid')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->eq('sys_language_uid', 0),
                );
            $result = $queryBuilder->execute();

            $pages = [];
            while ($row = $result->fetchAssociative()) {
                $pages[$row['uid']] = $row;
            }

            foreach ($pages as $page) {
                $CPtable[] = $page['uid'];
                if ($levels - 1) {
                    $CPtable = $this->getPageTreeInfo($page['uid'], $levels - 1, $CPtable);
                }
            }
        }
        return $CPtable;
    }

    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
