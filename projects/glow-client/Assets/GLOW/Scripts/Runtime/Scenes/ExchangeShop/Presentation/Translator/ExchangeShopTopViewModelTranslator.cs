using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Domain.UseCaseModel;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;

namespace GLOW.Scenes.ExchangeShop.Presentation.Translator
{
    public class ExchangeShopTopViewModelTranslator
    {
        public static ExchangeShopTopViewModel Translate(ExchangeShopUseCaseModel useCaseModel)
        {
            var cellViewModels = useCaseModel.CellUseCaseModels
                .Select(m =>
                {
                    var playerResourceIconViewModel =
                        PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(m.PlayerResourceModel);

                    return new ExchangeShopCellViewModel(
                        m.MstExchangeShopId,
                        m.MstLineupId,
                        m.ProductItemName,
                        m.ProductResourceType,
                        m.ProductResourceAmount,
                        playerResourceIconViewModel,
                        m.RemainingTime,
                        m.PurchasableCount,
                        m.CostItemIconAssetPath,
                        m.CostAmount,
                        m.SortOrder);

                })
                .ToList();

            return new ExchangeShopTopViewModel(
                useCaseModel.Name,
                cellViewModels,
                useCaseModel.TradeAmountModels.Select(CreateExchangeShopTopAmountViewModel).ToList());
        }

        public static ExchangeShopTopAmountViewModel CreateExchangeShopTopAmountViewModel(
            ExchangeShopTopAmountModel model)
        {
            return new ExchangeShopTopAmountViewModel(model.ItemIconAssetPath, model.Amount);
        }
    }
}
