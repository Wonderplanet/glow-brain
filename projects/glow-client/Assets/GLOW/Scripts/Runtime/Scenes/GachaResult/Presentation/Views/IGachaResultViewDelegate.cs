using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.GachaResult.Presentation.Views
{
    public interface IGachaResultViewDelegate
    {
        void OnViewDidLoad();
        void OnReDrawButtonTapped(MasterDataId gachaId, GachaDrawType gachaDrawType);
        void OnIconCellTapped(PlayerResourceIconViewModel model);
        void ExitGachaResult();
        void OnTutorialConfirmButtonTapped();
        void OnTutorialReDrawButtonTapped(MasterDataId gachaId, GachaDrawType gachaDrawType);
        void ShowInAppReview();
    }
}
