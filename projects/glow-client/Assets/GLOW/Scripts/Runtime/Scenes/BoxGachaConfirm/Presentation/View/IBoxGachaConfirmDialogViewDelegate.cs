using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.BoxGachaConfirm.Presentation.View
{
    public interface IBoxGachaConfirmDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnDrawButtonTapped(GachaDrawCount drawCount);
        void OnCancelButtonTapped();
        
    }
}