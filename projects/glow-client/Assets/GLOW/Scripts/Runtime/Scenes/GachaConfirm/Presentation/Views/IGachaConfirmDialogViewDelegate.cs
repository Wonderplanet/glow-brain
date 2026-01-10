using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaConfirm.Presentation.Views
{
    public interface IGachaConfirmDialogViewDelegate
    {
        void OnViewDidLoad();
        void GachaDraw(MasterDataId gachaId,
            GachaType gachaType,
            GachaDrawCount drawCount,
            CostType costType,
            CostAmount costAmount,
            MasterDataId costId,
            bool isReDraw,
            GachaDrawFromContentViewFlag isGachaDrawFromContentView);
        void OnSpecificCommerceButtonTapped();
        void TransitionToShopView();
        void TutorialGachaDraw();
    }
}
