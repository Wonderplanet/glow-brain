using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record GachaListViewModel(
        MasterDataId InitialShowOprGachaId,
        GachaListElementViewModel TutorialElementViewModel,
        IReadOnlyList<GachaListElementViewModel> GachaListElementViewModels)
    {
        // 取得はGetListElementViewModelsから行うので隠蔽
        IReadOnlyList<GachaListElementViewModel> GachaListElementViewModels { get; } = GachaListElementViewModels;

        public bool IsTutorial()
        {
            return !TutorialElementViewModel.IsEmpty();
        }

        public bool IsTutorialTarget(MasterDataId oprGachaId)
        {
            if (!IsTutorial())
            {
                return false;
            }

            return TutorialElementViewModel.GachaContentViewModel.OprGachaId == oprGachaId;
        }

        public IReadOnlyList<GachaContentAssetPath> GetAllGachaContentAssetPaths()
        {
            if (IsTutorial())
            {
                return new List<GachaContentAssetPath>() { TutorialElementViewModel.GachaContentAssetViewModel.GachaContentAssetPath };
            }

            return GachaListElementViewModels
                .Select(m => m.GachaContentAssetViewModel.GachaContentAssetPath)
                .ToList();
        }

        public IReadOnlyList<GachaFooterBannerViewModel> GetGachaFooterBannerViewModels()
        {
            if (IsTutorial())
            {
                return new List<GachaFooterBannerViewModel>() { TutorialElementViewModel.GachaFooterBannerViewModel };
            }

            return GachaListElementViewModels
                .Select(m => m.GachaFooterBannerViewModel)
                .ToList();
        }

        public IReadOnlyList<GachaListElementViewModel> GetListElementViewModels()
        {
            if (IsTutorial())
            {
                return new List<GachaListElementViewModel>() { TutorialElementViewModel };
            }
            else
            {
                return GachaListElementViewModels;
            }
        }
    };
}
