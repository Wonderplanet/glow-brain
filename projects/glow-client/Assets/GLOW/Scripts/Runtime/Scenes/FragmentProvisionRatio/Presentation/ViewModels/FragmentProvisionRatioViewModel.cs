using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.DestinationBanner;
using GLOW.Scenes.ItemDetail.Presentation.Views;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation.ViewModels
{
    public record FragmentProvisionRatioViewModel(
        IReadOnlyList<ProvisionRatioItemListViewModel> ListViewModels,
        ItemDetailAvailableLocationViewModel AvailableLocation)
    {
        public bool ExistR => ListViewModels.Any(m => m.Rarity == Rarity.R);
        public bool ExistSR => ListViewModels.Any(m => m.Rarity == Rarity.SR);
        public bool ExistSSR => ListViewModels.Any(m => m.Rarity == Rarity.SSR);
        public bool ExistUR => ListViewModels.Any(m => m.Rarity == Rarity.UR);

        public bool IsSingleRarity
        {
            get
            {
                var group = ListViewModels.GroupBy(m => m.Rarity);
                return group.Count() == 1;
            }
        }

        public bool IsAvailableLocation()
        {
            return AvailableLocation.EarnLocationViewModel1.TransitionPossibleFlag
                    || AvailableLocation.EarnLocationViewModel2.TransitionPossibleFlag;
        }
    };

    public record ProvisionRatioItemListViewModel(
        Rarity Rarity,
        OutputRatio RatioByRarity,
        IReadOnlyList<ProvisionRatioItemViewModel> Items);

    public record ProvisionRatioItemViewModel(
        MasterDataId MstUnitId,
        ItemIconViewModel ItemIconViewModel,
        ItemName ItemName,
        OutputRatio OutputRatio);
}
