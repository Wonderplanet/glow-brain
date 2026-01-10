using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels
{
    public record GachaHistoryDialogViewModel(
        IReadOnlyList<GachaHistoryCellViewModel> GachaHistoryCellViewModels,
        IReadOnlyList<GachaHistoryDetailDialogViewModel> GachaHistoryDetailDialogViewModels)
    {
        const int MaxPage = 5;
        const int ItemsPerPage = 10;
        
        public IReadOnlyList<GachaHistoryCellViewModel> GetGachaHistoryCellViewModelsForCurrentPage(int currentPage)
        {
            return GetPagedItems(GachaHistoryCellViewModels, currentPage, MaxPage, ItemsPerPage);
        }
        
        public IReadOnlyList<GachaHistoryDetailDialogViewModel> GetGachaHistoryDetailDialogViewModelsForCurrentPage(
            int currentPage)
        {
            return GetPagedItems(GachaHistoryDetailDialogViewModels, currentPage, MaxPage, ItemsPerPage);
        }
        
        IReadOnlyList<T> GetPagedItems<T>(IReadOnlyList<T> items, int currentPage, int maxPage, int itemsPerPage)
        {
            if (currentPage < 1 || currentPage > maxPage)
            {
                return new List<T>();
            }
            var startIndex = (currentPage - 1) * itemsPerPage;
            var count = itemsPerPage;
            if (startIndex >= items.Count)
            {
                return new List<T>();
            }
            // 10件に満たない場合の調整
            if (startIndex + count > items.Count)
            {
                count = items.Count - startIndex;
            }
            return items
                .Skip(startIndex)
                .Take(count)
                .ToList();
        }

        public int GetLastPageNum()
        {
            var itemCount = GachaHistoryCellViewModels.Count;

            if (itemCount <= 0)
            {
                return 1;
            }

            var pageNum = (itemCount + ItemsPerPage - 1) / ItemsPerPage;

            if (pageNum > MaxPage)
            {
                pageNum = MaxPage;
            }

            return pageNum;
        }
        
        public bool CanGoToFirstPage(int currentPage) => currentPage > 1;
        public bool CanGoToPreviousPage(int currentPage) => currentPage > 1;
        public bool CanGoToNextPage(int currentPage) => currentPage < GetLastPageNum();
        public bool CanGoToLastPage(int currentPage) => currentPage < GetLastPageNum();
    }
}