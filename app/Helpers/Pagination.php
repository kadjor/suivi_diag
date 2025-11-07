<?php

namespace Helpers;

/**
 * Helper pour gérer la pagination
 */
class Pagination
{
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $totalPages;
    private $offset;

    /**
     * Constructeur
     *
     * @param int $totalItems Nombre total d'éléments
     * @param int $itemsPerPage Nombre d'éléments par page
     * @param int $currentPage Page courante
     */
    public function __construct($totalItems, $itemsPerPage = 20, $currentPage = 1)
    {
        $this->totalItems = max(0, (int) $totalItems);
        $this->itemsPerPage = max(1, (int) $itemsPerPage);
        $this->currentPage = max(1, (int) $currentPage);
        $this->totalPages = max(1, (int) ceil($this->totalItems / $this->itemsPerPage));

        // Ajuster la page courante si elle dépasse le total
        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }

        $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
    }

    /**
     * Retourne l'offset pour la requête SQL
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Retourne la limite pour la requête SQL
     */
    public function getLimit()
    {
        return $this->itemsPerPage;
    }

    /**
     * Retourne la page courante
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Retourne le nombre total de pages
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Retourne le nombre total d'éléments
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * Retourne le nombre d'éléments par page
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * Vérifie s'il y a une page précédente
     */
    public function hasPrevious()
    {
        return $this->currentPage > 1;
    }

    /**
     * Vérifie s'il y a une page suivante
     */
    public function hasNext()
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Retourne le numéro de la page précédente
     */
    public function getPreviousPage()
    {
        return $this->hasPrevious() ? $this->currentPage - 1 : 1;
    }

    /**
     * Retourne le numéro de la page suivante
     */
    public function getNextPage()
    {
        return $this->hasNext() ? $this->currentPage + 1 : $this->totalPages;
    }

    /**
     * Retourne le numéro du premier élément affiché
     */
    public function getFirstItem()
    {
        return $this->totalItems > 0 ? $this->offset + 1 : 0;
    }

    /**
     * Retourne le numéro du dernier élément affiché
     */
    public function getLastItem()
    {
        return min($this->offset + $this->itemsPerPage, $this->totalItems);
    }

    /**
     * Génère les numéros de pages à afficher
     *
     * @param int $maxLinks Nombre maximum de liens à afficher
     * @return array
     */
    public function getPageNumbers($maxLinks = 7)
    {
        $pages = [];

        if ($this->totalPages <= $maxLinks) {
            // Afficher toutes les pages
            for ($i = 1; $i <= $this->totalPages; $i++) {
                $pages[] = $i;
            }
        } else {
            // Calculer la fenêtre de pages à afficher
            $halfLinks = floor($maxLinks / 2);
            $start = max(1, $this->currentPage - $halfLinks);
            $end = min($this->totalPages, $this->currentPage + $halfLinks);

            // Ajuster si on est trop près du début ou de la fin
            if ($this->currentPage <= $halfLinks) {
                $end = min($this->totalPages, $maxLinks);
            } elseif ($this->currentPage >= $this->totalPages - $halfLinks) {
                $start = max(1, $this->totalPages - $maxLinks + 1);
            }

            // Ajouter la première page si nécessaire
            if ($start > 1) {
                $pages[] = 1;
                if ($start > 2) {
                    $pages[] = '...';
                }
            }

            // Ajouter les pages intermédiaires
            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }

            // Ajouter la dernière page si nécessaire
            if ($end < $this->totalPages) {
                if ($end < $this->totalPages - 1) {
                    $pages[] = '...';
                }
                $pages[] = $this->totalPages;
            }
        }

        return $pages;
    }

    /**
     * Génère le HTML de la pagination
     *
     * @param string $baseUrl URL de base (sans le numéro de page)
     * @param string $pageParam Nom du paramètre GET pour la page
     * @return string
     */
    public function render($baseUrl, $pageParam = 'page')
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<nav class="pagination" aria-label="Pagination">';
        $html .= '<ul class="pagination-list">';

        // Bouton Précédent
        if ($this->hasPrevious()) {
            $url = $this->buildUrl($baseUrl, $pageParam, $this->getPreviousPage());
            $html .= '<li><a href="' . htmlspecialchars($url) . '" class="pagination-previous">&laquo; Précédent</a></li>';
        } else {
            $html .= '<li><span class="pagination-previous disabled">&laquo; Précédent</span></li>';
        }

        // Numéros de pages
        foreach ($this->getPageNumbers() as $page) {
            if ($page === '...') {
                $html .= '<li><span class="pagination-ellipsis">...</span></li>';
            } elseif ($page == $this->currentPage) {
                $html .= '<li><span class="pagination-link active">' . $page . '</span></li>';
            } else {
                $url = $this->buildUrl($baseUrl, $pageParam, $page);
                $html .= '<li><a href="' . htmlspecialchars($url) . '" class="pagination-link">' . $page . '</a></li>';
            }
        }

        // Bouton Suivant
        if ($this->hasNext()) {
            $url = $this->buildUrl($baseUrl, $pageParam, $this->getNextPage());
            $html .= '<li><a href="' . htmlspecialchars($url) . '" class="pagination-next">Suivant &raquo;</a></li>';
        } else {
            $html .= '<li><span class="pagination-next disabled">Suivant &raquo;</span></li>';
        }

        $html .= '</ul>';

        // Informations sur les résultats
        $html .= '<div class="pagination-info">';
        $html .= sprintf(
            'Affichage de %d à %d sur %d résultat%s',
            $this->getFirstItem(),
            $this->getLastItem(),
            $this->totalItems,
            $this->totalItems > 1 ? 's' : ''
        );
        $html .= '</div>';

        $html .= '</nav>';

        return $html;
    }

    /**
     * Construit l'URL avec le numéro de page
     *
     * @param string $baseUrl
     * @param string $pageParam
     * @param int $page
     * @return string
     */
    private function buildUrl($baseUrl, $pageParam, $page)
    {
        $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
        return $baseUrl . $separator . $pageParam . '=' . $page;
    }

    /**
     * Retourne les informations de pagination sous forme de tableau
     */
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'total_items' => $this->totalItems,
            'items_per_page' => $this->itemsPerPage,
            'first_item' => $this->getFirstItem(),
            'last_item' => $this->getLastItem(),
            'has_previous' => $this->hasPrevious(),
            'has_next' => $this->hasNext(),
            'previous_page' => $this->getPreviousPage(),
            'next_page' => $this->getNextPage(),
            'pages' => $this->getPageNumbers()
        ];
    }
}
