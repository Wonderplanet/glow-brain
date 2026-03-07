using System.Linq;
using GLOW.Scenes.DiamondPurchaseHistory.Domain;

namespace GLOW.Scenes.DiamondPurchaseHistory.Presentation
{
    public class DiamondPurchaseHistoryViewModelTranslator
    {

        public static DiamondPurchaseHistoryViewModel ToDiamondPurchaseHistoryViewModel(
            DiamondPurchaseHistoryUseCaseModel model)
        {
            var elements = model.Elements
                .Select(ToDiamondPurchaseHistoryElementViewModel)
                .ToList();
            return new DiamondPurchaseHistoryViewModel(elements, new PageNumber(1), CreateMaxPageNumber(elements.Count));
        }

        static DiamondPurchaseHistoryElementViewModel ToDiamondPurchaseHistoryElementViewModel(
            DiamondPurchaseHistoryElementUseCaseModel model)
        {
            return new DiamondPurchaseHistoryElementViewModel(
                model.Price,
                model.Amount,
                model.ProductName,
                model.PurchaseAt
            );
        }

        static PageNumber CreateMaxPageNumber(int elementCount)
        {
            if (elementCount <= 0)
            {
                return new PageNumber(1);
            }
            return new PageNumber((elementCount - 1) / DiamondPurchaseHistoryViewModel.ItemsPerPage + 1);
        }
    }
}
