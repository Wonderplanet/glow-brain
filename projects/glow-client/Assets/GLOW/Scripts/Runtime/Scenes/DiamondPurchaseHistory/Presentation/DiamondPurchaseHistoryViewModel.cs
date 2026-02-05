using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.DiamondPurchaseHistory.Domain;

namespace GLOW.Scenes.DiamondPurchaseHistory.Presentation
{
    public record DiamondPurchaseHistoryViewModel(
        IReadOnlyList<DiamondPurchaseHistoryElementViewModel> Elements,
        PageNumber CurrentPage,
        PageNumber MaxPage
    )
    {
        // 1pageあたり10件表示
        public const int ItemsPerPage = 10;

        public bool HasElements => Elements.Count > 0;

        public bool CanGoToFirstPage(PageNumber currentPage) => currentPage > 1;
        public bool CanGoToPreviousPage(PageNumber currentPage) => currentPage > 1;
        public bool CanGoToNextPage(PageNumber currentPage) => currentPage < MaxPage;
        public bool CanGoToLastPage(PageNumber currentPage) => currentPage < MaxPage;


        public IReadOnlyList<DiamondPurchaseHistoryElementViewModel> FilterByCurrentPage(PageNumber currentPage)
        {
            int startIndex = (currentPage.Value - 1) * ItemsPerPage;
            int endIndex = startIndex + ItemsPerPage;

            if (startIndex >= Elements.Count)
            {
                return new List<DiamondPurchaseHistoryElementViewModel>();
            }
            if (endIndex > Elements.Count)
            {
                endIndex = Elements.Count;
            }

            return Elements.ToList().GetRange(startIndex, endIndex - startIndex);
        }
    };
}
