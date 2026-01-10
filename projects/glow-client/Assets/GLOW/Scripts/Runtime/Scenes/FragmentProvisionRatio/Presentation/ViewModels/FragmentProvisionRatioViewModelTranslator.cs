using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.FragmentProvisionRatio.Domain;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.DestinationBanner;
using GLOW.Scenes.ItemDetail.Presentation.Translator;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation.ViewModels
{
    public static class FragmentProvisionRatioViewModelTranslator
    {
        public static FragmentProvisionRatioViewModel Translate(FragmentProvisionRatioUseCaseModel model)
        {
            var groups = model.Items.GroupBy(i => i.Rarity);
            var listViewModels = groups
                .Select(g => TranslateItemListViewModel(model.RatioByRarity(g.Key)
                    , g.Key,
                    g.ToList()))
                .ToList();

            var locationViewModel = ItemDetailWithTransitViewModelTranslator.ToItemDetailAvailableLocationViewModel(model.FragmentBoxEarnLocationModel);

            return new FragmentProvisionRatioViewModel(listViewModels, locationViewModel);
        }

        static ProvisionRatioItemListViewModel TranslateItemListViewModel(OutputRatio ratioByRarity,Rarity rarity,
            IReadOnlyList<FragmentProvisionRatioItemModel> groupedItems)
        {
            return new ProvisionRatioItemListViewModel(rarity, ratioByRarity,groupedItems.Select(TranslateRatioItemViewModel).ToList());
        }

        static ProvisionRatioItemViewModel TranslateRatioItemViewModel(FragmentProvisionRatioItemModel useCaseModel)
        {
            return new ProvisionRatioItemViewModel(useCaseModel.MstUnitId,
                ItemViewModelTranslator.ToItemIconViewModel(useCaseModel.ItemModel),
                useCaseModel.ItemName,
                useCaseModel.OutputRatio);
        }
    }
}
