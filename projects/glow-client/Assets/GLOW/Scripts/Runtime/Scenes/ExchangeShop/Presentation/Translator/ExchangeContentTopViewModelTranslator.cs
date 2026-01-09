using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.ExchangeShop.Domain.UseCaseModel;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;

namespace GLOW.Scenes.ExchangeShop.Presentation.Translator
{
    public class ExchangeContentTopViewModelTranslator
    {
        public static ExchangeContentTopViewModel Translate(IReadOnlyList<ActiveExchangeContentUseCaseModel> useCaseModels)
        {
            var cellViewModels = useCaseModels
                .Select(useCaseModel => new ExchangeContentCellViewModel(
                    useCaseModel.Id,
                    useCaseModel.MstGroupId,
                    useCaseModel.TradeType,
                    ExchangeContentBannerAssetPath.FromAssetKey(useCaseModel.BannerAssetKey.Value),
                    useCaseModel.LimitTime,
                    useCaseModel.EndAt))
                .ToList();

            return new ExchangeContentTopViewModel(cellViewModels);
        }
    }
}
