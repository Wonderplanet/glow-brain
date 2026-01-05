using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ExchangeShop.Domain.UseCaseModel;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;

namespace GLOW.Scenes.ExchangeShop.Presentation.Translator
{
    public class ExchangeConfirmViewModelTranslator
    {
        public static ExchangeConfirmViewModel Translate(ExchangeConfirmUseCaseModel useCaseModel)
        {
            var exchangeItemIconViewModel = new ItemIconViewModel(
                useCaseModel.ExchangeItemId,
                useCaseModel.ExchangeItemIconAssetPath,
                useCaseModel.ExchangeItemRarity,
                useCaseModel.ExchangeItemAmount);

            return new ExchangeConfirmViewModel(
                useCaseModel.ExchangeItemName,
                useCaseModel.ExchangeItemAmount,
                exchangeItemIconViewModel,
                useCaseModel.CostItemName,
                useCaseModel.CostItemAmount,
                useCaseModel.CostItemIconAssetPath,
                useCaseModel.CurrentCostItemAmount,
                useCaseModel.MaxPurchaseCount,
                useCaseModel.CurrentMaxPurchaseCount,
                useCaseModel.LimitTime);
        }
    }
}
