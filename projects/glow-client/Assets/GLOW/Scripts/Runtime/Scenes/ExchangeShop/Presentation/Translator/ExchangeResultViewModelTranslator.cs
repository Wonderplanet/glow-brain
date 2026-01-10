using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.ArtworkFragment.Presentation.Translator;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using GLOW.Scenes.ExchangeShop.Domain.UseCaseModel;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using GLOW.Scenes.UnitReceive.Presentation.Translator;
using GLOW.Scenes.UnitReceive.Presentation.ViewModel;

namespace GLOW.Scenes.ExchangeShop.Presentation.Translator
{
    public class ExchangeResultViewModelTranslator
    {
        public static ExchangeResultViewModel Translate(ExchangeResultUseCaseModel useCaseModel)
        {
            var commonReceiveResourceViewModels = useCaseModel.RewardModels
                .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                .ToList();

            // 報酬がアートワークの場合、アイコンのパスをデフォルトのものに差し替える
            if (commonReceiveResourceViewModels.Count > 0 &&
                commonReceiveResourceViewModels.First().PlayerResourceIconViewModel.ResourceType ==
                ResourceType.Artwork)
            {
                var assetPath =
                    ArtworkAssetPath.Default.ToPlayerResourceIconAssetPath();
                var playerResourceIconViewModel =
                    commonReceiveResourceViewModels.First().PlayerResourceIconViewModel with
                    {
                        AssetPath = assetPath
                    };

                commonReceiveResourceViewModels[0] = commonReceiveResourceViewModels.First() with
                {
                    PlayerResourceIconViewModel = playerResourceIconViewModel,
                };
            }

            var artwork = useCaseModel.ArtworkFragmentAcquisitionModel;
            var artworkFragmentAcquisitionViewModel = ArtworkFragmentAcquisitionViewModel.Empty;
            if (!artwork.IsEmpty())
            {
                artworkFragmentAcquisitionViewModel = new ArtworkFragmentAcquisitionViewModel(
                    ArtworkPanelViewModelTranslator.ToTranslate(artwork.ArtworkPanelModel),
                    artwork.AcquiredArtworkFragmentPositions,
                    artwork.ArtworkName,
                    artwork.Description,
                    artwork.IsCompleted,
                    artwork.AddHp);
            }

            var unitReceiveViewModel = UnitReceiveViewModel.Empty;
            if (!useCaseModel.UnitReceiveViewModel.IsEmpty())
            {
                unitReceiveViewModel = UnitReceiveViewModelTranslator.ToReceiveViewModel(useCaseModel.UnitReceiveViewModel);
            }

            return new ExchangeResultViewModel(
                commonReceiveResourceViewModels,
                artworkFragmentAcquisitionViewModel,
                unitReceiveViewModel);
        }
    }
}
